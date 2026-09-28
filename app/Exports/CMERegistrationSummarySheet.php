<?php

namespace App\Exports;

use App\Models\CMEProgram;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CMERegistrationSummarySheet implements
    FromArray,
    WithTitle,
    WithStyles
{
    public function __construct(
        protected CMEProgram $program
    ) {
    }

    public function array(): array
    {
        /*
        |--------------------------------------------------------------------------
        | RM / TM / LM
        |--------------------------------------------------------------------------
        */

        $memberCounts = DB::table('cme_program_registration as r')
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
            ->selectRaw(
                'm.psa_mem_type, COUNT(*) as total'
            )
            ->groupBy('m.psa_mem_type')
            ->pluck('total', 'm.psa_mem_type');

        $rm = (int) ($memberCounts['RM'] ?? 0);
        $tm = (int) ($memberCounts['TM'] ?? 0);
        $lm = (int) ($memberCounts['LM'] ?? 0);


        /*
        |--------------------------------------------------------------------------
        | NM
        |--------------------------------------------------------------------------
        */

        $nm = DB::table('cme_program_registrationNM')
            ->where(
                'cme_program_code',
                $this->program->cme_program_code
            )
            ->count();


        $total = $rm + $tm + $lm + $nm;


        return [
            ['CME REGISTRATION SUMMARY'],
            [],
            ['CME Program', $this->program->cme_program_code],
            ['Title', $this->program->cme_title],
            [],
            ['Membership Type', 'Total Registrations'],
            ['Regular Member (RM)', $rm],
            ['Trainee Member (TM)', $tm],
            ['Life Member (LM)', $lm],
            ['Non-Member (NM)', $nm],
            [],
            ['TOTAL REGISTRATIONS', $total],
        ];
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                ],
            ],

            6 => [
                'font' => [
                    'bold' => true,
                ],
            ],

            12 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }
}
