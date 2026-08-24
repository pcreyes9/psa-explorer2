<x-filament-panels::page>

    @include('filament.members.profile-header', [
        'record' => $this->record,
    ])

    <div class="mt-2">
        {{ $this->infolist }}
    </div>

    <div class="mt-6">

        @if (!$this->financialRecordsLoaded)

            <x-filament::section>

                <div class="flex flex-col items-center justify-center text-center">

                    {{-- <div class="mb-4">

                        <x-heroicon-o-banknotes
                            class="w-12 h-12 text-gray-400"
                        />

                    </div>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Financial Records
                    </h2> --}}

                    {{-- <p class="mt-1 text-sm text-gray-500">
                        Financial records have not been loaded yet.
                    </p> --}}

                    <div class="mt-6">

                        <x-filament::button
                            wire:click="loadFinancialRecords"
                            {{-- icon="heroicon-o-arrow-down-tray" --}}
                        >
                            Load Financial Records
                        </x-filament::button>

                    </div>

                </div>

            </x-filament::section>

        @else

            @include('filament.members.financial.index', [
                'record' => $this->record,
                'payments' => $this->financialPayments,
                'archives' => $this->financialArchives,
                'balances' => $this->financialBalances,
            ])

        @endif

    </div>

</x-filament-panels::page>