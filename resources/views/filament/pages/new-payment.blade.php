<x-filament-panels::page>

    <div class="space-y-5">

        {{-- ========================================= --}}
        {{-- MEMBER INFORMATION                       --}}
        {{-- ========================================= --}}

        @if ($this->selectedMember)

            <div class="flex items-center gap-4">

                <img
                    src="{{ $this->selectedMember->photo_url ?? asset('images/default-avatar.png') }}"
                    alt="Member Photo"
                    class="h-32 w-32 rounded-full border-4 border-gray-300 object-cover object-[50%_20%] shadow-sm"
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


        {{-- ========================================= --}}
        {{-- PAYMENT INFORMATION                       --}}
        {{-- ========================================= --}}

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">

            <div class="mb-5">

                <h2 class="text-lg font-semibold">
                    Payment Information
                </h2>

                <p class="text-sm text-gray-500">
                    Select the member, fiscal year, and payment details.
                </p>

            </div>

            {{ $this->form }}

            {{-- ACTION BUTTONS --}}

            <div class="mt-5 flex flex-wrap justify-end gap-2">

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


    {{-- ========================================= --}}
    {{-- SELECTED TRANSACTION ITEMS                --}}
    {{-- ========================================= --}}

    @if (count($this->selectedItems))

        <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">

            {{-- TABLE HEADER --}}

            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-800">

                <h2 class="text-lg font-semibold">
                    Selected Transaction Items
                </h2>

                <p class="text-sm text-gray-500">
                    Review the charges and enter the amount being collected.
                </p>

            </div>


            {{-- TABLE --}}

            <div class="overflow-x-auto">

                <table class="w-full text-sm">

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

                                {{-- FISCAL YEAR --}}

                                <td class="p-3 font-medium whitespace-nowrap">
                                    {{ $item['fiscal_year'] }}
                                </td>


                                {{-- ITEM CODE --}}

                                <td class="p-3 font-medium whitespace-nowrap">
                                    {{ $item['item_code'] }}
                                </td>


                                {{-- DESCRIPTION --}}

                                <td class="p-3 min-w-48">

                                    {{ $item['description'] }}

                                    @if (($item['previously_paid'] ?? 0) > 0)

                                        <div class="mt-1 text-xs text-gray-500">
                                            Previously paid:
                                            ₱{{ number_format((float) $item['previously_paid'], 2) }}
                                        </div>

                                    @endif

                                </td>


                                {{-- AMOUNT DUE --}}

                                <td class="p-3 text-right whitespace-nowrap">

                                    ₱{{ number_format((float) ($item['amount_due'] ?? 0), 2) }}

                                </td>


                                {{-- AMOUNT PAYING --}}

                                <td class="p-3 text-right">

                                    <input
                                        type="number"
                                        min="0"
                                        max="{{ (float) ($item['balance'] ?? $item['amount_due'] ?? 0) }}"
                                        step="0.01"
                                        wire:model.live.debounce.1000ms="selectedItems.{{ $key }}.amount_paying"
                                        wire:change="calculateTotal"
                                        class="w-32 rounded-md border-gray-300 text-right shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800"
                                    />

                                    <div class="mt-1 text-xs text-gray-500 whitespace-nowrap">

                                        Balance:
                                        ₱{{ number_format((float) ($item['balance'] ?? $item['amount_due'] ?? 0), 2) }}

                                    </div>

                                </td>


                                {{-- ACTION --}}

                                <td class="p-3 text-center">

                                    <button
                                        type="button"
                                        wire:click="removeItem(@js((string) $key))"
                                        wire:loading.attr="disabled"
                                        wire:target="removeItem"
                                        class="font-semibold text-red-600 hover:text-red-800 disabled:opacity-50"
                                    >
                                        <span wire:loading.remove wire:target="removeItem">
                                            Remove
                                        </span>

                                        <span wire:loading wire:target="removeItem">
                                            Removing...
                                        </span>
                                    </button>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>


                    {{-- TOTAL PAYMENT --}}

                    <tfoot>

                        <tr class="border-t border-gray-200 bg-blue-50 font-semibold dark:border-gray-700 dark:bg-blue-900/20">

                            <td colspan="4" class="p-3 text-right">
                                Total Payment
                            </td>

                            <td class="p-3 text-right text-lg whitespace-nowrap">

                                ₱{{ number_format((float) $this->totalAmount, 2) }}

                            </td>

                            <td></td>

                        </tr>

                    </tfoot>

                </table>

            </div>

        </div>


    @else

        {{-- EMPTY STATE --}}

        <div class="mt-4 rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-900">

            <x-heroicon-o-receipt-percent
                class="mx-auto h-10 w-10 text-gray-400"
            />

            <div class="mt-3 font-medium text-gray-700 dark:text-gray-200">
                No transaction items selected
            </div>

            <p class="mt-1 text-sm text-gray-500">
                Select a member and click Transaction Items to begin.
            </p>

        </div>

    @endif


    {{-- ========================================= --}}
    {{-- WARN BEFORE LEAVING WITH SELECTED ITEMS   --}}
    {{-- ========================================= --}}

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
