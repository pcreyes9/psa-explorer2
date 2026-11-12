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

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                CME Payments
            </h3>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Continuing Medical Education registration and payment history.
            </p>
        </div>

        <div class="inline-flex items-center gap-2 self-start rounded-lg bg-gray-100 px-3 py-2 text-sm text-gray-700 dark:bg-gray-800 dark:text-gray-300">
            <x-heroicon-o-document-text class="h-4 w-4" />

            <span>
                {{ $cmePayments->count() }}
                {{ $cmePayments->count() === 1 ? 'record' : 'records' }}
            </span>
        </div>

    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">

        <div class="overflow-x-auto">

            <table class="w-full min-w-[1150px] divide-y divide-gray-200 dark:divide-gray-700">

                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                            CME Year
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                            Program Code
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                            Registration Date
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                            Payment Date
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                            Payment Reference
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                            Payment Amount
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                            Discounted Amount
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                            Payment Type
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                            Official Receipt
                        </th>

                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">

                    @forelse ($cmePayments as $payment)

                        <tr
                            class="cursor-pointer transition hover:bg-gray-50 dark:hover:bg-gray-800/60"
                            @click='payment = @json($payment); open = true;'
                        >

                            <td class="whitespace-nowrap px-4 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                {{ $payment->cme_year }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-medium">
                                    {{ $payment->cme_program_code }}
                                </span>
                            </td>

                            {{-- <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                @if ($payment->cme_reg_date)
                                    {{ \Carbon\Carbon::parse($payment->cme_reg_date)->format('M d, Y') }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td> --}}

                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                @if ($payment->payment_date)
                                    {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ $payment->payment_ref_no ?: '—' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-medium text-gray-900 dark:text-white">
                                ₱{{ number_format((float) $payment->payment_total_amt, 2) }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-gray-700 dark:text-gray-300">
                                ₱{{ number_format((float) $payment->payment_discounted_amt, 2) }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $payment->payment_type ?: '—' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ filled($payment->or_no) ? $payment->or_no : '—' }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">

                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                    <x-heroicon-o-document-text class="h-6 w-6 text-gray-400" />
                                </div>

                                <h4 class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">
                                    No CME payments found
                                </h4>

                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    No matching CME registration and payment records were found for this member.
                                </p>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

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
        aria-labelledby="cme-payment-details-title"
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
                        id="cme-payment-details-title"
                        class="text-xl font-bold"
                    >
                        CME Payment Details
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        <span x-text="payment?.cme_program_code || '—'"></span>
                    </p>
                </div>

                <button
                    type="button"
                    @click="open = false"
                    class="text-2xl text-gray-500
                           hover:text-red-600"
                    aria-label="Close payment details"
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
                        <div class="text-gray-500">CME Year</div>
                        <div
                            class="font-semibold"
                            x-text="payment?.cme_year || '—'"
                        ></div>
                    </div>

                    <div>
                        <div class="text-gray-500">Program Code</div>
                        <div
                            class="font-semibold"
                            x-text="payment?.cme_program_code || '—'"
                        ></div>
                    </div>

                    <div>
                        <div class="text-gray-500">Registration Date</div>
                        <div
                            class="font-semibold"
                            x-text="formatDate(payment?.cme_reg_date)"
                        ></div>
                    </div>

                    <div>
                        <div class="text-gray-500">Payment Date</div>
                        <div
                            class="font-semibold"
                            x-text="formatDate(payment?.payment_date)"
                        ></div>
                    </div>

                    <div>
                        <div class="text-gray-500">Payment Reference</div>
                        <div
                            class="font-semibold"
                            x-text="payment?.payment_ref_no || '—'"
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
                        <div class="text-gray-500">Payment Status</div>
                        <div
                            class="font-semibold"
                            x-text="payment?.payment_status == 1 ? 'Paid' : (payment?.payment_status || '—')"
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
                        <div class="text-gray-500">Discounted Amount</div>
                        <div
                            class="font-semibold"
                            x-text="formatAmount(payment?.payment_discounted_amt)"
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

    <p class="text-xs text-gray-500 dark:text-gray-400">
        Payment amounts are displayed according to the values recorded in the payments table.
    </p>

</div>
