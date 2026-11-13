<div
    wire:init="initializeItems"
    class="pm"
    x-data="{
        confirming: false,
        posting: false,
        pickerOpen: false,
        checked: [],

        get total() {
            const sum = this.$wire.selected.reduce(
                (carry, key) => carry + (parseFloat(this.$wire.amounts[key]) || 0),
                0
            );

            return Math.round(sum * 100) / 100;
        },

        money(value) {
            return '₱' + Number(value).toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },

        isAdded(key) {
            return this.$wire.selected.includes(key);
        },

        openPicker() {
            this.checked = [];
            this.pickerOpen = true;
        },

        toggleKeys(selector) {
            const keys = [
                ...this.$refs.picker.querySelectorAll(selector)
            ].map((box) => box.value);

            const allTicked = keys.length > 0
                && keys.every((key) => this.checked.includes(key));

            this.checked = allTicked
                ? this.checked.filter((key) => !keys.includes(key))
                : [...new Set([...this.checked, ...keys])];
        },

        toggleGroup(keys) {
            const availableKeys = keys.filter(
                (key) => !this.isAdded(key)
            );

            if (availableKeys.length === 0) return;

            const allTicked = availableKeys.every(
                (key) => this.checked.includes(key)
            );

            this.checked = allTicked
                ? this.checked.filter((key) => !availableKeys.includes(key))
                : [...new Set([...this.checked, ...availableKeys])];
        },

        addChecked() {
            this.$wire.selected = [
                ...new Set([
                    ...this.$wire.selected,
                    ...this.checked
                ])
            ];

            this.checked = [];
            this.pickerOpen = false;
        },

        removeItem(key) {
            this.$wire.selected = this.$wire.selected.filter(
                (item) => item !== key
            );
        },

        clamp(el, max) {
            const value = Math.min(
                Math.max(parseFloat(el.value) || 0, 0),
                max
            );

            el.value = value.toFixed(2);

            el.dispatchEvent(
                new Event('input', { bubbles: true })
            );
        },

        async post() {
            if (this.posting) return;

            this.posting = true;

            try {
                await this.$wire.postPayment();
            } finally {
                this.posting = false;
                this.confirming = false;
            }
        },
    }"
    x-on:beforeunload.window="if (total > 0) {
        $event.preventDefault();
        $event.returnValue = '';
    }"
>

    <style>
        .pm > * + * {
            margin-top: 1.25rem;
        }

        .pm [x-cloak] {
            display: none !important;
        }

        .pm-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .pm-span {
            grid-column: 1 / -1;
        }

        @media (max-width: 760px) {
            .pm-grid {
                grid-template-columns: 1fr;
            }
        }

        .pm-label {
            display: block;
            margin-bottom: .25rem;
            font-size: .875rem;
            font-weight: 500;
        }

        .pm-error {
            margin: .25rem 0 0;
            font-size: .75rem;
            font-weight: 500;
            color: #dc2626;
        }

        .pm-muted {
            opacity: .65;
        }

        .pm-small {
            font-size: .75rem;
        }

        .pm-card {
            border: 1px solid color-mix(in srgb, currentColor 18%, transparent);
            border-radius: .75rem;
            overflow: hidden;
        }

        .pm-card-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .75rem 1rem;
            border-bottom: 1px solid color-mix(in srgb, currentColor 18%, transparent);
            background: color-mix(in srgb, currentColor 5%, transparent);
        }

        /* Separate transaction items modal */

        .pm-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(0, 0, 0, .55);
            backdrop-filter: blur(2px);
        }

        .pm-modal {
            display: flex;
            flex-direction: column;
            width: min(100%, 950px);
            max-height: 90vh;
            overflow: hidden;
            border: 1px solid color-mix(in srgb, currentColor 18%, transparent);
            border-radius: 1rem;
            background: var(--fi-modal-window-bg, white);
            color: inherit;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .25);
        }

        .pm-modal-header,
        .pm-modal-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .75rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid color-mix(in srgb, currentColor 15%, transparent);
        }

        .pm-modal-footer {
            justify-content: flex-end;
            border-top: 1px solid color-mix(in srgb, currentColor 15%, transparent);
            border-bottom: 0;
        }

        .pm-modal-content {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
        }

        .pm-modal-title {
            font-size: 1rem;
            font-weight: 700;
        }

        @media (max-width: 640px) {
            .pm-modal-backdrop {
                padding: .5rem;
            }

            .pm-modal {
                max-height: 95vh;
            }
        }

        .pm-btns {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .pm-amount {
            width: 8rem;
            padding: .4rem .6rem;
            text-align: right;
            color: inherit;
            background: transparent;
            border: 1px solid color-mix(in srgb, currentColor 25%, transparent);
            border-radius: .5rem;
        }

        .pm-badge {
            display: inline-block;
            margin-left: .35rem;
            padding: 0 .5rem;
            font-size: .7rem;
            border-radius: 999px;
            background: color-mix(in srgb, currentColor 15%, transparent);
        }

        .pm-link {
            border: 0;
            background: none;
            padding: 0;
            font: inherit;
            font-weight: 600;
            color: #dc2626;
            cursor: pointer;
        }

        .pm-scroll {
            overflow-x: auto;
        }

        .pm-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .875rem;
        }

        .pm-table th,
        .pm-table td {
            padding: .6rem .75rem;
            text-align: left;
            border-top: 1px solid color-mix(in srgb, currentColor 12%, transparent);
        }

        .pm-table thead th {
            border-top: 0;
            background: color-mix(in srgb, currentColor 6%, transparent);
        }

        .pm-table .pm-right {
            text-align: right;
        }

        .pm-table .pm-center {
            text-align: center;
        }

        .pm-table .pm-nowrap {
            white-space: nowrap;
        }

        .pm-table tfoot td {
            font-weight: 600;
            background: color-mix(in srgb, #3b82f6 10%, transparent);
        }

        .pm-memf {
            grid-column: 1 / -1;
            width: 100%;
            font: inherit;
            color: inherit;
            text-align: left;
            background: transparent;
        }

        .pm-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(16rem, 1fr));
            gap: .75rem;
            padding: .75rem;
        }

        .pm-item {
            display: flex;
            gap: .65rem;
            padding: .75rem;
            border: 1px solid color-mix(in srgb, currentColor 20%, transparent);
            border-radius: .75rem;
            cursor: pointer;
            transition: border-color .15s, background .15s;
        }

        .pm-item:hover {
            border-color: #3b82f6;
        }

        .pm-item-on {
            border-color: #3b82f6;
            background: color-mix(in srgb, #3b82f6 10%, transparent);
        }

        .pm-item-off {
            opacity: .55;
            cursor: not-allowed;
        }

        .pm-item-off:hover {
            border-color: color-mix(in srgb, currentColor 20%, transparent);
        }

        .pm-item-body {
            flex: 1;
            min-width: 0;
        }

        .pm-item-top {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .4rem;
        }

        .pm-item-desc {
            margin-top: .25rem;
            font-size: .85rem;
        }

        .pm-dl {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: .15rem .75rem;
            margin: .5rem 0 0;
            font-size: .8rem;
        }

        .pm-dl dt {
            opacity: .65;
        }

        .pm-dl dd {
            margin: 0;
            text-align: right;
            white-space: nowrap;
        }

        .pm-footer {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            gap: .75rem;
        }

        .pm-confirm {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .85rem 1rem;
            border: 1px solid #16a34a;
            border-radius: .75rem;
            background: color-mix(in srgb, #16a34a 10%, transparent);
        }
    </style>

    <div>
        <div class="pm-grid">

            <div>
                <label class="pm-label" for="pm-date">
                    Payment Date
                </label>

                <x-filament::input.wrapper>
                    <x-filament::input
                        id="pm-date"
                        type="date"
                        wire:model="paymentDate"
                    />
                </x-filament::input.wrapper>

                @error('paymentDate')
                    <p class="pm-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="pm-label" for="pm-type">
                    Payment Method
                </label>

                <x-filament::input.wrapper>
                    <x-filament::input.select
                        id="pm-type"
                        wire:model="paymentType"
                    >
                        <option value="Cash">Cash</option>
                        <option value="Check">Check</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>

                @error('paymentType')
                    <p class="pm-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="pm-label" for="pm-or">
                    Official Receipt Number
                </label>

                <x-filament::input.wrapper>
                    <x-filament::input
                        id="pm-or"
                        type="text"
                        maxlength="18"
                        wire:model="orNo"
                    />
                </x-filament::input.wrapper>

                @error('orNo')
                    <p class="pm-error">{{ $message }}</p>
                @enderror
            </div>

            <div x-show="$wire.paymentType === 'Check'" x-cloak>
                <label class="pm-label" for="pm-check">
                    Check Number
                </label>

                <x-filament::input.wrapper>
                    <x-filament::input
                        id="pm-check"
                        type="text"
                        maxlength="18"
                        wire:model="checkNo"
                    />
                </x-filament::input.wrapper>

                @error('checkNo')
                    <p class="pm-error">{{ $message }}</p>
                @enderror
            </div>

            <div
                x-show="['Check', 'Bank Transfer'].includes($wire.paymentType)"
                x-cloak
            >
                <label class="pm-label" for="pm-bank">
                    Bank Name
                </label>

                <x-filament::input.wrapper>
                    <x-filament::input
                        id="pm-bank"
                        type="text"
                        maxlength="50"
                        wire:model="bankNameCode"
                    />
                </x-filament::input.wrapper>

                @error('bankNameCode')
                    <p class="pm-error">{{ $message }}</p>
                @enderror
            </div>

            <div
                x-show="$wire.paymentType === 'Bank Transfer'"
                x-cloak
            >
                <label class="pm-label" for="pm-ref">
                    Bank Transfer Reference Number
                </label>

                <x-filament::input.wrapper>
                    <x-filament::input
                        id="pm-ref"
                        type="text"
                        maxlength="50"
                        wire:model="bankReferenceNo"
                    />
                </x-filament::input.wrapper>

                @error('bankReferenceNo')
                    <p class="pm-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="pm-span">
                <label class="pm-label" for="pm-remarks">
                    Remarks
                </label>

                <x-filament::input.wrapper>
                    <x-filament::input
                        id="pm-remarks"
                        type="text"
                        maxlength="50"
                        wire:model="remarks"
                    />
                </x-filament::input.wrapper>

                @error('remarks')
                    <p class="pm-error">{{ $message }}</p>
                @enderror
            </div>

        </div>
    </div>

    <div class="pm-card">

        <div class="pm-card-head">

            <div>
                <strong>Transaction Items</strong>

                <div class="pm-muted pm-small">
                    Add transaction items to this payment, then enter the amount collected.
                </div>
            </div>

            <x-filament::button
                color="primary"
                icon="heroicon-o-plus"
                :disabled="! $itemsLoaded"
                x-on:click="openPicker()"
            >
                Select Transaction Items
            </x-filament::button>

        </div>

        <div
            style="padding: 2rem; text-align: center;"
            class="pm-muted"
            x-show="$wire.selected.length === 0"
        >
            @if ($itemsLoaded)
                No transaction items added yet.
            @else
                Loading transaction items...
            @endif
        </div>

        <div
            class="pm-scroll"
            x-show="$wire.selected.length > 0"
            x-cloak
        >

            <table class="pm-table">

                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Item Code</th>
                        <th>Description</th>
                        <th class="pm-right">Amount Due</th>
                        <th class="pm-right">Amount Paying</th>
                        <th class="pm-center">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($transactionItems as $item)

                        @php
                            $key = $item['_key'];

                            $amountDue = (float) (
                                $item['original_amount']
                                ?? $item['item_amount']
                                ?? 0
                            );

                            $balance = (float) (
                                $item['balance'] ?? $amountDue
                            );
                        @endphp

                        @continue($balance <= 0)

                        <tr
                            wire:key="pm-added-{{ $key }}"
                            x-show="isAdded('{{ $key }}')"
                            x-cloak
                        >

                            <td class="pm-nowrap">
                                <strong>
                                    {{ $item['fiscal_year'] ?? $fiscalYear }}
                                </strong>
                            </td>

                            <td class="pm-nowrap">
                                <strong>
                                    {{ $item['item_code'] ?? '' }}
                                </strong>
                            </td>

                            <td>
                                {{ $item['item_details'] ?? '' }}

                                @if (($item['previously_paid'] ?? 0) > 0)
                                    <div class="pm-muted pm-small">
                                        Previously paid:
                                        ₱{{ number_format((float) $item['previously_paid'], 2) }}
                                    </div>
                                @endif
                            </td>

                            <td class="pm-right pm-nowrap">
                                ₱{{ number_format($amountDue, 2) }}
                            </td>

                            <td class="pm-right">

                                <input
                                    type="number"
                                    class="pm-amount"
                                    min="0"
                                    max="{{ $balance }}"
                                    step="0.01"
                                    wire:model="amounts.{{ $key }}"
                                    x-on:change="clamp($el, {{ $balance }})"
                                >

                                <div
                                    class="pm-muted pm-small pm-nowrap"
                                    style="margin-top: .25rem;"
                                >
                                    Balance:
                                    ₱{{ number_format($balance, 2) }}
                                </div>

                            </td>

                            <td class="pm-center">
                                <button
                                    type="button"
                                    class="pm-link"
                                    x-on:click="removeItem('{{ $key }}')"
                                >
                                    Remove
                                </button>
                            </td>

                        </tr>

                    @endforeach

                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="4" class="pm-right">
                            Total Payment
                        </td>

                        <td
                            class="pm-right pm-nowrap"
                            style="font-size: 1.1rem;"
                        >
                            <span x-text="money(total)">₱0.00</span>
                        </td>

                        <td></td>
                    </tr>
                </tfoot>

            </table>

        </div>

    </div>

    <div
        class="pm-modal-backdrop"
        x-show="pickerOpen"
        x-cloak
        x-transition.opacity
        x-on:click.self="pickerOpen = false"
        x-on:keydown.escape.window="pickerOpen = false"
    >

        <div
            class="pm-modal"
            x-transition
            x-on:click.stop
        >

            <div class="pm-modal-header">

                <div>
                    <div class="pm-modal-title">
                        Select Transaction Items
                    </div>

                    <div class="pm-muted pm-small">
                        Choose the items you want to include in this payment.
                    </div>
                </div>

                <button
                    type="button"
                    class="pm-link"
                    x-on:click="pickerOpen = false"
                    aria-label="Close transaction items"
                >
                    <x-filament::icon
                        icon="heroicon-o-x-mark"
                        class="w-5 h-5"
                    />
                </button>

            </div>

            <div class="pm-modal-content">

                <div class="pm-card-head">

                    <div style="min-width: 150px;">

                        <label class="pm-label" for="pm-picker-year">
                            Fiscal Year
                        </label>

                        <x-filament::input.wrapper>
                            <x-filament::input.select
                                id="pm-picker-year"
                                wire:model.live="fiscalYear"
                                x-on:change="checked = []"
                            >
                                @foreach ($this->fiscalYears as $year)
                                    <option value="{{ $year }}">
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>

                    </div>

                    <x-filament::button
                        size="sm"
                        color="gray"
                        x-on:click="toggleKeys('input[data-pick]:not(:disabled)')"
                    >
                        Select / Clear All
                    </x-filament::button>

                </div>

                <div class="pm-cards" x-ref="picker">

                    @php
                        $memfItems = collect($availableItems)
                            ->filter(function ($item) {
                                $balance = (float) (
                                    $item['balance']
                                    ?? $item['original_amount']
                                    ?? $item['item_amount']
                                    ?? 0
                                );

                                return strtoupper(
                                    trim((string) ($item['tran_code'] ?? ''))
                                ) === 'MEMF' && $balance > 0;
                            })
                            ->values();

                        $memfKeys = $memfItems->pluck('_key')->all();

                        $memfBalance = $memfItems->sum(function ($item) {
                            return (float) (
                                $item['balance']
                                ?? $item['original_amount']
                                ?? $item['item_amount']
                                ?? 0
                            );
                        });
                    @endphp

                    @if ($memfItems->isNotEmpty())

                        <button
                            type="button"
                            class="pm-item pm-memf"
                            x-on:click="toggleGroup(@js($memfKeys))"
                            x-bind:class="{
                                'pm-item-on': @js($memfKeys).every(
                                    (key) => checked.includes(key) || isAdded(key)
                                )
                            }"
                        >

                            <div class="pm-item-body">

                                <div class="pm-item-top">

                                    <strong>MEMF</strong>

                                    <span class="pm-badge">
                                        {{ count($memfKeys) }}
                                        {{ \Illuminate\Support\Str::plural('item', count($memfKeys)) }}
                                    </span>

                                </div>

                                <div class="pm-item-desc">
                                    Membership Fees
                                </div>

                                <dl class="pm-dl">

                                    <dt>Total balance</dt>

                                    <dd>
                                        <strong>
                                            ₱{{ number_format($memfBalance, 2) }}
                                        </strong>
                                    </dd>

                                </dl>

                                <div
                                    class="pm-small pm-muted"
                                    style="margin-top: .4rem;"
                                >
                                    Click to select all MEMF items for this fiscal year.
                                </div>

                            </div>

                        </button>

                    @endif

                    @foreach ($availableItems as $item)

                        @php
                            $key = $item['_key'];

                            $amountDue = (float) (
                                $item['original_amount']
                                ?? $item['item_amount']
                                ?? 0
                            );

                            $balance = (float) (
                                $item['balance'] ?? $amountDue
                            );

                            $paid = $balance <= 0;

                            $isMemf = strtoupper(
                                trim((string) ($item['tran_code'] ?? ''))
                            ) === 'MEMF';
                        @endphp

                        @continue($isMemf)

                        <label
                            wire:key="pm-picker-{{ $key }}"
                            class="pm-item"
                            x-bind:class="{
                                'pm-item-on': checked.includes('{{ $key }}') || isAdded('{{ $key }}'),
                                'pm-item-off': {{ $paid ? 'true' : 'false' }} || isAdded('{{ $key }}')
                            }"
                        >

                            <input
                                type="checkbox"
                                value="{{ $key }}"
                                data-pick
                                x-bind:checked="isAdded('{{ $key }}') || checked.includes('{{ $key }}')"
                                x-bind:disabled="{{ $paid ? 'true' : 'false' }} || isAdded('{{ $key }}')"
                                x-on:change="$el.checked
                                    ? checked.push($el.value)
                                    : checked = checked.filter((item) => item !== $el.value)"
                            >

                            <div class="pm-item-body">

                                <div class="pm-item-top">

                                    <strong>
                                        {{ $item['item_code'] ?? '' }}
                                    </strong>

                                    <span>

                                        <span class="pm-badge">
                                            {{ $item['fiscal_year'] ?? $fiscalYear }}
                                        </span>

                                        @if ($paid)

                                            <span class="pm-badge">
                                                Paid
                                            </span>

                                        @else

                                            <span
                                                class="pm-badge"
                                                x-show="isAdded('{{ $key }}')"
                                                x-cloak
                                            >
                                                Added
                                            </span>

                                        @endif

                                    </span>

                                </div>

                                <div class="pm-item-desc">
                                    {{ $item['item_details'] ?? '' }}
                                </div>

                                <dl class="pm-dl">

                                    <dt>Amount</dt>

                                    <dd>
                                        ₱{{ number_format($amountDue, 2) }}
                                    </dd>

                                    @if (($item['previously_paid'] ?? 0) > 0)

                                        <dt>Previously paid</dt>

                                        <dd>
                                            ₱{{ number_format((float) $item['previously_paid'], 2) }}
                                        </dd>

                                    @endif

                                    <dt>Balance</dt>

                                    <dd>
                                        <strong>
                                            ₱{{ number_format($balance, 2) }}
                                        </strong>
                                    </dd>

                                </dl>

                            </div>

                        </label>

                    @endforeach

                    @if (empty($availableItems))

                        <div
                            class="pm-muted"
                            style="grid-column: 1 / -1; padding: 2rem; text-align: center;"
                        >
                            No transaction items are available for this fiscal year.
                        </div>

                    @endif

                </div>

            </div>

            <div class="pm-modal-footer">

                <div style="margin-right: auto;">

                    <strong x-text="checked.length"></strong>

                    <span class="pm-muted pm-small">
                        item(s) selected
                    </span>

                </div>

                <x-filament::button
                    color="gray"
                    x-on:click="pickerOpen = false"
                >
                    Cancel
                </x-filament::button>

                <x-filament::button
                    color="primary"
                    x-bind:disabled="checked.length === 0"
                    x-on:click="addChecked()"
                >
                    Add Selected
                </x-filament::button>

            </div>

        </div>

    </div>

    @error('selected')
        <p class="pm-error">{{ $message }}</p>
    @enderror

    <div
        class="pm-footer"
        x-show="! confirming"
    >

        <x-filament::button
            color="success"
            icon="heroicon-o-check-circle"
            x-bind:disabled="total <= 0"
            x-on:click="confirming = true"
        >
            Post Payment
        </x-filament::button>

    </div>

    <div
        class="pm-confirm"
        x-show="confirming"
        x-cloak
    >

        <div>
            Post
            <strong x-text="money(total)"></strong>
            for
            <strong>{{ $memberName }}</strong>?

            This cannot be undone.
        </div>

        <div class="pm-btns">

            <x-filament::button
                color="gray"
                x-bind:disabled="posting"
                x-on:click="confirming = false"
            >
                Cancel
            </x-filament::button>

            <x-filament::button
                color="success"
                x-bind:disabled="posting"
                x-on:click="post()"
            >

                <span x-show="! posting">
                    Yes, post payment
                </span>

                <span x-show="posting" x-cloak>
                    Posting…
                </span>

            </x-filament::button>

        </div>

    </div>

</div>
