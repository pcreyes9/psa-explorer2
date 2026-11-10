<?php

namespace App\Support;

use App\Models\Member;
use App\Models\MemberLedger;
use App\Models\Payment;
use App\Models\TransactionTypeItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PaymentManager
{
    /**
     * Generate a payment reference number.
     *
     * Existing reference format is retained.
     */
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
            ->where(
                'payment_ref_no',
                'like',
                "{$prefix}{$datePart}%"
            )
            ->orderByDesc('payment_ref_no')
            ->first();

        $nextSequence = $latest
            ? (int) substr($latest->payment_ref_no, -5) + 1
            : 1;

        return sprintf(
            '%s%s%05d',
            $prefix,
            $datePart,
            $nextSequence
        );
    }

    /**
     * LEGACY METHOD.
     *
     * Retained for existing features that still depend on member_ledger.
     *
     * Do not use this method for new Payment Posting calculations.
     */
    public static function getMemberBalances(Member $member): array
    {
        $items = TransactionTypeItem::query()
            ->where('charge_code', $member->psa_mem_type)
            ->where('stat', 1)
            ->orderBy('tran_code')
            ->orderBy('item_code')
            ->get();

        $balances = [];

        foreach ($items as $item) {
            $due = (float) MemberLedger::query()
                ->where('member_id_no', $member->member_id_no)
                ->where('item_code', $item->item_code)
                ->where('entry_type', 0)
                ->sum('amt');

            $paid = (float) MemberLedger::query()
                ->where('member_id_no', $member->member_id_no)
                ->where('item_code', $item->item_code)
                ->where('entry_type', 1)
                ->sum('amt');

            if ($due <= 0) {
                continue;
            }

            $balance = max($due - $paid, 0);

            $status = match (true) {
                $balance <= 0 => 'Paid',
                $paid > 0 => 'Partial',
                default => 'Unpaid',
            };

            $balances[] = [
                'tran_code' => $item->tran_code,
                'item_code' => $item->item_code,
                'description' => $item->item_details,
                'due' => $due,
                'paid' => $paid,
                'balance' => $balance,
                'status' => $status,
            ];
        }

        return $balances;
    }

    /**
     * Retrieve available transaction items for a member.
     */
    public static function getAvailableItems(Member $member): Collection
    {
        return TransactionTypeItem::query()
            ->where('charge_code', $member->psa_mem_type)
            ->where('stat', 1)
            ->orderBy('tran_code')
            ->orderBy('item_code')
            ->get();
    }

    /**
     * Retrieve transaction items and calculate outstanding balances.
     *
     * OPTIMIZED:
     * Instead of querying payment history separately for every item,
     * retrieve all payment allocations in one grouped SQL query.
     *
     * New Payment Posting calculations use:
     * - payments
     * - payment_items
     *
     * They do not use member_ledger or member_ledger_bal.
     */
    public static function getTransactionItems(
        Member $member,
        int $fiscalYear
    ): Collection {

        /*
         * 1. Retrieve active transaction item definitions.
         */
        $items = TransactionTypeItem::query()
            ->where('charge_code', $member->psa_mem_type)
            ->where('fiscal_year', (string) $fiscalYear)
            ->where('stat', 1)
            ->orderBy('tran_code')
            ->orderBy('item_code')
            ->get();

        if ($items->isEmpty()) {
            return collect();
        }

        /*
         * 2. Retrieve all active payment allocations in ONE query.
         *
         * Historical records:
         * amount_paid is NULL, so amount_due is used as fallback.
         *
         * New records:
         * amount_paid contains the actual collected amount.
         */
        $payments = DB::table('payment_items as pi')
            ->join(
                'payments as p',
                'p.payment_ref_no',
                '=',
                'pi.payment_ref_no'
            )
            ->select([
                'pi.item_code',
                'pi.tran_code',
                'pi.fiscal_year',
            ])
            ->selectRaw(
                'SUM(
                    COALESCE(pi.amount_paid, pi.amount_due)
                ) AS total_paid'
            )
            ->where('pi.member_id_no', $member->member_id_no)
            ->where('pi.fiscal_year', (string) $fiscalYear)
            ->where('pi.stat', 1)
            ->where('p.stat', 1)
            ->groupBy(
                'pi.item_code',
                'pi.tran_code',
                'pi.fiscal_year'
            )
            ->get();

        /*
         * 3. Convert payment allocations into a lookup collection.
         *
         * This avoids repeatedly searching the payment collection
         * while processing transaction items.
         */
        $paidAmounts = [];

        foreach ($payments as $payment) {
            $key = self::makeItemKey(
                $payment->item_code,
                $payment->tran_code,
                $payment->fiscal_year
            );

            $paidAmounts[$key] = (float) $payment->total_paid;
        }

        /*
         * 4. Calculate balances for each transaction item.
         */
        return $items->map(function ($item) use (
            $paidAmounts,
            $fiscalYear
        ) {
            $originalAmount = (float) $item->item_amount;

            $key = self::makeItemKey(
                $item->item_code,
                $item->tran_code,
                $item->fiscal_year
            );

            $previouslyPaid = (float) (
                $paidAmounts[$key] ?? 0
            );

            $balance = round(
                max($originalAmount - $previouslyPaid, 0),
                2
            );

            $status = match (true) {
                $balance <= 0 => 'Paid',
                $previouslyPaid > 0 => 'Partial',
                default => 'Unpaid',
            };

            /*
             * Preserve the original model fields because
             * the existing modal uses item_amount and item_details.
             */
            return array_merge(
                $item->toArray(),
                [
                    'fiscal_year' => (string) $fiscalYear,
                    'original_amount' => $originalAmount,
                    'previously_paid' => $previouslyPaid,
                    'balance' => $balance,
                    'amount_paying' => $balance,
                    'payment_status' => $status,
                ]
            );
        });
    }

    /**
     * Generate a consistent lookup key for transaction items.
     */
    protected static function makeItemKey(
        ?string $itemCode,
        ?string $tranCode,
        ?string $fiscalYear
    ): string {
        return implode('|', [
            trim((string) $fiscalYear),
            trim((string) $tranCode),
            trim((string) $itemCode),
        ]);
    }
}
