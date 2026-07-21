<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function cogs(Member $member, Request $request)
    {
        $memberName = strtoupper(trim(implode(' ', array_filter([
            $member->mem_first_name,
            $member->mem_middle_name,
            $member->mem_last_name,
        ])))) . ', MD';

        $secretary = 'JOSELITO T. MORETE, MD, FPSA';

        $president = 'FRANCIS B. MAYUGA, MD, FPSA';

        $membershipType = $member->membershipType?->Memtype ?? '';

        $requestedBy = 'DR. ' . strtoupper($member->mem_last_name);

        $purpose = $request->string('purpose')->toString();

        $fiscalYear = now()->year;

        $issueDate = $this->issueDate();

        $pdf = Pdf::loadView(
            'pdf.cogs',
            compact(
                'memberName',
                'membershipType',
                'requestedBy',
                'purpose',
                'fiscalYear',
                'issueDate',
                'secretary',
                'president'
            )
        );

        // return $pdf->stream('Certificate of Good Standing.pdf');
        return $pdf->download($member->member_id_no.' '.$member->mem_first_name.' '.$member->mem_last_name.'-COGS.pdf');
    }

    private function issueDate(): string
    {
        $day = now()->day;

        return sprintf(
            '%s day of %s %s',
            $this->ordinal($day),
            now()->format('F'),
            now()->year
        );
    }

    private function ordinal(int $number): string
    {
        if (! in_array($number % 100, [11, 12, 13])) {
            return match ($number % 10) {
                1 => "{$number}st",
                2 => "{$number}nd",
                3 => "{$number}rd",
                default => "{$number}th",
            };
        }

        return "{$number}th";
    }
}
