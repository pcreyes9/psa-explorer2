<div class="grid grid-cols-1 gap-4 md:grid-cols-4">

    @php
        $items = collect($this->transactionItems);

        /*
         * Group membership fees using tran_code = MEMF.
         * item_code can be different for each membership type.
         */
        $memfItems = $items->filter(
            fn ($item) => strtoupper(trim($item['tran_code'] ?? '')) === 'MEMF'
        );

        $otherItems = $items->reject(
            fn ($item) => strtoupper(trim($item['tran_code'] ?? '')) === 'MEMF'
        );

        $memfTotal = $memfItems->sum(
            fn ($item) => (float) ($item['item_amount'] ?? 0)
        );

        /*
         * Use individual item codes to determine which MEMF entries
         * have been selected.
         */
        $memfCodes = $memfItems
            ->pluck('item_code')
            ->unique()
            ->values()
            ->all();

        $memfSelected = count($memfCodes) > 0
            && collect($memfCodes)->every(
                fn ($code) => in_array($code, $this->selectedTransactionItems)
            );
    @endphp

    {{-- GROUPED MEMF CARD --}}
    @if ($memfItems->isNotEmpty())
        <div
            @class([
                'rounded-xl border p-5 transition',
                'border-primary-600 bg-primary-50 shadow dark:bg-primary-950'
                    => $memfSelected,
                'border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800'
                    => ! $memfSelected,
            ])
        >
            <button
                type="button"
                wire:click="toggleMemfItems"
                class="w-full text-left"
            >
                <div class="flex items-center justify-between gap-2">
                    <div class="font-bold text-gray-950 dark:text-white">
                        MEMF
                    </div>

                    <div class="text-xs font-medium text-primary-600">
                        {{ $memfItems->count() }} item(s)
                    </div>
                </div>

                <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                    Membership Fee
                </div>

                <div class="mt-5 text-lg font-bold text-gray-950 dark:text-white">
                    ₱{{ number_format($memfTotal, 2) }}
                </div>

                {{-- <div class="mt-2 text-xs font-medium text-primary-600">
                    {{ $memfSelected ? '✓ Selected' : 'Click to select all' }}
                </div> --}}
            </button>

            {{-- MEMF BREAKDOWN --}}
            {{-- <div class="mt-4 border-t border-gray-200 pt-3 dark:border-gray-700">
                <div class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Membership Fee Breakdown
                </div>

                <div class="space-y-3">
                    @foreach ($memfItems as $memfItem)
                        <div class="flex items-start justify-between gap-3 text-sm">
                            <div class="min-w-0">
                                <div class="font-medium text-gray-800 dark:text-gray-200">
                                    {{ $memfItem['item_code'] }}
                                </div>

                                <div class="mt-1 text-gray-500 dark:text-gray-400">
                                    {{ $memfItem['item_details'] ?? 'Membership Fee' }}
                                </div>
                            </div>

                            <div class="shrink-0 font-semibold text-gray-900 dark:text-white">
                                ₱{{ number_format((float) ($memfItem['item_amount'] ?? 0), 2) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div> --}}
        </div>
    @endif

    {{-- OTHER TRANSACTION ITEMS --}}
    @foreach ($otherItems as $item)
        @php
            $itemCode = $item['item_code'] ?? '';
            $isSelected = in_array($itemCode, $this->selectedTransactionItems);
        @endphp

        <button
            type="button"
            wire:click="toggleTransactionItem(@js($itemCode))"
            @class([
                'rounded-xl border p-5 text-left transition',
                'border-primary-600 bg-primary-50 shadow dark:bg-primary-950'
                    => $isSelected,
                'border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800'
                    => ! $isSelected,
            ])
        >
            <div class="flex items-center justify-between gap-2">
                <div class="font-bold text-gray-950 dark:text-white">
                    {{ $itemCode }}
                </div>

                @if ($isSelected)
                    <div class="text-primary-600">✓</div>
                @endif
            </div>

            <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                {{ $item['item_details'] ?? '' }}
            </div>

            <div class="mt-5 text-lg font-bold text-gray-950 dark:text-white">
                ₱{{ number_format((float) ($item['item_amount'] ?? 0), 2) }}
            </div>
        </button>
    @endforeach

    {{-- EMPTY STATE --}}
    @if ($items->isEmpty())
        <div class="col-span-full p-6 text-center text-gray-500">
            No transaction items available.
        </div>
    @endif

</div>