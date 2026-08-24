<x-filament::section>

    <x-slot name="heading">
        Financial Records
    </x-slot>

    <div x-data="{ tab: 'summary' }">

        <div class="flex gap-6 border-b border-gray-200 dark:border-gray-700">

            <button
                type="button"
                @click="tab = 'summary'"
                class="flex items-center gap-2 py-3 px-2 text-sm font-medium border-b-2 transition"
                :class="tab === 'summary'
                    ? 'border-primary-600 text-primary-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700'"
            >
                <x-heroicon-m-banknotes class="w-5 h-5" />

                Membership Dues
            </button>

            <button
                type="button"
                @click="tab = 'payments'"
                class="flex items-center gap-2 py-3 px-2 text-sm font-medium border-b-2 transition"
                :class="tab === 'payments'
                    ? 'border-primary-600 text-primary-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700'"
            >
                <x-heroicon-m-credit-card class="w-5 h-5" />

                Payment History
            </button>

            <button
                type="button"
                @click="tab = 'archive'"
                class="flex items-center gap-2 py-3 px-2 text-sm font-medium border-b-2 transition"
                :class="tab === 'archive'
                    ? 'border-primary-600 text-primary-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700'"
            >
                <x-heroicon-m-archive-box class="w-5 h-5" />

                Archive History
            </button>

        </div>


        {{-- MEMBERSHIP DUES --}}

        <div
            x-show="tab === 'summary'"
            x-cloak
            class="mt-6"
        >

            @include('filament.members.financial.tabs.summary', [
                'record' => $record,
                'balances' => $balances,
            ])

        </div>


        {{-- PAYMENT HISTORY --}}

        <div
            x-show="tab === 'payments'"
            x-cloak
            class="mt-6"
        >

            @include('filament.members.financial.tabs.payments', [
                'record' => $record,
                'payments' => $payments,
            ])

        </div>


        {{-- ARCHIVE HISTORY --}}

        <div
            x-show="tab === 'archive'"
            x-cloak
            class="mt-6"
        >

            @include('filament.members.financial.tabs.archive', [
                'record' => $record,
                'archives' => $archives,
            ])

        </div>

    </div>

</x-filament::section>