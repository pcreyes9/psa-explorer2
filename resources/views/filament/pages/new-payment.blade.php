<x-filament-panels::page>

    <div class="space-y-4">

        @if($this->selectedMember)

            <div class="flex items-center gap-4">

                <img
                    src="{{ $this->selectedMember->photo_url ?? asset('images/default-avatar.png') }}"
                    alt="Member Photo"
                    class="h-40 w-40 rounded-full border-4 border-gray-300 object-cover object-[50%_20%] shadow-sm"
                >

                <div>

                    <div class="text-sm text-gray-500">
                        {{ $this->selectedMember->member_id_no }}
                    </div>

                    <div class="text-2xl font-bold">
                        {{ $this->selectedMember->mem_last_name }},
                        {{ $this->selectedMember->mem_first_name }}
                    </div>

                    <div class="mt-1 text-sm text-gray-500">

                        {{ $this->selectedMember->membershipType?->Memtype
                            ?? $this->selectedMember->psa_mem_type }}

                        ·

                        {{ $this->selectedMember->chapter?->psa_chapter_desc
                            ?? $this->selectedMember->psa_chapter_code }}

                    </div>

                </div>

            </div>

        @endif

        <div class="flex items-end gap-4">

            <div class="flex-1">
                {{  $this->form }}
            </div>

            <div class="flex gap-2 pb-1">

                {{ $this->transactionItemsAction }}

                <x-filament::button
                    color="success"
                    icon="heroicon-o-check-circle"
                    wire:click="postPayment"
                    :disabled="count($this->selectedItems) === 0"
                >
                    Post Payment
                </x-filament::button>

            </div>

        </div>

    </div>

    @if(count($this->selectedItems))

        <div class="mt-2 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b bg-gray-50 px-6 py-4">
                <h2 class="text-lg font-semibold">
                    Selected Transaction Items | Payment Reference #:
                </h2>
            </div>

            <div class="overflow-x-auto">

                <table class="w-full text-sm border border-gray-200 dark:border-gray-700">

                    <thead class="bg-gray-100 dark:bg-gray-800">

                        <tr>
                            <th class="p-3">
                                Year
                            </th>

                            <th class="p-3 text-left">
                                Item Code
                            </th>

                            <th class="p-3 text-left">
                                Description
                            </th>

                            <th class="p-3 text-right">
                                Amount Due
                            </th>

                            <th class="p-3 text-right">
                                Amount Paying
                            </th>

                            <th class="p-3 text-center">
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($this->selectedItems as $item)

                            <tr class="border-t border-gray-200 dark:border-gray-700">
                                <td class="p-3 font-medium">
                                    {{ $item['fiscal_year'] }}
                                </td>

                                <td class="p-3 font-medium">
                                    {{ $item['item_code'] }}
                                </td>

                                <td class="p-3">
                                    {{ $item['description'] }}
                                </td>

                                <td class="p-3 text-right">
                                    ₱{{ number_format($item['amount_due'], 2) }}
                                </td>

                                <td class="p-3 text-right">

                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        wire:model.live="selectedItems.{{ $item['item_code'] }}.amount_paying"
                                        wire:change="calculateTotal"
                                        class="w-32 rounded-md border-gray-300 text-right"
                                    >

                                </td>

                                <td class="p-3 text-center">

                                    <button
                                        type="button"
                                        wire:click="removeItem('{{ $item['item_code'] }}')"
                                        class="font-semibold text-red-600 hover:text-red-800"
                                    >
                                        Remove
                                    </button>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                    <tfoot>

                        <tr class="border-t bg-blue-50 dark:bg-blue-900/20 font-semibold">

                            <td colspan="4" class="p-3 text-right">
                                Total Payment
                            </td>

                            <td class="p-3 text-right text-lg">
                                ₱{{ number_format($this->totalAmount, 2) }}
                            </td>

                            <td></td>

                        </tr>

                    </tfoot>

                </table>

            </div>

        </div>

    @endif
    @if(count($this->selectedItems))
        <script>
            window.addEventListener('beforeunload', function (e) {

                e.preventDefault();

                e.returnValue = '';

            });
        </script>
    @endif

</x-filament-panels::page>
