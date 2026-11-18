<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Payment Search</x-slot>
        <x-slot name="description">Search payments by selecting one search criterion below.</x-slot>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div>
                <label class="mb-3 block text-sm font-medium">Search By</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="radio" wire:model.live="searchBy" value="payment_ref_no">
                        <span>Reference Number</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="radio" wire:model.live="searchBy" value="or_no">
                        <span>OR Number</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="radio" wire:model.live="searchBy" value="member_id_no">
                        <span>PSA ID Number</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="radio" wire:model.live="searchBy" value="last_name">
                        <span>Last Name</span>
                    </label>
                </div>
            </div>

            <div>
                <x-filament::input.wrapper>
                    <x-filament::input
                        wire:model="searchValue"
                        wire:keydown.enter="search"
                        placeholder="{{ $this->searchPlaceholder }}"
                    />
                </x-filament::input.wrapper>

                @error('searchValue')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                @enderror

                <div class="mt-5 flex justify-end gap-3">
                    <x-filament::button
                        icon="heroicon-m-magnifying-glass"
                        wire:click="search"
                        wire:loading.attr="disabled"
                        wire:target="search"
                    >
                        Search
                    </x-filament::button>
                    <x-filament::button
                        color="gray"
                        icon="heroicon-m-arrow-path"
                        wire:click="resetSearch"
                    >
                        Reset
                    </x-filament::button>
                </div>
            </div>
        </div>
    </x-filament::section>

    @if ($searched)
        @php
            $results = $this->searchResults;
            $paymentsForModal = $results->values()->all();
        @endphp

        <div
            x-data="{
                open: false,
                payment: null,
                payments: @js($paymentsForModal),
                formatAmount(amount) {
                    return Number(amount ?? 0).toLocaleString('en-PH', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },
                formatDate(date) {
                    if (!date) return '—';
                    const parsed = new Date(date);
                    return Number.isNaN(parsed.getTime())
                        ? date
                        : parsed.toLocaleDateString('en-PH', {
                            year: 'numeric',
                            month: 'short',
                            day: '2-digit'
                        });
                }
            }"
            @keydown.escape.window="open = false"
        >
            <x-filament::section>
                <x-slot name="heading">Search Results</x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="p-3 text-left">Date</th>
                                <th class="p-3 text-left">Reference No.</th>
                                <th class="p-3 text-left">OR No.</th>
                                <th class="p-3 text-left">PSA ID No.</th>
                                <th class="p-3 text-left">Member Name</th>
                                <th class="p-3 text-right">Amount</th>
                                <th class="p-3 text-left">Payment Type</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($results as $index => $payment)
                                <tr
                                    class="cursor-pointer transition-colors hover:bg-primary-50 dark:hover:bg-gray-800"
                                    @click="payment = payments[{{ $index }}]; open = true"
                                    title="Click to view payment details"
                                >
                                    <td class="p-3">{{ $payment->payment_date?->format('M d, Y') ?? '—' }}</td>
                                    <td class="p-3">{{ $payment->payment_ref_no }}</td>
                                    <td class="p-3">{{ $payment->or_no ?: '—' }}</td>
                                    <td class="p-3">{{ $payment->member_id_no }}</td>
                                    <td class="p-3">{{ $payment->payment_name ?: '—' }}</td>
                                    <td class="p-3 text-right font-medium">₱{{ number_format((float) $payment->payment_total_amt, 2) }}</td>
                                    <td class="p-3">{{ $payment->payment_type ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-gray-500">No payments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($results->hasPages())
                    <div class="mt-4">{{ $results->links() }}</div>
                @endif
            </x-filament::section>

            <div
                x-show="open"
                x-cloak
                x-transition.opacity
                @click.self="open = false"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            >
                <div
                    x-transition.scale
                    class="flex max-h-[85vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900"
                >
                    <div class="flex items-center justify-between border-b px-6 py-4 dark:border-gray-700">
                        <h2 class="text-xl font-bold">Payment Details</h2>
                        <button type="button" @click="open = false" class="text-2xl text-gray-500 hover:text-red-600">×</button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <div class="text-gray-500">Payment Date</div>
                                <div class="font-semibold" x-text="formatDate(payment?.payment_date)"></div>
                            </div>
                            <div>
                                <div class="text-gray-500">Reference No.</div>
                                <div class="font-semibold" x-text="payment?.payment_ref_no || '—'"></div>
                            </div>
                            <div>
                                <div class="text-gray-500">OR Number</div>
                                <div class="font-semibold" x-text="payment?.or_no || '—'"></div>
                            </div>
                            <div>
                                <div class="text-gray-500">Payment Type</div>
                                <div class="font-semibold" x-text="payment?.payment_type || '—'"></div>
                            </div>
                            <div>
                                <div class="text-gray-500">PSA ID</div>
                                <div class="font-semibold" x-text="payment?.member_id_no || '—'"></div>
                            </div>
                            <div>
                                <div class="text-gray-500">Processed By</div>
                                <div class="font-semibold" x-text="payment?.userid || '—'"></div>
                            </div>
                        </div>

                        <hr class="my-6">
                        <h3 class="mb-3 text-lg font-bold">Payment Breakdown</h3>

                        <div class="max-h-80 overflow-y-auto rounded-lg border dark:border-gray-700">
                            <table class="w-full text-sm">
                                <thead class="sticky top-0 z-10 bg-gray-100 dark:bg-gray-800">
                                    <tr>
                                        <th class="p-3 text-left">Description</th>
                                        <th class="p-3 text-center">Fiscal Year</th>
                                        <th class="p-3 text-left">Transaction Code</th>
                                        <th class="p-3 text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="item in (payment?.payment_items ?? [])" :key="item.item_code + '-' + item.fiscal_year + '-' + item.tran_code">
                                        <tr class="border-t dark:border-gray-700">
                                            <td class="p-3" x-text="item.transaction_type_item?.item_details || item.item_code || '—'"></td>
                                            <td class="p-3 text-center" x-text="item.transaction_type_item?.fiscal_year || item.fiscal_year || '—'"></td>
                                            <td class="p-3" x-text="item.tran_code || '—'"></td>
                                            <td class="p-3 text-right" x-text="'₱' + formatAmount(item.amount_due)"></td>
                                        </tr>
                                    </template>
                                    <tr x-show="!(payment?.payment_items ?? []).length">
                                        <td colspan="4" class="p-6 text-center text-gray-500">No payment items found.</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="border-t bg-gray-50 font-bold dark:border-gray-700 dark:bg-gray-800">
                                        <td colspan="3" class="p-3 text-right">TOTAL</td>
                                        <td class="p-3 text-right" x-text="'₱' + formatAmount(payment?.payment_total_amt)"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="mt-6">
                            <div class="font-semibold">Remarks</div>
                            <div class="mt-2 text-gray-600 dark:text-gray-300" x-text="payment?.remarks || 'None'"></div>
                        </div>
                    </div>

                    <div class="flex justify-end border-t bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                        <button type="button" @click="open = false" class="rounded-lg bg-gray-700 px-5 py-2 text-white hover:bg-gray-800">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
