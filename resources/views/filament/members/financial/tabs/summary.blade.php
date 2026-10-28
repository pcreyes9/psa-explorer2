@php
    /*
    |--------------------------------------------------------------------------
    | Prepare Membership Payment Details
    |--------------------------------------------------------------------------
    | Each payment is matched to a fiscal year using its payment items.
    | Only MEMF items for the selected fiscal year will appear in the modal.
    */

    $paymentDetails = collect($membershipPayments ?? [])
        ->map(function ($payment) {
            return [
                'payment_date' => $payment->payment_date,
                'payment_ref_no' => $payment->payment_ref_no,
                'or_no' => $payment->or_no,
                'payment_type' => $payment->payment_type,
                'payment_total_amt' => $payment->payment_total_amt,
                'payment_discounted_amt' => $payment->payment_discounted_amt,
                'payment_status' => $payment->stat,

                'items' => $payment->paymentItems->map(function ($item) {
                    return [
                        'fiscal_year' => $item->fiscal_year,
                        'tran_code' => $item->tran_code,
                        'item_code' => $item->item_code,
                        'item_details' => $item->transactionTypeItem?->item_details,
                        'amount_due' => $item->amount_due,
                    ];
                })->values()->all(),
            ];
        })
        ->values()
        ->all();
@endphp

<div
    x-data="{
        showPaymentModal: false,
        selectedLedger: null,
        selectedPayment: null,
        membershipPayments: @js($paymentDetails),

        openPaymentModal(ledger) {
            this.selectedLedger = ledger;
            this.showPaymentModal = true;
        },

        closePaymentModal() {
            this.showPaymentModal = false;
            this.selectedLedger = null;
        },

        openTransactionDetails(payment) {
            this.selectedPayment = payment;
        },

        closeTransactionDetails() {
            this.selectedPayment = null;
        },

        get selectedPayments() {
            if (!this.selectedLedger) {
                return [];
            }

            const year = String(this.selectedLedger.fiscal_year);

            return this.membershipPayments.filter(payment =>
                payment.items.some(item =>
                    String(item.fiscal_year) === year &&
                    String(item.tran_code).trim().toUpperCase() === 'MEMF'
                )
            );
        },

        formatAmount(amount) {
            return new Intl.NumberFormat('en-PH', {
                style: 'currency',
                currency: 'PHP',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(Number(amount ?? 0));
        },

        formatDate(date) {
            if (!date) {
                return '—';
            }

            const parsedDate = new Date(date);

            if (Number.isNaN(parsedDate.getTime())) {
                return date;
            }

            return parsedDate.toLocaleDateString('en-PH', {
                year: 'numeric',
                month: 'short',
                day: '2-digit'
            });
        },

        paymentStatus(status) {
            const value = String(status ?? '').trim();

            if (value === '1') {
                return 'Paid';
            }

            return value || '—';
        }
    }"
    @keydown.escape.window="closePaymentModal()"
>
    {{-- ========================================================= --}}
    {{-- MEMBERSHIP DUES LEDGER                                   --}}
    {{-- ========================================================= --}}

    <div class="overflow-x-auto">

        @if ($financialLedger === null)

            <div class="p-8 text-center text-gray-500">
                Loading membership dues...
            </div>

        @else

            <table class="w-full border border-gray-200 text-sm dark:border-gray-700">

                <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th class="p-3 text-left">Fiscal Year</th>
                        <th class="p-3 text-left">Description</th>
                        <th class="p-3 text-right">Debit</th>
                        <th class="p-3 text-right">Credit</th>
                        <th class="p-3 text-right">Balance</th>
                        <th class="p-3 text-center">Status</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($financialLedger as $ledger)

                        @php
                            $balance = (float) $ledger->preliminary_balance;
                        @endphp

                        <tr
                            wire:key="membership-ledger-{{ $ledger->fiscal_year }}"
                            @click="openPaymentModal({
                                fiscal_year: @js($ledger->fiscal_year),
                                standard_annual_dues: @js($ledger->standard_annual_dues),
                                total_paid: @js($ledger->total_paid),
                                preliminary_balance: @js($ledger->preliminary_balance)
                            })"
                            class="cursor-pointer border-t border-gray-200
                                   transition-colors hover:bg-primary-50
                                   dark:border-gray-700 dark:hover:bg-gray-800"
                            title="Click to view membership payment details"
                        >

                            <td class="p-3 font-medium">
                                {{ $ledger->fiscal_year }}
                            </td>

                            <td class="p-3">
                                Membership Dues
                            </td>

                            <td class="p-3 text-right">
                                ₱{{ number_format($ledger->standard_annual_dues, 2) }}
                            </td>

                            <td class="p-3 text-right">
                                ₱{{ number_format($ledger->total_paid, 2) }}
                            </td>

                            <td class="p-3 text-right font-semibold">
                                ₱{{ number_format($balance, 2) }}
                            </td>

                            <td class="p-3 text-center">

                                @if (abs($balance) < 0.005)

                                    <span class="font-semibold text-green-600">
                                        Paid
                                    </span>

                                @elseif ($balance < 0)

                                    <span class="font-semibold text-blue-600">
                                        Overpaid
                                    </span>

                                @else

                                    <span class="font-semibold text-red-600">
                                        Outstanding
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-500">
                                No membership dues found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

                @if ($financialLedger->isNotEmpty())

                    @php
                        $totalBalance = $financialLedger->sum('preliminary_balance');
                    @endphp

                    <tfoot>

                        <tr class="border-t bg-green-50 font-semibold dark:bg-green-900/20">

                            <td colspan="2" class="p-3">
                                Totals
                            </td>

                            <td class="p-3 text-right">
                                ₱{{ number_format($financialLedger->sum('standard_annual_dues'), 2) }}
                            </td>

                            <td class="p-3 text-right">
                                ₱{{ number_format($financialLedger->sum('total_paid'), 2) }}
                            </td>

                            <td class="p-3 text-right">
                                ₱{{ number_format($totalBalance, 2) }}
                            </td>

                            <td class="p-3 text-center">

                                @if (abs($totalBalance) < 0.005)

                                    <span class="text-green-600">
                                        Fully Paid
                                    </span>

                                @elseif ($totalBalance < 0)

                                    <span class="text-blue-600">
                                        Net Overpaid
                                    </span>

                                @else

                                    <span class="text-red-600">
                                        With Balance
                                    </span>

                                @endif

                            </td>

                        </tr>

                    </tfoot>

                @endif

            </table>

            {{-- ================================================= --}}
            {{-- ADVANCE PAYMENT                                   --}}
            {{-- ================================================= --}}

            <div class="mt-8">

                <h3 class="mb-3 text-lg font-bold text-red-600">
                    Advance Payment
                </h3>

                <div class="overflow-x-auto">

                    <table class="w-full border border-gray-200 text-sm dark:border-gray-700">

                        <thead class="bg-gray-100 dark:bg-gray-800">

                            <tr>
                                <th class="p-3 text-left">
                                    Fiscal Year
                                </th>

                                <th class="p-3 text-right">
                                    Credit
                                </th>
                            </tr>

                        </thead>

                        <tbody>

                            @forelse ($advancePayments as $payment)

                                <tr class="border-t border-gray-200 dark:border-gray-700">

                                    <td class="p-3 font-medium">
                                        {{ $payment->fiscal_year }}
                                    </td>

                                    <td class="p-3 text-right font-semibold">
                                        ₱{{ number_format($payment->total_credit, 2) }}
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="2" class="p-6 text-center text-gray-500">
                                        No advance payments found.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                        @if ($advancePayments->isNotEmpty())

                            <tfoot>

                                <tr class="border-t bg-gray-50 font-semibold dark:bg-gray-800">

                                    <td class="p-3">
                                        Total Advance Payments
                                    </td>

                                    <td class="p-3 text-right">
                                        ₱{{ number_format($advancePayments->sum('total_credit'), 2) }}
                                    </td>

                                </tr>

                            </tfoot>

                        @endif

                    </table>

                </div>

            </div>

        @endif

    </div>

    {{-- ========================================================= --}}
    {{-- MEMBERSHIP PAYMENT DETAILS MODAL                         --}}
    {{-- ========================================================= --}}

    <div
        x-cloak
        x-show="showPaymentModal"
        x-transition.opacity
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        style="display: none;"
        @click.self="closePaymentModal()"
    >

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-gray-950/60"></div>

        {{-- Modal --}}
        <div
            x-show="showPaymentModal"
            x-transition
            class="relative z-10 flex max-h-[90vh] w-full max-w-6xl
                   flex-col overflow-hidden rounded-xl bg-white shadow-2xl
                   dark:bg-gray-900"
            @click.stop
        >

            {{-- Modal Header --}}
            <div class="flex items-center justify-between border-b
                        border-gray-200 px-6 py-4 dark:border-gray-700">

                <div>

                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        Membership Payment Details
                    </h2>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">

                        Fiscal Year:

                        <span
                            class="font-semibold text-gray-900 dark:text-white"
                            x-text="selectedLedger?.fiscal_year ?? ''"
                        ></span>

                    </p>

                </div>

                <button
                    type="button"
                    @click="closePaymentModal()"
                    class="rounded-lg p-2 text-gray-500 transition
                           hover:bg-gray-100 hover:text-gray-700
                           dark:hover:bg-gray-800"
                    aria-label="Close modal"
                >
                    <x-heroicon-o-x-mark class="h-5 w-5" />
                </button>

            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 gap-3 border-b border-gray-200
                        p-6 sm:grid-cols-3 dark:border-gray-700">

                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Annual Membership Dues
                    </p>

                    <p
                        class="mt-2 text-lg font-bold text-gray-900 dark:text-white"
                        x-text="formatAmount(selectedLedger?.standard_annual_dues)"
                    ></p>

                </div>

                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Total Paid in Ledger
                    </p>

                    <p
                        class="mt-2 text-lg font-bold text-green-600"
                        x-text="formatAmount(selectedLedger?.total_paid)"
                    ></p>

                </div>

                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Remaining Balance
                    </p>

                    <p
                        class="mt-2 text-lg font-bold"
                        :class="Number(selectedLedger?.preliminary_balance ?? 0) > 0
                            ? 'text-red-600'
                            : Number(selectedLedger?.preliminary_balance ?? 0) < 0
                                ? 'text-blue-600'
                                : 'text-green-600'"
                        x-text="formatAmount(selectedLedger?.preliminary_balance)"
                    ></p>

                </div>

            </div>

            {{-- Individual Payment Transactions --}}
            <div class="flex-1 overflow-y-auto p-6">

                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">

                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        Payment Transactions
                    </h3>

                    <span
                        class="rounded-full bg-primary-50 px-3 py-1 text-sm
                               font-medium text-primary-700 dark:bg-primary-900/30
                               dark:text-primary-300"
                        x-text="selectedPayments.length + ' transaction(s)'"
                    ></span>

                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">

                    <table class="w-full min-w-[1000px] text-sm">

                        <thead class="bg-gray-100 dark:bg-gray-800">

                            <tr>

                                <th class="p-3 text-left">
                                    Payment Date
                                </th>

                                <th class="p-3 text-left">
                                    Reference Number
                                </th>

                                <th class="p-3 text-left">
                                    OR Number
                                </th>

                                <th class="p-3 text-left">
                                    Payment Type
                                </th>

                                <th class="p-3 text-right">
                                    Payment Amount
                                </th>

                                <th class="p-3 text-right">
                                    Discounted Amount
                                </th>

                                <th class="p-3 text-left">
                                    Status
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <template
                                x-for="(payment, index) in selectedPayments"
                                :key="String(payment.payment_ref_no ?? index) + '-' + index"
                            >

                                <tr
                                    @click="openTransactionDetails(payment)"
                                    class="cursor-pointer border-t border-gray-200
                                           transition-colors hover:bg-primary-50
                                           dark:border-gray-700 dark:hover:bg-gray-800"
                                    title="Click to view payment breakdown"
                                >

                                    <td class="whitespace-nowrap p-3">
                                        <span x-text="formatDate(payment.payment_date)"></span>
                                    </td>

                                    <td class="p-3">
                                        <span x-text="payment.payment_ref_no || '—'"></span>
                                    </td>

                                    <td class="p-3">
                                        <span x-text="payment.or_no || '—'"></span>
                                    </td>

                                    <td class="p-3">
                                        <span x-text="payment.payment_type || '—'"></span>
                                    </td>

                                    <td class="whitespace-nowrap p-3 text-right">
                                        <span x-text="formatAmount(payment.payment_total_amt)"></span>
                                    </td>

                                    <td class="whitespace-nowrap p-3 text-right">
                                        <span x-text="formatAmount(payment.payment_discounted_amt)"></span>
                                    </td>

                                    <td class="p-3">
                                        <span
                                            class="inline-flex rounded-full bg-gray-100
                                                   px-2.5 py-1 text-xs font-medium
                                                   text-gray-700 dark:bg-gray-800
                                                   dark:text-gray-300"
                                            x-text="paymentStatus(payment.payment_status)"
                                        ></span>
                                    </td>

                                </tr>

                            </template>

                            <tr x-show="selectedPayments.length === 0">

                                <td colspan="7" class="p-8 text-center text-gray-500">

                                    <x-heroicon-o-document-magnifying-glass
                                        class="mx-auto mb-2 h-8 w-8 text-gray-400"
                                    />

                                    <p class="font-medium">
                                        No membership payment transactions found.
                                    </p>

                                    <p class="mt-1 text-xs">
                                        No payment items matched MEMF for this fiscal year.
                                    </p>

                                </td>

                            </tr>

                        </tbody>

                        <tfoot x-show="selectedPayments.length > 0">

                            <tr class="border-t bg-gray-50 font-semibold dark:bg-gray-800">

                                <td colspan="4" class="p-3 text-right">
                                    Total Payment Amount
                                </td>

                                <td class="whitespace-nowrap p-3 text-right text-green-600">

                                    <span
                                        x-text="formatAmount(
                                            selectedPayments.reduce(
                                                (total, payment) =>
                                                    total + Number(payment.payment_total_amt ?? 0),
                                                0
                                            )
                                        )"
                                    ></span>

                                </td>

                                <td class="whitespace-nowrap p-3 text-right">

                                    <span
                                        x-text="formatAmount(
                                            selectedPayments.reduce(
                                                (total, payment) =>
                                                    total + Number(payment.payment_discounted_amt ?? 0),
                                                0
                                            )
                                        )"
                                    ></span>

                                </td>

                                <td></td>

                            </tr>

                        </tfoot>

                    </table>

                </div>

                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Transactions are matched using the MEMF transaction code and
                    the fiscal year recorded in the payment items.
                </p>

            </div>

            {{-- Modal Footer --}}
            <div class="flex justify-end border-t border-gray-200
                        px-6 py-4 dark:border-gray-700">

                <x-filament::button
                    color="gray"
                    @click="closePaymentModal()"
                >
                    Close
                </x-filament::button>

            </div>

        </div>

    </div>

    {{-- ========================================================= --}}
    {{-- INDIVIDUAL PAYMENT TRANSACTION DETAILS MODAL              --}}
    {{-- ========================================================= --}}
    <div
        x-cloak
        x-show="selectedPayment !== null"
        x-transition.opacity
        class="fixed inset-0 z-[110] flex items-center justify-center bg-gray-950/60 p-4"
        style="display: none;"
        @click.self="closeTransactionDetails()"
    >
        <div
            x-show="selectedPayment !== null"
            x-transition
            class="relative z-10 flex max-h-[85vh] w-full max-w-3xl flex-col
                   overflow-hidden rounded-xl bg-white shadow-2xl dark:bg-gray-900"
            @click.stop
        >
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-gray-200
                        px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                    Payment Details
                </h2>

                <button
                    type="button"
                    @click="closeTransactionDetails()"
                    class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100
                           hover:text-gray-700 dark:hover:bg-gray-800"
                    aria-label="Close payment details"
                >
                    <x-heroicon-o-x-mark class="h-5 w-5" />
                </button>
            </div>

            {{-- Transaction information --}}
            <div class="flex-1 space-y-6 overflow-y-auto p-6">
                <div class="grid grid-cols-1 gap-5 text-sm sm:grid-cols-2">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Payment Date</p>
                        <p class="font-semibold text-gray-900 dark:text-white"
                           x-text="formatDate(selectedPayment?.payment_date)"></p>
                    </div>

                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Reference No.</p>
                        <p class="font-semibold text-gray-900 dark:text-white"
                           x-text="selectedPayment?.payment_ref_no || '—'"></p>
                    </div>

                    <div>
                        <p class="text-gray-500 dark:text-gray-400">OR No.</p>
                        <p class="font-semibold text-gray-900 dark:text-white"
                           x-text="selectedPayment?.or_no || '—'"></p>
                    </div>

                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Payment Type</p>
                        <p class="font-semibold text-gray-900 dark:text-white"
                           x-text="selectedPayment?.payment_type || '—'"></p>
                    </div>
                </div>

                <hr class="border-gray-200 dark:border-gray-700">

                {{-- Itemized payment breakdown --}}
                <div>
                    <h3 class="mb-3 text-base font-bold text-gray-900 dark:text-white">
                        Payment Breakdown
                    </h3>

                    <div class="max-h-80 overflow-auto rounded-lg border border-gray-200
                                dark:border-gray-700">
                        <table class="w-full min-w-[520px] text-sm">
                            <thead class="sticky top-0 z-10 bg-gray-100 dark:bg-gray-800">
                                <tr>
                                    <th class="p-3 text-left">Description</th>
                                    <th class="p-3 text-center">Fiscal Year</th>
                                    <th class="p-3 text-right">Amount</th>
                                </tr>
                            </thead>

                            <tbody>
                                <template
                                    x-for="(item, index) in (selectedPayment?.items ?? [])"
                                    :key="String(item.item_code ?? item.tran_code ?? index) + '-' + index"
                                >
                                    <tr class="border-t border-gray-200 dark:border-gray-700">
                                        <td class="p-3">
                                            <span x-text="item.item_details || item.item_code || item.tran_code || 'Payment item'"></span>
                                        </td>
                                        <td class="p-3 text-center">
                                            <span x-text="item.fiscal_year ?? '—'"></span>
                                        </td>
                                        <td class="whitespace-nowrap p-3 text-right">
                                            <span x-text="formatAmount(item.amount_due)"></span>
                                        </td>
                                    </tr>
                                </template>

                                <tr x-show="!(selectedPayment?.items?.length)">
                                    <td colspan="3" class="p-6 text-center text-gray-500">
                                        No payment breakdown items found.
                                    </td>
                                </tr>
                            </tbody>

                            <tfoot>
                                <tr class="border-t bg-gray-50 font-bold dark:bg-gray-800">
                                    <td class="p-3">TOTAL</td>
                                    <td></td>
                                    <td class="whitespace-nowrap p-3 text-right"
                                        x-text="formatAmount(selectedPayment?.payment_total_amt)"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex justify-end border-t border-gray-200 bg-gray-50
                        px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
                <x-filament::button
                    color="gray"
                    @click="closeTransactionDetails()"
                >
                    Close
                </x-filament::button>
            </div>
        </div>
    </div>

</div>