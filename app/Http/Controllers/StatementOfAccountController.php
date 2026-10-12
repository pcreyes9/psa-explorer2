<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class StatementOfAccountController extends Controller
{
    public function generate(string $member)
    {
        $memberRecord = Member::where(
            'member_id_no',
            $member
        )->firstOrFail();

        $sql = <<<'SQL'
            WITH SOA AS
            (
                SELECT
                    mlb.fiscal_year,
                    mlb.member_id_no,
                    m.mem_first_name,
                    m.mem_middle_name,
                    m.mem_last_name,
                    m.mem_email_address,
                    m.mem_home_address,
                    m.psa_chapter_code,
                    m.psa_mem_type,
                    m.mem_dbfstat,
                    mlb.tran_desc,

                    mlb.dbit AS charges,

                    ISNULL(memf.total_paid, 0) AS credit,

                    (
                        mlb.dbit - ISNULL(memf.total_paid, 0)
                    ) AS balance

                FROM member_ledger_bal mlb

                INNER JOIN member m
                    ON m.member_id_no = mlb.member_id_no

                LEFT JOIN
                (
                    SELECT
                        member_id_no,
                        fiscal_year,
                        SUM(amount_due) AS total_paid
                    FROM payment_items
                    WHERE tran_code = 'MEMF'
                    AND stat = 1
                    GROUP BY
                        member_id_no,
                        fiscal_year
                ) memf
                    ON memf.member_id_no = mlb.member_id_no
                    AND memf.fiscal_year = mlb.fiscal_year

                WHERE mlb.member_id_no = ?
            ),

            LatestZero AS
            (
                SELECT
                    MAX(fiscal_year) AS latest_zero_year
                FROM SOA
                WHERE balance = 0
            )

            SELECT
                s.fiscal_year,
                s.member_id_no,
                s.mem_first_name,
                s.mem_middle_name,
                s.mem_last_name,
                s.mem_email_address,
                s.mem_home_address,
                s.psa_chapter_code,
                s.psa_mem_type,
                s.mem_dbfstat,
                s.tran_desc,
                s.charges,
                s.credit,
                s.balance

            FROM SOA s

            CROSS JOIN LatestZero z

            WHERE s.balance > 0
            AND s.fiscal_year > ISNULL(z.latest_zero_year, 0)

            ORDER BY s.fiscal_year;
        SQL;

        $dues = collect(
            DB::connection('sqlsrv')->select($sql, [$member])
        );

        $totalCharges = $dues->sum(
            fn ($row) => (float) $row->charges
        );

        $totalCredit = $dues->sum(
            fn ($row) => (float) $row->credit
        );

        $totalBalance = $dues->sum(
            fn ($row) => (float) $row->balance
        );

        $pdf = Pdf::loadView(
            'pdf.statement-of-account',
            [
                'member'        => $memberRecord,
                'dues'          => $dues,
                'statementDate' => now(),
                'totalCharges'  => $totalCharges,
                'totalCredit'   => $totalCredit,
                'totalBalance'  => $totalBalance,
            ]
        )->setPaper('a4', 'portrait');

        return $pdf->stream(
            'SOA-' . $memberRecord->member_id_no . '.pdf'
        );
    }
}