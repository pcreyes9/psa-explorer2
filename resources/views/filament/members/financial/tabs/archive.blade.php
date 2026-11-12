<div
    class="space-y-4"
    x-data="{
        open: false,
        payment: null,

        formatDate(value) {
            if (! value) {
                return '—';
            }

            return new Date(value).toLocaleDateString('en-PH', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            });
        },

        formatAmount(value) {
            return '₱' + Number(value ?? 0).toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },
    }"
    @keydown.escape.window="open = false"
>
    <div class="overflow-x-auto">

    <table class="w-full text-sm border border-gray-200 dark:border-gray-700">

        <thead class="bg-gray-100 dark:bg-gray-800">

            <tr>

                <th class="p-3 text-left">
                    Date
                </th>

                <th class="p-3 text-left">
                    OR Number
                </th>

                <th class="p-3 text-left">
                    Payment Type
                </th>

                <th class="p-3 text-right">
                    Amount
                </th>

                <th class="p-3 text-left">
                    Processed By
                </th>

                <th class="p-3 text-left">
                    Remarks
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse ($archives as $archive)

                <tr
                    class="cursor-pointer border-t border-gray-200 transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800/60"
                    @click='payment = @json($archive); open = true;'
                >

                    <td class="p-3">
                        {{ $archive->payment_date }}
                    </td>

                    <td class="p-3">
                        {{ $archive->or_no }}
                    </td>

                    <td class="p-3">
                        {{ $archive->payment_type }}
                    </td>

                    <td class="p-3 text-right font-medium">
                        ₱{{ number_format($archive->payment_total_amt, 2) }}
                    </td>

                    <td class="p-3">
                        {{ $archive->userid }}
                    </td>

                    <td class="p-3">
                        {{ $archive->remarks }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td
                        colspan="6"
                        class="p-8 text-center text-gray-500"
                    >
                        No archived payment records found.
                    </td>

                </tr>

            @endforelse

        </tbody>

        @if ($archives->isNotEmpty())

            <tfoot>

                <tr class="border-t bg-gray-50 dark:bg-gray-800 font-semibold">

                    <td colspan="3" class="p-3">
                        Total
                    </td>

                    <td class="p-3 text-right">
                        ₱{{ number_format($archives->sum('payment_total_amt'), 2) }}
                    </td>

                    <td colspan="2"></td>

                </tr>

            </tfoot>

        @endif

    </table>

    </div>

    <div
        x-show="open"
        x-cloak
        x-transition.opacity
        @click.self="open = false"
        class="fixed inset-0
               z-50
               flex items-center justify-center
               bg-black/50 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="archived-payment-details-title"
    >
        <div
            x-transition.scale
            class="bg-white
                   rounded-2xl
                   shadow-2xl
                   w-full
                   max-w-3xl
                   max-h-[85vh]
                   flex flex-col
                   overflow-hidden"
        >

            <div
                class="px-6 py-4
                       border-b
                       flex justify-between items-center"
            >
                <div>
                    <h2
                        id="archived-payment-details-title"
                        class="text-xl font-bold"
                    >
                        Archived Payment Details
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        <span x-text="payment?.or_no || 'No official receipt'"></span>
                    </p>
                </div>

                <button
                    type="button"
                    @click="open = false"
                    class="text-2xl text-gray-500
                           hover:text-red-600"
                    aria-label="Close archived payment details"
                >
                    ×
                </button>
            </div>

            <div
                class="flex-1
                       overflow-y-auto
                       p-6
                       custom-scrollbar"
            >
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-gray-500">Payment Date</div>
                        <div
                            class="font-semibold"
                            x-text="formatDate(payment?.payment_date)"
                        ></div>
                    </div>

                    <div>
                        <div class="text-gray-500">Official Receipt</div>
                        <div
                            class="font-semibold"
                            x-text="payment?.or_no || '—'"
                        ></div>
                    </div>

                    <div>
                        <div class="text-gray-500">Payment Type</div>
                        <div
                            class="font-semibold"
                            x-text="payment?.payment_type || '—'"
                        ></div>
                    </div>

                    <div>
                        <div class="text-gray-500">Payment Amount</div>
                        <div
                            class="font-semibold"
                            x-text="formatAmount(payment?.payment_total_amt)"
                        ></div>
                    </div>

                    <div>
                        <div class="text-gray-500">Processed By</div>
                        <div
                            class="font-semibold"
                            x-text="payment?.userid || '—'"
                        ></div>
                    </div>

                    <div class="col-span-2">
                        <div class="text-gray-500">Remarks</div>
                        <div
                            class="font-semibold"
                            x-text="payment?.remarks || '—'"
                        ></div>
                    </div>
                </div>
            </div>

            <div
                class="px-6 py-4
                       border-t
                       bg-gray-50
                       flex justify-end"
            >
                <button
                    type="button"
                    @click="open = false"
                    class="px-5 py-2
                           rounded-lg
                           bg-gray-700
                           hover:bg-gray-800
                           text-white"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
