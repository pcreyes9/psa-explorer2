<x-filament::section
    heading="Partial or Incomplete Payments"
    description="Charges with a payment applied but a remaining balance."
>
    <div class="overflow-x-auto">
        <table class="w-full border border-gray-200 text-sm dark:border-gray-700">
            <thead class="bg-gray-100 dark:bg-gray-800">
                <tr>
                    <th class="p-3 text-left">Fiscal Year</th>
                    <th class="p-3 text-left">Transaction</th>
                    <th class="p-3 text-left">Item Code</th>
                    <th class="p-3 text-left">Description</th>
                    <th class="p-3 text-right">Amount Due</th>
                    <th class="p-3 text-right">Paid</th>
                    <th class="p-3 text-right">Remaining</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($incompletePayments as $payment)
                    <tr class="border-t border-gray-200 dark:border-gray-700">
                        <td class="p-3 font-medium whitespace-nowrap">
                            {{ $payment->fiscal_year }}
                        </td>
                        <td class="p-3 font-medium whitespace-nowrap">
                            {{ $payment->tran_code }}
                        </td>
                        <td class="p-3 whitespace-nowrap">
                            {{ $payment->item_code }}
                        </td>
                        <td class="p-3">
                            {{ $payment->item_details ?: '—' }}
                        </td>
                        <td class="p-3 text-right whitespace-nowrap">
                            ₱{{ number_format((float) $payment->amount_due, 2) }}
                        </td>
                        <td class="p-3 text-right whitespace-nowrap">
                            ₱{{ number_format((float) $payment->amount_paid, 2) }}
                        </td>
                        <td class="p-3 text-right font-semibold text-danger-600 whitespace-nowrap dark:text-danger-400">
                            ₱{{ number_format((float) $payment->balance, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-gray-500">
                            No partial or incomplete payments found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if (count($incompletePayments))
                <tfoot>
                    <tr class="border-t bg-warning-50 font-semibold dark:border-gray-700 dark:bg-warning-900/20">
                        <td colspan="4" class="p-3">Totals</td>
                        <td class="p-3 text-right">
                            ₱{{ number_format(collect($incompletePayments)->sum('amount_due'), 2) }}
                        </td>
                        <td class="p-3 text-right">
                            ₱{{ number_format(collect($incompletePayments)->sum('amount_paid'), 2) }}
                        </td>
                        <td class="p-3 text-right text-danger-600 dark:text-danger-400">
                            ₱{{ number_format(collect($incompletePayments)->sum('balance'), 2) }}
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</x-filament::section>
