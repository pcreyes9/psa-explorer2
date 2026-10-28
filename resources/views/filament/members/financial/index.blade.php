<x-filament::section>
    <x-slot name="heading">
        Financial Records
    </x-slot>

    <div x-data="{ tab: 'summary' }">

        {{-- ========================================================= --}}
        {{-- TAB NAVIGATION --}}
        {{-- ========================================================= --}}

        <div class="flex gap-6 overflow-x-auto border-b border-gray-200 dark:border-gray-700">

            {{-- Membership Dues --}}
            <button
                type="button"
                @click="tab = 'summary'"
                :class="tab === 'summary'
                    ? 'border-b-2 border-primary-600 text-primary-600 dark:text-primary-400'
                    : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                class="flex items-center gap-2 whitespace-nowrap px-1 pb-3 text-sm font-medium transition"
            >
                <x-heroicon-m-banknotes class="h-5 w-5" />
                Membership Dues
            </button>

            {{-- CME Payments --}}
            <button
                type="button"
                @click="tab = 'cme-payments'"
                :class="tab === 'cme-payments'
                    ? 'border-b-2 border-primary-600 text-primary-600 dark:text-primary-400'
                    : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                class="flex items-center gap-2 whitespace-nowrap px-1 pb-3 text-sm font-medium transition"
            >
                <x-heroicon-m-academic-cap class="h-5 w-5" />
                CME Payments
            </button>

            {{-- Payment History --}}
            <button
                type="button"
                @click="tab = 'payments'"
                :class="tab === 'payments'
                    ? 'border-b-2 border-primary-600 text-primary-600 dark:text-primary-400'
                    : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                class="flex items-center gap-2 whitespace-nowrap px-1 pb-3 text-sm font-medium transition"
            >
                <x-heroicon-m-credit-card class="h-5 w-5" />
                Payment History
            </button>

            {{-- Archive History --}}
            <button
                type="button"
                @click="tab = 'archive'"
                :class="tab === 'archive'
                    ? 'border-b-2 border-primary-600 text-primary-600 dark:text-primary-400'
                    : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                class="flex items-center gap-2 whitespace-nowrap px-1 pb-3 text-sm font-medium transition"
            >
                <x-heroicon-m-archive-box class="h-5 w-5" />
                Archive History
            </button>

        </div>


        {{-- ========================================================= --}}
        {{-- MEMBERSHIP DUES --}}
        {{-- ========================================================= --}}

        <div
            x-show="tab === 'summary'"
            x-cloak
            class="mt-6"
        >
            @include('filament.members.financial.tabs.summary', [
                'record' => $record,
                'balances' => $balances,
                'membershipPayments' => $membershipPayments,
            ])
        </div>


        {{-- ========================================================= --}}
        {{-- CME PAYMENTS --}}
        {{-- ========================================================= --}}

        {{-- <div
            x-show="tab === 'cme-payments'"
            x-cloak
            class="mt-6"
        >
            @include('filament.members.financial.tabs.cme-payments', [
                'record' => $record,
                'cmePayments' => $cmePayments,
            ])
        </div> --}}


        {{-- ========================================================= --}}
        {{-- PAYMENT HISTORY --}}
        {{-- ========================================================= --}}

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


        {{-- ========================================================= --}}
        {{-- ARCHIVE HISTORY --}}
        {{-- ========================================================= --}}

        <div
            x-show="tab === 'archive'"
            x-cloak
            class="mt-6"
        >
            {{-- Temporary diagnostic --}}
            <div class="mb-4 rounded-lg bg-yellow-50 p-3 text-sm text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300">
                Archive records received: {{ $archives->count() }}
            </div>

            @include('filament.members.financial.tabs.archive', [
                'record' => $record,
                'archives' => $archives,
            ])
        </div>

    </div>
</x-filament::section>
