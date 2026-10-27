<x-filament-panels::page>

    <div class="space-y-4">

        {{-- MEMBER INFORMATION --}}
        @if ($this->selectedMember)

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

        {{-- MEMBER, FISCAL YEAR, AND ACTIONS --}}
        <div class="flex items-end gap-4">

            <div class="flex-1">
                {{ $this->form }}
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

    {{-- SELECTED TRANSACTION ITEMS --}}
    @if (count($this->selectedItems))

        <div class="mt-2 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b bg-gray-50 px-6 py-4">
                <h2 class="text-lg font-semibold">
                    Selected Transaction Items | Payment Reference #:
                </h2>
            </div>

            <div class="overflow-x-auto">

                <table class="w-full border border-gray-200 text-sm dark:border-gray-700">

                    <thead class="bg-gray-100 dark:bg-gray-800">

                        <tr>
                            <th class="p-3 text-left">
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

                        @foreach ($this->selectedItems as $key => $item)

                            <tr
                                wire:key="selected-payment-item-{{ $key }}"
                                class="border-t border-gray-200 dark:border-gray-700"
                            >

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
                                    ₱{{ number_format((float) ($item['amount_due'] ?? 0), 2) }}
                                </td>

                                <td class="p-3 text-right">

                                    <input
                                        type="number"
                                        min="0"
                                        max="{{ (float) ($item['amount_due'] ?? 0) }}"
                                        step="0.01"
                                        wire:model.live.debounce.300ms="selectedItems.{{ $key }}.amount_paying"
                                        wire:change="calculateTotal"
                                        class="w-32 rounded-md border-gray-300 text-right shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800"
                                    >

                                </td>

                                <td class="p-3 text-center">

                                    <button
                                        type="button"
                                        wire:click="removeItem(@js((string) $key))"
                                        class="font-semibold text-red-600 hover:text-red-800"
                                    >
                                        Remove
                                    </button>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                    <tfoot>

                        <tr class="border-t bg-blue-50 font-semibold dark:bg-blue-900/20">

                            <td colspan="4" class="p-3 text-right">
                                Total Payment
                            </td>

                            <td class="p-3 text-right text-lg">
                                ₱{{ number_format((float) $this->totalAmount, 2) }}
                            </td>

                            <td></td>

                        </tr>

                    </tfoot>

                </table>

            </div>

        </div>

    @endif

    {{-- WARN BEFORE LEAVING WITH SELECTED ITEMS --}}
    @if (count($this->selectedItems))

        <script>
            document.addEventListener('livewire:init', () => {
                if (window.paymentUnloadWarningInitialized) {
                    return;
                }

                window.paymentUnloadWarningInitialized = true;

                window.addEventListener('beforeunload', function (event) {
                    event.preventDefault();
                    event.returnValue = '';
                });
            });
        </script>

    @endif

</x-filament-panels::page>
