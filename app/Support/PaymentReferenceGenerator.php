<?php

namespace App\Support;

use App\Models\Payment;
use App\Models\Member;

class PaymentReferenceGenerator
{
    public static function generate(Member $member): string
    {
        $prefix = match ($member->psa_mem_type) {
            'RM', 'R2', 'RD', 'RE' => 'R',
            'LM', 'L2', 'LD', 'LE' => 'L',
            'TM', 'TD', 'TE' => 'T',
            'EM' => 'E',
            'HM' => 'H',
            'AM' => 'A',
            default => 'X',
        };

        $datePart = now()->format('my'); // 0626

        $latest = Payment::query()
            ->where('payment_ref_no', 'like', "{$prefix}{$datePart}%")
            ->orderByDesc('payment_ref_no')
            ->first();

        $nextSequence = 1;

        if ($latest) {
            $nextSequence = (int) substr(
                $latest->payment_ref_no,
                -5
            ) + 1;
        }

        return sprintf(
            '%s%s%05d',
            $prefix,
            $datePart,
            $nextSequence
        );
    }
}