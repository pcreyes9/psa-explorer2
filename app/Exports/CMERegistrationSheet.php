<?php

namespace App\Exports;

use App\Models\CMEProgram;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CMERegistrationSheet implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithStyles
{
    public function __construct(
        protected CMEProgram $program
    ) {
    }

    public function collection(): Collection
    {
        $rows = collect();

        /*
        |--------------------------------------------------------------------------
        | RM / TM / LM
        |--------------------------------------------------------------------------
        */

        $memberRegistrations = DB::table('cme_program_registration as r')
            ->join(
                'member as m',
                'm.member_id_no',
                '=',
                'r.member_id_no'
            )
            ->where(
                'r.cme_program_code',
                $this->program->cme_program_code
            )
            ->whereIn(
                'm.psa_mem_type',
                ['RM', 'TM', 'LM']
            )
            ->select([
                'r.member_id_no',
                'r.payment_ref_no',
                'm.mem_last_name',
                'm.mem_first_name',
                'm.mem_middle_name',
                'm.psa_mem_type',
            ])
            ->orderBy('m.mem_last_name')
            ->get();

        foreach ($memberRegistrations as $registration) {

            $middleName = trim(
                $registration->mem_middle_name ?? ''
            );

            $name = trim(
                $registration->mem_last_name
                . ', '
                . $registration->mem_first_name
                . ($middleName ? ' ' . $middleName : '')
            );

            $rows->push([
                'psa_id'          => $registration->member_id_no,
                'member_name'     => $name,
                'membership_type' => $registration->psa_mem_type,
                'payment_ref_no'  => $registration->payment_ref_no ?: '-',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | NM
        |--------------------------------------------------------------------------
        |
        | Non-members come from cme_program_registrationNM.
        |
        */

        $nmRegistrations = DB::table('cme_program_registrationNM')
            ->where(
                'cme_program_code',
                $this->program->cme_program_code
            )
            ->get();

        foreach ($nmRegistrations as $registration) {

            /*
             * Adjust these fields if your NM table uses
             * different column names.
             */

            $lastName = $registration->nm_last_name
                ?? $registration->last_name
                ?? '';

            $firstName = $registration->nm_first_name
                ?? $registration->first_name
                ?? '';

            $middleName = $registration->nm_middle_name
                ?? $registration->middle_name
                ?? '';

            $paymentRef = $registration->payment_ref_no
                ?? $registration->payment_ref
                ?? '-';

            $memberId = $registration->member_id_no
                ?? $registration->nm_id_no
                ?? $registration->id_no
                ?? '-';

            $name = trim(
                $lastName
                . ', '
                . $firstName
                . ($middleName ? ' ' . $middleName : '')
            );

            /*
             * If the NM table stores a complete name instead,
             * use it as a fallback.
             */
            if ($name === ',') {
                $name =
                    $registration->name
                    ?? $registration->full_name
                    ?? 'Non-Member';
            }

            $rows->push([
                'psa_id'          => $memberId,
                'member_name'     => $name,
                'membership_type' => 'NM',
                'payment_ref_no'  => $paymentRef,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Sort
        |--------------------------------------------------------------------------
        */

        $rows = $rows
            ->sortBy([
                ['membership_type', 'asc'],
                ['member_name', 'asc'],
            ])
            ->values();

        return $rows->map(
            function ($row, $index) {
                return [
                    $index + 1,
                    $row['psa_id'],
                    $row['member_name'],
                    $row['membership_type'],
                    $row['payment_ref_no'],
                ];
            }
        );
    }

    public function headings(): array
    {
        return [
            '#',
            'PSA ID',
            'Member Name',
            'Membership Type',
            'Payment Ref. No.',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }
}
