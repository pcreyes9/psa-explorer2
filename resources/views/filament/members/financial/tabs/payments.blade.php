@php
    $payments = $record->payments()
    ->orderByDesc('payment_date')
    ->get();
@endphp

<div class="overflow-x-auto">

    
    <table class="w-full text-sm border border-gray-200 dark:border-gray-700">

        <thead class="bg-gray-100 dark:bg-gray-800">

            <tr>

                <th class="p-3 text-left">
                    Date
                </th>

                <th class="p-3 text-left">
                    OR No.
                </th>

                <th class="p-3 text-left">
                    Payment Type
                </th>

                <th class="p-3 text-right">
                    Amount
                </th>

                <th class="p-3 text-left">
                    Remarks
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse ($payments as $payment)

                <tr class="border-t border-gray-200 dark:border-gray-700">

                    <td class="p-3">
                        {{ $payment->payment_date }}
                    </td>

                    <td class="p-3">
                        {{ $payment->or_no }}
                    </td>

                    <td class="p-3">
                        {{ $payment->payment_type }}
                    </td>

                    <td class="p-3 text-right font-medium">
                        ₱{{ number_format($payment->payment_total_amt, 2) }}
                    </td>

                    <td class="p-3">
                        {{ $payment->remarks }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td
                        colspan="5"
                        class="p-6 text-center text-gray-500"
                    >
                        No payment history found.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>
    

</div>
