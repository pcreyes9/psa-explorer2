@php
    $payments = $record->payments()
        ->with('paymentItems.transactionTypeItem')
        ->orderByDesc('payment_date')
        ->get();
@endphp

<div
    x-data="{
        open: false,
        payment: null
    }"
>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f3f4f6;
            border-radius: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #9ca3af;
            border-radius: 8px;
            border: 2px solid #f3f4f6;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background-color: #6b7280;
        }
        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #9ca3af #f3f4f6;
        }
    </style>

    <div class="overflow-x-auto">

        <table class="w-full text-sm border border-gray-200">

            <thead class="bg-gray-100">

                <tr>

                    <th class="p-3 text-left">Date</th>
                    <th class="p-3 text-left">Reference No.</th>
                    <th class="p-3 text-left">OR No.</th>
                    <th class="p-3 text-left">Payment Type</th>
                    <th class="p-3 text-right">Amount</th>
                    <th class="p-3 text-left">Remarks</th>

                </tr>

            </thead>

            <tbody>

                @forelse($payments as $payment)

                    <tr
                        class="cursor-pointer hover:bg-blue-50 transition"
                        @click='payment = @json($payment); open = true'
                    >

                        <td class="p-3">
                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}
                        </td>

                        <td class="p-3">
                            {{ $payment->payment_ref_no }}
                        </td>

                        <td class="p-3">
                            {{ $payment->or_no }}
                        </td>

                        <td class="p-3">
                            {{ $payment->payment_type }}
                        </td>

                        <td class="p-3 text-right font-semibold">
                            ₱{{ number_format($payment->payment_total_amt,2) }}
                        </td>

                        <td class="p-3">
                            {{ $payment->remarks }}
                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="6"
                            class="text-center p-6 text-gray-500">

                            No payment history found.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <!-- PAYMENT DETAILS -->

    <div
        x-show="open"
        x-cloak
        x-transition.opacity
        @click.self="open=false"
        @keydown.escape.window="open=false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >

        <div
            x-transition.scale
            class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl h-[85vh] flex flex-col overflow-hidden"
        >

            <div class="px-6 py-4 border-b flex justify-between items-center">

                <h2 class="text-xl font-bold">
                    Payment Details
                </h2>

                <button
                    @click="open=false"
                    class="text-2xl text-gray-500 hover:text-red-600"
                >
                    ×
                </button>

            </div>

            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">

                <div class="grid grid-cols-2 gap-4 text-sm">

                    <div>

                        <div class="text-gray-500">
                            Payment Date
                        </div>

                        <div
                            class="font-semibold"
                            x-text="payment.payment_date"
                        ></div>

                    </div>

                    <div>

                        <div class="text-gray-500">
                            Reference No.
                        </div>

                        <div
                            class="font-semibold"
                            x-text="payment.payment_ref_no"
                        ></div>

                    </div>

                    <div>

                        <div class="text-gray-500">
                            OR No.
                        </div>

                        <div
                            class="font-semibold"
                            x-text="payment.or_no"
                        ></div>

                    </div>

                    <div>

                        <div class="text-gray-500">
                            Payment Type
                        </div>

                        <div
                            class="font-semibold"
                            x-text="payment.payment_type"
                        ></div>

                    </div>

                </div>

                <hr class="my-6">

                <h3 class="font-bold text-lg mb-3">

                    Payment Breakdown

                </h3>

                <div class="max-h-80 overflow-y-auto border rounded-lg custom-scrollbar">

                    <table class="w-full border text-sm">

                        <thead class="sticky top-0 bg-gray-100 z-10">

                            <tr>

                                <th class="text-left p-3">
                                    Description
                                </th>

                                <th class="text-center p-3">
                                    Fiscal Year
                                </th>

                                <th class="text-right p-3">
                                    Amount
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <template
                                x-for="item in payment.payment_items"
                                :key="item.item_code"
                            >

                                <tr class="border-t">

                                    <td class="p-3">

                                        <span
                                            x-text="item.transaction_type_item.item_details"
                                        ></span>

                                    </td>

                                    <td class="p-3 text-center">

                                        <span
                                            x-text="item.transaction_type_item.fiscal_year"
                                        ></span>

                                    </td>

                                    <td class="p-3 text-right">

                                        <span
                                            x-text="'₱' + Number(item.amount_due).toLocaleString(undefined,{
                                                minimumFractionDigits:2,
                                                maximumFractionDigits:2
                                            })"
                                        ></span>

                                    </td>

                                </tr>

                            </template>

                        </tbody>

                        <tfoot>

                            <tr class="border-t bg-gray-50 font-bold">

                                <td class="p-3">
                                    TOTAL
                                </td>

                                <td class="p-3">

                                </td>

                                <td
                                    class="p-3 text-right"
                                    x-text="'₱' + Number(payment.payment_total_amt).toLocaleString(undefined,{
                                        minimumFractionDigits:2,
                                        maximumFractionDigits:2
                                    })"
                                ></td>

                            </tr>

                        </tfoot>

                    </table>

                </div>

                <div class="mt-6">

                    <div class="font-semibold">

                        Remarks

                    </div>

                    <div
                        class="mt-2 text-gray-600"
                        x-text="payment.remarks || 'None'"
                    ></div>

                </div>

            </div>

            <div class="px-6 py-4 border-t bg-gray-50 flex justify-end">

                <button
                    @click="open=false"
                    class="px-5 py-2 rounded-lg bg-gray-700 hover:bg-gray-800 text-white"
                >

                    Close

                </button>

            </div>

        </div>

    </div>

</div>