<x-filament-panels::page>

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            {{-- <div class="flex items-center gap-4">

                <div class="flex h-14 w-14 items-center justify-center rounded-2xl
                            bg-primary-600 text-white shadow-lg shadow-primary-600/20">
                    <x-heroicon-o-document-text class="h-7 w-7"/>
                </div>

                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                        PSA CASH VOUCHER
                    </h1>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Create and manage cash voucher details
                    </p>
                </div>

            </div> --}}

            <div
                x-data="{
                    showExportModal: false
                }"
            >

                {{-- Export Button --}}
                <button
                    type="button"
                    @click="showExportModal = true"
                    class="inline-flex items-center justify-center gap-2 rounded-xl
                        border border-gray-300 bg-primary-600 px-4 py-2.5
                        text-sm font-semibold text-white shadow-sm
                        transition hover:bg-primary-700
                        dark:border-gray-700 dark:bg-gray-900
                        dark:text-gray-200"

                >

                    <x-heroicon-o-arrow-down-tray class="h-5 w-5"/>

                    Export Disbursement Voucher

                </button>

                <div
                    x-show="showExportModal"
                    x-cloak
                    x-transition.opacity
                    class="fixed inset-0 z-[9999] flex items-center justify-center
                        bg-black/50 p-4"
                >

                    {{-- Modal --}}
                    <div
                        @click.outside="showExportModal = false"
                        x-transition
                        class="w-full max-w-md overflow-hidden rounded-2xl
                            bg-white shadow-2xl
                            dark:bg-gray-900"
                    >

                        {{-- Header --}}
                        <div class="border-b border-gray-200 px-6 py-5
                                    dark:border-gray-800">

                            <div class="flex items-center gap-3">

                                <div
                                    class="flex h-10 w-10 items-center justify-center
                                        rounded-xl bg-primary-50 text-primary-600
                                        dark:bg-primary-500/10
                                        dark:text-primary-400"
                                >
                                    <x-heroicon-o-calendar-days class="h-5 w-5"/>
                                </div>

                                <div>

                                    <h2 class="text-lg font-semibold
                                            text-gray-950 dark:text-white">
                                        Export Disbursement Voucher
                                    </h2>

                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Select the date of the disbursement voucher.
                                    </p>

                                </div>

                            </div>

                        </div>


                        {{-- Body --}}
                        <div class="space-y-5 px-6 py-6">

                            <div>

                                <label
                                    for="exportDate"
                                    class="mb-2 block text-sm font-medium
                                        text-gray-700 dark:text-gray-300"
                                >
                                    Disbursement Date
                                </label>

                                <input
                                    id="exportDate"
                                    type="date"
                                    wire:model="exportDate"
                                    class="block w-full rounded-xl
                                        border-gray-300 bg-white
                                        px-4 py-3 text-sm shadow-sm
                                        focus:border-primary-500
                                        focus:ring-primary-500
                                        dark:border-gray-700
                                        dark:bg-gray-950
                                        dark:text-white"
                                >

                                @error('exportDate')
                                    <p class="mt-2 text-sm text-danger-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>


                        {{-- Footer --}}
                        <div
                            class="flex justify-end gap-3
                                border-t border-gray-200
                                bg-gray-50 px-6 py-4
                                dark:border-gray-800
                                dark:bg-gray-800/50"
                        >

                            {{-- Cancel --}}
                            <button
                                type="button"
                                @click="showExportModal = false"
                                class="rounded-xl border border-gray-300
                                    bg-white px-5 py-2.5
                                    text-sm font-semibold text-gray-700
                                    hover:bg-gray-50
                                    dark:border-gray-700
                                    dark:bg-gray-900
                                    dark:text-gray-200"
                            >
                                Cancel
                            </button>


                            {{-- Export --}}
                            <button
                                type="button"
                                wire:click="exportDisbursementVoucher"
                                wire:loading.attr="disabled"
                                wire:target="exportDisbursementVoucher"
                                class="inline-flex items-center gap-2
                                    rounded-xl bg-primary-600
                                    px-5 py-2.5
                                    text-sm font-semibold text-white
                                    hover:bg-primary-700
                                    disabled:opacity-50"
                            >

                                <x-heroicon-o-arrow-down-tray
                                    class="h-5 w-5"
                                    wire:loading.remove
                                    wire:target="exportDisbursementVoucher"
                                />

                                <x-heroicon-o-arrow-path
                                    class="h-5 w-5 animate-spin"
                                    wire:loading
                                    wire:target="exportDisbursementVoucher"
                                />

                                <span wire:loading.remove wire:target="exportDisbursementVoucher">
                                    Export
                                </span>

                                <span wire:loading wire:target="exportDisbursementVoucher">
                                    Preparing...
                                </span>

                            </button>

                        </div>

                    </div>

                </div>

            </div>

             <button
                type="button"
                wire:click="resetForm"
                class="inline-flex items-center justify-center gap-2 rounded-xl
                       border border-gray-300 bg-white px-4 py-2.5
                       text-sm font-semibold text-gray-700 shadow-sm
                       transition hover:bg-gray-50
                       dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
            >
                <x-heroicon-o-arrow-path class="h-5 w-5"/>

                Reset Form
            </button>

        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200
                    bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800">

                <div class="flex items-center gap-3">

                    <div class="flex h-9 w-9 items-center justify-center rounded-lg
                                bg-primary-50 text-primary-600
                                dark:bg-primary-500/10 dark:text-primary-400">

                        <x-heroicon-o-document-text class="h-5 w-5"/>

                    </div>

                    <div>

                        <h2 class="font-semibold text-gray-950 dark:text-white">
                            Voucher Information
                        </h2>

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Enter the basic information for this voucher.
                        </p>

                    </div>

                </div>

            </div>


            <div class="grid gap-5 p-6 md:grid-cols-2">
                <div class="grid gap-5 md:grid-cols-2">
                    <div>

                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Voucher No.
                        </label>

                        <input
                            type="text"
                            wire:model="voucherNo"
                            placeholder="Enter voucher number"
                            class="w-full rounded-xl border-gray-300
                                bg-white px-4 py-3 text-sm shadow-sm
                                focus:border-primary-500 focus:ring-primary-500
                                dark:border-gray-700 dark:bg-gray-950 dark:text-white"
                        >

                    </div>

                    <div>

                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Check No.
                        </label>

                        <input
                            type="text"
                            wire:model="checkNo"
                            placeholder="Enter check number"
                            class="w-full rounded-xl border-gray-300
                                bg-white px-4 py-3 text-sm shadow-sm
                                focus:border-primary-500 focus:ring-primary-500
                                dark:border-gray-700 dark:bg-gray-950 dark:text-white"
                        >

                    </div>

                </div>

                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Date
                    </label>

                    <input
                        type="date"
                        wire:model="date"
                        class="w-full rounded-xl border-gray-300
                               bg-white px-4 py-3 text-sm shadow-sm
                               focus:border-primary-500 focus:ring-primary-500
                               dark:border-gray-700 dark:bg-gray-950 dark:text-white"
                    >

                </div>

                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Pay To
                    </label>

                    <input
                        type="text"
                        wire:model="payTo"
                        placeholder="Enter payee name"
                        class="w-full rounded-xl border-gray-300
                               bg-white px-4 py-3 text-sm shadow-sm
                               focus:border-primary-500 focus:ring-primary-500
                               dark:border-gray-700 dark:bg-gray-950 dark:text-white"
                    >

                </div>
                <div class="">

                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Address
                    </label>

                    <input
                        type="text"
                        wire:model="address"
                        placeholder="Enter address"
                        class="w-full rounded-xl border-gray-300
                               bg-white px-4 py-3 text-sm shadow-sm
                               focus:border-primary-500 focus:ring-primary-500
                               dark:border-gray-700 dark:bg-gray-950 dark:text-white"
                    >

                </div>

            </div>

        </div>

        <div
            x-data="{
                items: [],

                description: '',
                amount: '',
                selected: null,

                addItem() {

                    if (!this.description.trim() || !this.amount) {
                        return;
                    }

                    this.items.push({
                        description: this.description,
                        amount: parseFloat(this.amount)
                    });

                    this.description = '';
                    this.amount = '';
                    this.selected = null;

                    this.syncItems();
                },

                editItem() {

                    if (this.selected === null) {
                        return;
                    }

                    let item = this.items[this.selected];

                    this.description = item.description;
                    this.amount = item.amount;

                    this.items.splice(this.selected, 1);

                    this.selected = null;

                    this.syncItems();
                },

                removeItem() {

                    if (this.selected === null) {
                        return;
                    }

                    this.items.splice(this.selected, 1);

                    this.selected = null;

                    this.syncItems();
                },

                syncItems() {
                    $wire.set('items', this.items);
                },

                get total() {

                    return this.items.reduce(
                        (total, item) => total + Number(item.amount || 0),
                        0
                    );

                },

                money(value) {

                    return new Intl.NumberFormat('en-PH', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(value);

                }
            }"

            class="overflow-hidden rounded-2xl border border-gray-200
                bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >

            <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800">

                <div class="flex items-center gap-3">

                    <div class="flex h-9 w-9 items-center justify-center rounded-lg
                                bg-primary-50 text-primary-600
                                dark:bg-primary-500/10 dark:text-primary-400">

                        <x-heroicon-o-list-bullet class="h-5 w-5"/>

                    </div>

                    <div>

                        <h2 class="font-semibold text-gray-950 dark:text-white">
                            Items
                        </h2>

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Add the expenses or items included in this voucher.
                        </p>

                    </div>

                </div>

            </div>


            <div class="p-6">
                <div class="grid gap-4 lg:grid-cols-[1fr_260px_auto_auto_auto]">
                    <div>

                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Description
                        </label>

                        <input
                            type="text"
                            x-model="description"
                            @keydown.enter.prevent="addItem()"
                            placeholder="Enter description"
                            class="w-full rounded-xl border-gray-300
                                   bg-white px-4 py-3 text-sm
                                   focus:border-primary-500 focus:ring-primary-500
                                   dark:border-gray-700 dark:bg-gray-950 dark:text-white"
                        >

                    </div>

                    <div>

                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Amount
                        </label>

                        <div class="relative">

                            <span class="absolute left-4 top-1/2
                                         -translate-y-1/2 text-gray-500">
                                ₱
                            </span>

                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                x-model="amount"
                                @keydown.enter.prevent="addItem()"
                                placeholder="0.00"
                                class="w-full rounded-xl border-gray-300
                                       bg-white py-3 pl-9 pr-4 text-sm
                                       focus:border-primary-500 focus:ring-primary-500
                                       dark:border-gray-700 dark:bg-gray-950 dark:text-white"
                            >

                        </div>

                    </div>
                    <button
                        type="button"
                        @click="addItem()"
                        class="mt-auto inline-flex h-[46px]
                               items-center justify-center gap-2
                               rounded-xl bg-primary-600 px-5
                               text-sm font-semibold text-white
                               hover:bg-primary-700"
                    >

                        <x-heroicon-o-plus class="h-5 w-5"/>

                        Add Item

                    </button>

                    <button
                        type="button"
                        @click="editItem()"
                        :disabled="selected === null"
                        class="mt-auto inline-flex h-[46px]
                               items-center justify-center gap-2
                               rounded-xl border border-primary-200
                               bg-primary-50 px-5 text-sm font-semibold
                               text-primary-700
                               hover:bg-primary-100
                               disabled:cursor-not-allowed
                               disabled:opacity-50"
                    >

                        <x-heroicon-o-pencil class="h-5 w-5"/>

                        Edit Selected

                    </button>

                    <button
                        type="button"
                        @click="removeItem()"
                        :disabled="selected === null"
                        class="mt-auto inline-flex h-[46px]
                               items-center justify-center gap-2
                               rounded-xl border border-red-200
                               bg-red-50 px-5 text-sm font-semibold
                               text-red-600
                               hover:bg-red-100
                               disabled:cursor-not-allowed
                               disabled:opacity-50"
                    >

                        <x-heroicon-o-trash class="h-5 w-5"/>

                        Remove

                    </button>

                </div>

                <div class="mt-6 overflow-hidden rounded-xl border
                            border-gray-200 dark:border-gray-700">

                    <div class="overflow-x-auto">

                        <table class="w-full text-left text-sm">

                            <thead class="bg-gray-50 text-xs
                                          uppercase text-gray-600
                                          dark:bg-gray-800 dark:text-gray-300">

                                <tr>

                                    <th class="w-16 px-5 py-3.5">
                                        #
                                    </th>

                                    <th class="px-5 py-3.5">
                                        Description
                                    </th>

                                    <th class="w-48 px-5 py-3.5 text-right">
                                        Amount
                                    </th>

                                    <th class="w-24 px-5 py-3.5 text-center">
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody class="divide-y divide-gray-100
                                         dark:divide-gray-800">

                                <template
                                    x-for="(item, index) in items"
                                    :key="index"
                                >

                                    <tr
                                        @click="selected = index"
                                        class="cursor-pointer transition
                                               hover:bg-gray-50
                                               dark:hover:bg-gray-800/50"
                                        :class="selected === index
                                            ? 'bg-primary-50 dark:bg-primary-500/10'
                                            : ''"
                                    >

                                        <td
                                            class="px-5 py-4 text-gray-500"
                                            x-text="index + 1"
                                        ></td>

                                        <td
                                            class="px-5 py-4 font-medium
                                                   text-gray-900
                                                   dark:text-white"
                                            x-text="item.description"
                                        ></td>

                                        <td class="px-5 py-4 text-right
                                                   font-semibold
                                                   text-gray-900
                                                   dark:text-white">

                                            ₱<span
                                                x-text="money(item.amount)"
                                            ></span>

                                        </td>

                                        <td class="px-5 py-4 text-center">

                                            <button
                                                type="button"
                                                @click.stop="selected = index"
                                                class="rounded-lg p-2 text-gray-400
                                                       hover:bg-gray-100
                                                       hover:text-primary-600"
                                            >

                                                <x-heroicon-o-pencil-square
                                                    class="mx-auto h-4 w-4"
                                                />

                                            </button>

                                        </td>

                                    </tr>

                                </template>

                                <tr x-show="items.length === 0">

                                    <td colspan="4" class="px-6 py-16 text-center">

                                        <div class="flex flex-col items-center">

                                            <div class="mb-4 flex h-14 w-14
                                                        items-center justify-center
                                                        rounded-2xl bg-gray-100
                                                        text-gray-400
                                                        dark:bg-gray-800">

                                                <x-heroicon-o-inbox
                                                    class="h-7 w-7"
                                                />

                                            </div>

                                            <p class="font-semibold text-gray-700
                                                      dark:text-gray-200">

                                                No items added yet

                                            </p>

                                            <p class="mt-1 text-sm text-gray-500">

                                                Add an item using the form above.

                                            </p>

                                        </div>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

                <div class="mt-5 flex items-center justify-end gap-4">

                    <span class="text-sm font-semibold uppercase
                                 tracking-wide text-gray-500">

                        Total

                    </span>

                    <span class="text-2xl font-bold text-primary-600
                                 dark:text-primary-400">

                        ₱<span x-text="money(total)"></span>

                    </span>

                </div>

            </div>

        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200
                    bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800">

                <div class="flex items-center gap-3">

                    <div class="flex h-9 w-9 items-center justify-center rounded-lg
                                bg-primary-50 text-primary-600
                                dark:bg-primary-500/10 dark:text-primary-400">

                        <x-heroicon-o-shield-check class="h-5 w-5"/>

                    </div>

                    <div>

                        <h2 class="font-semibold text-gray-950 dark:text-white">
                            Approvals
                        </h2>

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Enter the personnel responsible for this voucher.
                        </p>

                    </div>

                </div>

            </div>


            <div class="grid gap-5 p-6 md:grid-cols-3">
                <div>

                    <label class="mb-2 block text-sm font-medium
                                  text-gray-700 dark:text-gray-300">

                        Approved By

                    </label>

                    <input
                        type="text"
                        wire:model="approvedBy"
                        placeholder="Enter name"
                        class="w-full rounded-xl border-gray-300
                               bg-white px-4 py-3 text-sm
                               focus:border-primary-500 focus:ring-primary-500
                               dark:border-gray-700 dark:bg-gray-950 dark:text-white"
                    >

                </div>

                <div>

                    <label class="mb-2 block text-sm font-medium
                                  text-gray-700 dark:text-gray-300">

                        Checked By

                    </label>

                    <input
                        type="text"
                        wire:model="checkedBy"
                        placeholder="Enter name"
                        class="w-full rounded-xl border-gray-300
                               bg-white px-4 py-3 text-sm
                               focus:border-primary-500 focus:ring-primary-500
                               dark:border-gray-700 dark:bg-gray-950 dark:text-white"
                    >

                </div>

                <div>

                    <label class="mb-2 block text-sm font-medium
                                  text-gray-700 dark:text-gray-300">

                        Received By

                    </label>

                    <input
                        type="text"
                        wire:model="receivedBy"
                        placeholder="Enter name"
                        class="w-full rounded-xl border-gray-300
                               bg-white px-4 py-3 text-sm
                               focus:border-primary-500 focus:ring-primary-500
                               dark:border-gray-700 dark:bg-gray-950 dark:text-white"
                    >

                </div>

            </div>

        </div>

        <div class="flex gap-3 rounded-2xl border
                    border-gray-200 bg-white p-5 shadow-sm
                    sm:flex-row sm:items-center sm:justify-between
                    dark:border-gray-800 dark:bg-gray-900">

            <div class="flex flex-col gap-3 sm:flex-row">
                <button
                    type="button"
                    wire:click="printVoucher"
                    class="inline-flex items-center justify-center gap-2
                           rounded-xl bg-primary-600 px-5 py-3
                           text-sm font-semibold text-white
                           shadow-sm transition hover:bg-primary-700"
                >

                    <x-heroicon-o-printer class="h-5 w-5"/>

                    Print Voucher

                </button>

                <button
                    type="button"
                    wire:click="printCheck"
                    class="inline-flex items-center justify-center gap-2
                           rounded-xl border border-primary-200
                           bg-primary-50 px-5 py-3
                           text-sm font-semibold text-primary-700
                           transition hover:bg-primary-100
                           dark:border-primary-500/20
                           dark:bg-primary-500/10
                           dark:text-primary-400"
                >

                    <x-heroicon-o-printer class="h-5 w-5"/>

                    Print Check

                </button>

            </div>


            {{-- <button
                type="button"
                onclick="history.back()"
                class="inline-flex items-center justify-center gap-2
                       rounded-xl border border-red-200
                       bg-red-50 px-5 py-3
                       text-sm font-semibold text-red-600
                       transition hover:bg-red-100
                       dark:border-red-500/20
                       dark:bg-red-500/10
                       dark:text-red-400"
            >

                <x-heroicon-o-arrow-right-on-rectangle
                    class="h-5 w-5"
                />

                Exit

            </button> --}}

        </div>

    </div>

    <style>

        @media print {

            aside,
            header,
            nav,
            .fi-sidebar,
            .fi-topbar,
            .fi-breadcrumbs,
            button {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .fi-main {
                padding: 0 !important;
            }

            .fi-page {
                padding: 0 !important;
            }

            .shadow-sm,
            .shadow-lg {
                box-shadow: none !important;
            }

        }

    </style>

</x-filament-panels::page>