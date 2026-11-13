<?php

namespace App\Support;

use App\Models\CMEProgram;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BulkRegisterCmeMembers
{

    public function execute(
        CMEProgram $program,
        array $memberIds,
        array $paymentReferences = []
    ): array {

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

        return DB::transaction(function () use (
            $program,
            $memberIds,
            $paymentReferences
        ) {

            DB::select(
                '
                SELECT cme_program_code
                FROM dbo.cme_program WITH (UPDLOCK, HOLDLOCK)
                WHERE cme_program_code = ?
                ',
                [$program->cme_program_code]
            );

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

            $invalidCount = $memberIds
                ->diff($eligibleIds)
                ->count();

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

            $referencesByMember = $this->getSuppliedPaymentReferences(
                $newMembers,
                $paymentReferences
            );

            $year = substr(
                (string) $program->cme_year,
                -2
            );

            $programType = substr(
                (string) $program->cme_program_code,
                -3
            );

            $prefix = 'V' . $year . $programType;

            $nextSequence = null;

            if ($referencesByMember === []) {
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

                if (
                    $nextSequence + $newMembers->count() - 1 > 9999
                ) {
                    throw ValidationException::withMessages([
                        'member_ids' => 'The payment reference sequence has reached its maximum of 9999.',
                    ]);
                }
            }

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

            $insertRows = [];

            foreach ($newMembers as $memberId => $member) {

                $paymentReference = $referencesByMember[$memberId]
                    ?? $prefix . str_pad(
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

                if ($referencesByMember === []) {
                    $nextSequence++;
                }
            }

            foreach (array_chunk($insertRows, 500) as $chunk) {

                DB::table('cme_program_registration')
                    ->insert($chunk);
            }

            return [
                'processed' => count($insertRows),
                'skipped' => $skippedCount,
                'invalid' => $invalidCount,
            ];
        }, 3);
    }

    private function getSuppliedPaymentReferences(
        $members,
        array $paymentReferences
    ): array {
        if ($paymentReferences === []) {
            return [];
        }

        $referencesByMember = [];

        foreach ($members as $member) {
            $memberId = trim((string) $member->member_id_no);
            $reference = trim(
                (string) ($paymentReferences[$memberId] ?? '')
            );

            if ($reference === '') {
                throw ValidationException::withMessages([
                    'member_file' => "A payment reference number is required for PSA ID {$memberId}.",
                ]);
            }

            if (strlen($reference) > 20) {
                throw ValidationException::withMessages([
                    'member_file' => "The payment reference number for PSA ID {$memberId} cannot exceed 20 characters.",
                ]);
            }

            $referencesByMember[$memberId] = $reference;
        }

        $normalizedReferences = array_map(
            'strtolower',
            array_values($referencesByMember)
        );

        if (
            count(array_unique($normalizedReferences))
            !== count($normalizedReferences)
        ) {
            throw ValidationException::withMessages([
                'member_file' => 'Each member must have a unique payment reference number.',
            ]);
        }

        $referenceExists = DB::table('cme_program_registration')
            ->whereIn(
                'payment_ref_no',
                array_values($referencesByMember)
            )
            ->exists();

        if ($referenceExists) {
            throw ValidationException::withMessages([
                'member_file' => 'One or more payment reference numbers already exist. Please use unique numbers.',
            ]);
        }

        return $referencesByMember;
    }

    public function preview(
        CMEProgram $program,
        array $memberIds,
        array $paymentReferences = []
    ): array {

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

        $eligibleMembers = $members
            ->reject(
                fn ($member, $id) => isset($existingLookup[$id])
            )
            ->values();

        $alreadyRegistered = $members
            ->filter(
                fn ($member, $id) => isset($existingLookup[$id])
            )
            ->values();
        $referencesByMember = $this->getSuppliedPaymentReferences(
            $eligibleMembers,
            $paymentReferences
        );

        $invalidIds = $memberIds
            ->diff($members->keys())
            ->values()
            ->all();

        return [
            'total_submitted' => $originalCount,
            'duplicate_input_count' => $duplicateInputCount,
            'uses_supplied_references' => $paymentReferences !== [],
            'eligible' => $eligibleMembers
                ->map(fn ($member) => [
                    'member_id_no' => $member->member_id_no,
                    'member_name' => trim(
                        $member->mem_last_name . ', ' .
                        $member->mem_first_name . ' ' .
                        ($member->mem_middle_name ?? '')
                    ),
                    'membership_type' => $member->psa_mem_type,
                    'payment_ref_no' => $referencesByMember[
                        trim((string) $member->member_id_no)
                    ] ?? null,
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
