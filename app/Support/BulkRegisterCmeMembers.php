<?php

namespace App\Support;

use App\Models\CMEProgram;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BulkRegisterCmeMembers
{
    /**
     * Bulk register eligible members into a CME program.
     *
     * Eligible membership types:
     * RM - Regular Member
     * TM - Trainee Member
     * LM - Life Member
     *
     * Existing registrations and duplicate PSA IDs are skipped.
     */
    public function execute(
        CMEProgram $program,
        array $memberIds
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Normalize PSA IDs
        |--------------------------------------------------------------------------
        */

        $memberIds = collect($memberIds)
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values();

        if ($memberIds->isEmpty()) {
            throw ValidationException::withMessages([
                'member_ids' => 'No valid PSA IDs were provided.',
            ]);
        }

        return DB::transaction(function () use ($program, $memberIds) {

            // Lock the CME program record to coordinate
            // individual and bulk registration operations.
            DB::select(
                '
                SELECT cme_program_code
                FROM dbo.cme_program WITH (UPDLOCK, HOLDLOCK)
                WHERE cme_program_code = ?
                ',
                [$program->cme_program_code]
            );

            /*
            |--------------------------------------------------------------------------
            | Retrieve Eligible Members
            |--------------------------------------------------------------------------
            */

            $members = DB::table('member')
                ->whereIn('member_id_no', $memberIds)
                ->whereIn('psa_mem_type', ['RM', 'TM', 'LM'])
                ->select([
                    'member_id_no',
                    'psa_mem_type',
                ])
                ->get()
                ->keyBy(
                    fn ($member) => trim(
                        (string) $member->member_id_no
                    )
                );

            $eligibleIds = $members->keys();

            /*
            |--------------------------------------------------------------------------
            | Count Invalid or Ineligible Members
            |--------------------------------------------------------------------------
            */

            $invalidCount = $memberIds
                ->diff($eligibleIds)
                ->count();

            /*
            |--------------------------------------------------------------------------
            | Check Existing Registrations
            |--------------------------------------------------------------------------
            */

            $existingIds = DB::table('cme_program_registration')
                ->where(
                    'cme_program_code',
                    $program->cme_program_code
                )
                ->whereIn('member_id_no', $eligibleIds)
                ->pluck('member_id_no')
                ->map(
                    fn ($id) => trim((string) $id)
                )
                ->all();

            $existingLookup = array_fill_keys(
                $existingIds,
                true
            );

            /*
            |--------------------------------------------------------------------------
            | Remove Existing Registrations
            |--------------------------------------------------------------------------
            */

            $newMembers = $members->reject(
                fn ($member, $id) => isset($existingLookup[$id])
            );

            $skippedCount = $members->count()
                - $newMembers->count();

            if ($newMembers->isEmpty()) {
                return [
                    'processed' => 0,
                    'skipped' => $skippedCount,
                    'invalid' => $invalidCount,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Generate Payment Reference Prefix
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | CME2026001 = V26001
            | CME2026002 = V26002
            |
            | Final reference:
            | V260010001
            | V260020001
            |
            */

            $year = substr(
                (string) $program->cme_year,
                -2
            );

            $programType = substr(
                (string) $program->cme_program_code,
                -3
            );

            $prefix = 'V' . $year . $programType;

            /*
            |--------------------------------------------------------------------------
            | Retrieve Last Payment Reference
            |--------------------------------------------------------------------------
            */

            $lastReference = DB::table('cme_program_registration')
                ->where(
                    'payment_ref_no',
                    'LIKE',
                    $prefix . '%'
                )
                ->orderByDesc('payment_ref_no')
                ->lockForUpdate()
                ->value('payment_ref_no');

            $lastSequence = $lastReference
                ? (int) substr(
                    $lastReference,
                    strlen($prefix)
                )
                : 0;

            $nextSequence = $lastSequence + 1;

            /*
            |--------------------------------------------------------------------------
            | Validate Reference Sequence
            |--------------------------------------------------------------------------
            */

            if (
                $nextSequence + $newMembers->count() - 1 > 9999
            ) {
                throw ValidationException::withMessages([
                    'member_ids' => 'The payment reference sequence has reached its maximum of 9999.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Registration Information
            |--------------------------------------------------------------------------
            */

            $user = Auth::user();

            $registeredBy = $user?->username
                ?? $user?->name
                ?? (string) Auth::id();

            $registeredBy = substr(
                $registeredBy,
                0,
                20
            );

            $registrationDate = now()->toDateString();

            /*
            |--------------------------------------------------------------------------
            | Prepare Registration Records
            |--------------------------------------------------------------------------
            */

            $insertRows = [];

            foreach ($newMembers as $memberId => $member) {

                $paymentReference = $prefix . str_pad(
                    (string) $nextSequence,
                    4,
                    '0',
                    STR_PAD_LEFT
                );

                $insertRows[] = [
                    'cme_year' => $program->cme_year,
                    'cme_program_code' => $program->cme_program_code,
                    'member_id_no' => $memberId,
                    'cme_reg_date' => $registrationDate,
                    'payment_ref_no' => $paymentReference,
                    'registeredby' => $registeredBy,
                ];

                $nextSequence++;
            }

            /*
            |--------------------------------------------------------------------------
            | Insert Registrations
            |--------------------------------------------------------------------------
            |
            | Do not insert computed columns:
            |
            | mem_last_name
            | mem_first_name
            | mem_middle_name
            | cme_cert_no
            | cme_units
            |
            */

            foreach (array_chunk($insertRows, 500) as $chunk) {

                DB::table('cme_program_registration')
                    ->insert($chunk);
            }

            /*
            |--------------------------------------------------------------------------
            | Return Processing Summary
            |--------------------------------------------------------------------------
            */

            return [
                'processed' => count($insertRows),
                'skipped' => $skippedCount,
                'invalid' => $invalidCount,
            ];
        }, 3);
    }
    
    /**
     * Preview members before bulk registration.
     *
     * This method does not insert or modify any records.
     */
    public function preview(
        CMEProgram $program,
        array $memberIds
    ): array {

        // Normalize PSA IDs.
        $originalCount = count($memberIds);

        $memberIds = collect($memberIds)
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values();

        $duplicateInputCount = $originalCount - $memberIds->count();

        if ($memberIds->isEmpty()) {
            throw ValidationException::withMessages([
                'member_ids' => 'No valid PSA IDs were provided.',
            ]);
        }

        // Retrieve eligible RM, TM, and LM members.
        $members = DB::table('member')
            ->whereIn('member_id_no', $memberIds)
            ->whereIn('psa_mem_type', ['RM', 'TM', 'LM'])
            ->select([
                'member_id_no',
                'mem_last_name',
                'mem_first_name',
                'mem_middle_name',
                'psa_mem_type',
            ])
            ->orderBy('mem_last_name')
            ->orderBy('mem_first_name')
            ->get()
            ->keyBy(
                fn ($member) => trim(
                    (string) $member->member_id_no
                )
            );

        // Find existing registrations.
        $existingIds = [];

        if ($members->isNotEmpty()) {
            $existingIds = DB::table('cme_program_registration')
                ->where(
                    'cme_program_code',
                    $program->cme_program_code
                )
                ->whereIn(
                    'member_id_no',
                    $members->keys()
                )
                ->pluck('member_id_no')
                ->map(fn ($id) => trim((string) $id))
                ->all();
        }

        $existingLookup = array_fill_keys($existingIds, true);

        // Members eligible for new registration.
        $eligibleMembers = $members
            ->reject(
                fn ($member, $id) => isset($existingLookup[$id])
            )
            ->values();

        // Members already registered.
        $alreadyRegistered = $members
            ->filter(
                fn ($member, $id) => isset($existingLookup[$id])
            )
            ->values();

        // Invalid or ineligible PSA IDs.
        $invalidIds = $memberIds
            ->diff($members->keys())
            ->values()
            ->all();

        return [
            'total_submitted' => $originalCount,

            'duplicate_input_count' => $duplicateInputCount,

            'eligible' => $eligibleMembers
                ->map(fn ($member) => [
                    'member_id_no' => $member->member_id_no,
                    'member_name' => trim(
                        $member->mem_last_name . ', ' .
                        $member->mem_first_name . ' ' .
                        ($member->mem_middle_name ?? '')
                    ),
                    'membership_type' => $member->psa_mem_type,
                ])
                ->all(),

            'already_registered' => $alreadyRegistered
                ->map(fn ($member) => [
                    'member_id_no' => $member->member_id_no,
                    'member_name' => trim(
                        $member->mem_last_name . ', ' .
                        $member->mem_first_name . ' ' .
                        ($member->mem_middle_name ?? '')
                    ),
                    'membership_type' => $member->psa_mem_type,
                ])
                ->all(),

            'invalid' => $invalidIds,

            'summary' => [
                'eligible' => $eligibleMembers->count(),
                'already_registered' => $alreadyRegistered->count(),
                'invalid' => count($invalidIds),
                'duplicate_input' => $duplicateInputCount,
            ],
        ];
    }

}