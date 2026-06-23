<div class="grid grid-cols-1 gap-4 md:grid-cols-4">

    @forelse($this->transactionItems as $item)

        <button
            type="button"
            wire:click="toggleTransactionItem('{{ $item['item_code'] }}')"

            @class([
                'rounded-xl border p-5 text-left transition',

                'border-primary-600 bg-primary-50 shadow'
                    => in_array($item['item_code'], $this->selectedTransactionItems),

                'border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800'
                    => ! in_array($item['item_code'], $this->selectedTransactionItems),
            ])
        >

            <div class="flex justify-between">

                <div class="font-bold">
                    {{ $item['item_code'] }}
                </div>


                @if(in_array($item['item_code'], $this->selectedTransactionItems))

                    <div class="text-primary-600">
                        ✓
                    </div>

                @endif

            </div>


            <div class="mt-3 text-sm text-gray-500">
                {{ $item['item_details'] }}
            </div>


            <div class="mt-5 text-lg font-bold">

                ₱{{ number_format($item['item_amount'], 2) }}

            </div>

        </button>


    @empty

        <div class="col-span-3 p-6 text-center text-gray-500">

            No transaction items available.

        </div>

    @endforelse

</div>
