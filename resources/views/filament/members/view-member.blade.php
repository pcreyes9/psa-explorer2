<x-filament-panels::page>

    {{-- Member Profile Header --}}
    @include('filament.members.profile-header', [
        'record' => $this->record,
    ])

    {{-- Member Information --}}
    <div class="mt-2">
        {{ $this->infolist }}
    </div>

    {{-- Financial Records --}}
    <div class="mt-6">

        @if (! $this->financialRecordsLoaded)

            <x-filament::section>

                <div class="flex flex-col items-center justify-center py-6 text-center">

                    <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                        <x-heroicon-o-banknotes
                            class="h-7 w-7 text-gray-500 dark:text-gray-400"
                        />
                    </div>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Financial Records
                    </h2>

                    <p class="mt-1 max-w-md text-sm text-gray-500 dark:text-gray-400">
                        Load this member's membership payments, archived payments,
                        ledger balances, and CME payment history.
                    </p>

                    <div class="mt-5">

                        <x-filament::button
                            wire:click="loadFinancialRecords"
                            wire:loading.attr="disabled"
                            wire:target="loadFinancialRecords"
                            icon="heroicon-o-arrow-down-tray"
                        >
                            <span wire:loading.remove wire:target="loadFinancialRecords">
                                Load Financial Records
                            </span>

                            <span wire:loading wire:target="loadFinancialRecords">
                                Loading Records...
                            </span>
                        </x-filament::button>

                    </div>

                </div>

            </x-filament::section>

        @else

            {{-- Financial Records Tabs --}}
            @include('filament.members.financial.index', [
                'record' => $this->record,
                'payments' => $this->financialPayments,
                'membershipPayments' => $this->financialPayments,
                'archives' => $this->financialArchives,
                'balances' => $this->financialBalances,
                'cmePayments' => $this->cmePayments,
            ])

        @endif

    </div>

</x-filament-panels::page>
