<div class="space-y-4">

    {{-- Header --}}
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

    {{-- CME Payments Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">

        <div class="overflow-x-auto">

            <table class="w-full min-w-[1150px] divide-y divide-gray-200 dark:divide-gray-700">

                {{-- Table Header --}}
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

                {{-- Table Body --}}
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">

                    @forelse ($cmePayments as $payment)

                        <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">

                            {{-- CME Year --}}
                            <td class="whitespace-nowrap px-4 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                {{ $payment->cme_year }}
                            </td>

                            {{-- Program Code --}}
                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-medium">
                                    {{ $payment->cme_program_code }}
                                </span>
                            </td>

                            {{-- Registration Date --}}
                            {{-- <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                @if ($payment->cme_reg_date)
                                    {{ \Carbon\Carbon::parse($payment->cme_reg_date)->format('M d, Y') }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td> --}}

                            {{-- Payment Date --}}
                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                @if ($payment->payment_date)
                                    {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Payment Reference --}}
                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ $payment->payment_ref_no ?: '—' }}
                            </td>

                            {{-- Payment Amount --}}
                            <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-medium text-gray-900 dark:text-white">
                                ₱{{ number_format((float) $payment->payment_total_amt, 2) }}
                            </td>

                            {{-- Discounted Amount --}}
                            <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-gray-700 dark:text-gray-300">
                                ₱{{ number_format((float) $payment->payment_discounted_amt, 2) }}
                            </td>

                            {{-- Payment Type --}}
                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $payment->payment_type ?: '—' }}
                            </td>

                            {{-- Official Receipt --}}
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

    {{-- Note --}}
    <p class="text-xs text-gray-500 dark:text-gray-400">
        Payment amounts are displayed according to the values recorded in the payments table.
    </p>

</div>