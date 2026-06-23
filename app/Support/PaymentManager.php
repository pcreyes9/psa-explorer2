<?php

namespace App\Support;

use App\Models\Member;
use App\Models\MemberLedger;
use App\Models\Payment;
use App\Models\TransactionTypeItem;

class PaymentManager
{
    public static function generateReference(Member $member): string
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

        $datePart = now()->format('my');

        $latest = Payment::query()
            ->where('payment_ref_no', 'like', "{$prefix}{$datePart}%")
            ->orderByDesc('payment_ref_no')
            ->first();

        $nextSequence = 1;

        if ($latest) {
            $nextSequence = ((int) substr($latest->payment_ref_no, -5)) + 1;
        }

        return sprintf(
            '%s%s%05d',
            $prefix,
            $datePart,
            $nextSequence
        );
    }

    public static function getMemberBalances(Member $member): array
    {
        $items = TransactionTypeItem::query()
            ->where('charge_code', $member->psa_mem_type)
            ->where('stat', 1)
            ->orderBy('tran_code')
            ->orderBy('item_code')
            ->get();

        return $items->map(function ($item) use ($member) {

            $due = MemberLedger::query()
                ->where('member_id_no', $member->member_id_no)
                ->where('item_code', $item->item_code)
                ->where('entry_type', 0)
                ->sum('amt');

            $paid = MemberLedger::query()
                ->where('member_id_no', $member->member_id_no)
                ->where('item_code', $item->item_code)
                ->where('entry_type', 1)
                ->sum('amt');

            $balance = max($due - $paid, 0);

            return [
                'tran_code'   => $item->tran_code,
                'item_code'   => $item->item_code,
                'description' => $item->item_details,
                'due'         => $due,
                'paid'        => $paid,
                'balance'     => $balance,

                'status' => match (true) {
                    $balance <= 0 && $due > 0 => 'Paid',
                    $paid > 0 && $balance > 0 => 'Partial',
                    default => 'Unpaid',
                },
            ];
        })
        ->filter(fn ($item) => $item['due'] > 0)
        ->values()
        ->toArray();
    }
    public static function getAvailableItems(Member $member)
    {
        return TransactionTypeItem::query()
            ->where('charge_code', $member->psa_mem_type)
            ->where('stat', 1)
            ->orderBy('tran_code')
            ->orderBy('item_code')
            ->get();
    }

    public static function getTransactionItems( Member $member, int $fiscalYear)
    {
        return TransactionTypeItem::query()
            ->where('charge_code', $member->psa_mem_type)
            ->where('fiscal_year', $fiscalYear)
            ->where('stat', 1)
            ->orderBy('tran_code')
            ->orderBy('item_code')
            ->get();
    }
}