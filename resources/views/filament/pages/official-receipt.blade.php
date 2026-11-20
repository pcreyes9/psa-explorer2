<x-filament-panels::page>
    <div class="or-entry">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-gray-600 dark:text-gray-300">Enter the receipt details, then print them on the supplied OR layout.</p>
            <div class="flex gap-3">
                <button type="button" wire:click="resetForm" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">Clear form</button>
                <button type="button" wire:click="printReceipt" wire:loading.attr="disabled" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 disabled:opacity-50">Print receipt</button>
            </div>
        </div>

        <div class="space-y-5">
            <section class="or-card">
                <h2>Receipt details</h2>
                <div class="or-form-grid">
                    <label>Date of payment<input type="date" wire:model="receipt.payment_date"></label>
                    <label>Received from<input type="text" wire:model="receipt.received_from"></label>
                    <label>TIN<input type="text" wire:model="receipt.tin"></label>
                    <label>Business style / name<input type="text" wire:model="receipt.business_name"></label>
                    <label class="or-wide">Address<input type="text" wire:model="receipt.address"></label>
                    <label class="or-wide">Amount in words (automatic)<input type="text" wire:model="receipt.amount_words" readonly></label>
                    <label>Amount (Php)<input type="number" min="0" step="0.01" wire:model.live.debounce.300ms="receipt.amount"></label>
                </div>
            </section>

            <section class="or-card">
                <h2>Payment for</h2>
                <div class="or-form-grid">
                    <label>In the payment of
                        <select wire:model="paymentFor">
                            <option value="">Select payment type</option>
                            <option value="Membership Dues">Membership Dues</option>
                            <option value="Convention Registration">Convention Registration</option>
                        </select>
                    </label>
                    <label>Annual dues — fiscal year<input type="text" wire:model="receipt.fiscal_year"></label>
                    <label>Regular members amount (Php)<input type="number" min="0" step="0.01" wire:model="receipt.regular_members_amount"></label>
                    <fieldset class="or-wide"><legend>Regular member categories</legend><div class="or-options">
                        @foreach (['Life', 'Non-Life', 'Active', 'Retired', 'Below 65 y/o', 'Above 65 y/o'] as $category)
                            <label><input type="checkbox" wire:model="memberCategories" value="{{ $category }}">{{ $category }}</label>
                        @endforeach
                    </div></fieldset>
                    <label>Trainee members amount (Php)<input type="number" min="0" step="0.01" wire:model="receipt.trainee_members_amount"></label>
                    <fieldset><legend>Trainee year level</legend><div class="or-options">
                        @foreach (['I', 'II', 'III'] as $level)
                            <label><input type="radio" wire:model="receipt.trainee_year_level" value="{{ $level }}">{{ $level }}</label>
                        @endforeach
                    </div></fieldset>
                    <fieldset class="or-wide"><legend>Registration fees</legend><div class="or-options">
                        @foreach (['Annual Convention', 'Postgraduate Course', 'Midyear Convention'] as $fee)
                            <label><input type="checkbox" wire:model="registrationFees" value="{{ $fee }}">{{ $fee }}</label>
                        @endforeach
                        <label><input type="checkbox" wire:model="registrationFees" value="Others">Others</label>
                        <input type="text" aria-label="Other registration fee" placeholder="Specify other fee" wire:model="receipt.other_registration">
                    </div></fieldset>
                    <label>Other assessment — specify<input type="text" wire:model="receipt.other_assessment"></label>
                    <label>Other assessment amount (Php)<input type="number" min="0" step="0.01" wire:model="receipt.other_assessment_amount"></label>
                </div>
            </section>

            <section class="or-card">
                <h2>Form of payment</h2>
                <div class="or-options or-payment-options">
                    @foreach (['Cash', 'Interbranch', 'Credit Card', 'PMO No.', 'Cheque'] as $method)
                        <label><input type="radio" wire:model="paymentMethod" value="{{ $method }}">{{ $method }}</label>
                    @endforeach
                </div>
                <div class="or-form-grid or-payment-details">
                    <label>Bank<input type="text" wire:model="receipt.bank"></label>
                    <label>Payment number<input type="text" wire:model="receipt.payment_number"></label>
                    <label>PMO number<input type="text" wire:model="receipt.pmo_number"></label>
                    <label>Payment date<input type="date" wire:model="receipt.payment_date_detail"></label>
                </div>
            </section>

            <section class="or-card">
                <h2>Receipt acknowledgment</h2>
                <label class="or-received-input">Received by<input type="text" wire:model="receipt.received_by"></label>
            </section>
        </div>
    </div>

    @php
        $formatMoney = static function ($amount): string {
            return $amount === null || $amount === ''
                ? ''
                : number_format((float) $amount, 2, '.', ',');
        };
    @endphp

    <div class="or-print-page" x-on:print-receipt.window="window.print()">
        <div class="or-sheet">
            <span class="or-bar payment-date-bar"></span>
            <span class="or-value service-invoice">SERVICE INVOICE</span>
            {{-- <span class="or-value payment-date">{{ $receipt['payment_date'] ? \Illuminate\Support\Carbon::parse($receipt['payment_date'])->format('m/d/Y') : '' }}</span> --}}
            <span class="or-value received-from">{{ $receipt['received_from'] }}</span>
            <span class="or-value tin">{{ $receipt['tin'] }}</span>
            <span class="or-value business-name">{{ $receipt['business_name'] }}</span>
            <span class="or-value address">{{ $receipt['address'] }}</span>
            <span class="or-value amount-words">{{ $receipt['amount_words'] }}</span>
            <span class="or-value amount">{{ $formatMoney($receipt['amount']) }}</span>
            <span class="or-value fiscal-year">{{ $receipt['fiscal_year'] }}</span>
            <span class="or-value payment-for">{{ $paymentFor }}</span>
            <span class="or-value regular-amount">{{ $formatMoney($receipt['regular_members_amount']) }}</span>
            @php($memberMarks = ['life' => 'Life', 'non-life' => 'Non-Life', 'active' => 'Active', 'retired' => 'Retired', 'below-65' => 'Below 65 y/o', 'above-65' => 'Above 65 y/o'])
            @foreach ($memberMarks as $class => $category)
                @if (in_array($category, $memberCategories))<span class="or-mark {{ $class }}">X</span>@endif
            @endforeach
            <span class="or-value trainee-amount">{{ $formatMoney($receipt['trainee_members_amount']) }}</span>
            @foreach (['I', 'II', 'III'] as $index => $level)
                @if ($receipt['trainee_year_level'] === $level)<span class="or-mark trainee-level-{{ $index + 1 }}">X</span>@endif
            @endforeach
            @php($registrationMarks = ['Annual Convention', 'Postgraduate Course', 'Midyear Convention', 'Others'])
            @foreach ($registrationMarks as $index => $fee)
                @if (in_array($fee, $registrationFees))<span class="or-mark registration-{{ $index + 1 }}">X</span>@endif
            @endforeach
            <span class="or-value other-registration">{{ $receipt['other_registration'] }}</span>
            <span class="or-value other-assessment">{{ $receipt['other_assessment'] }}</span>
            <span class="or-value other-assessment-amount">{{ $formatMoney($receipt['other_assessment_amount']) }}</span>
            @foreach (['Cash', 'Interbranch', 'Credit Card', 'PMO No.', 'Cheque'] as $index => $method)
                @if ($paymentMethod === $method)<span class="or-mark payment-method-{{ $index + 1 }}">X</span>@endif
            @endforeach
            <span class="or-value bank">{{ $receipt['bank'] }}</span>
            <span class="or-value payment-number">{{ $receipt['payment_number'] }}</span>
            <span class="or-value pmo-number">{{ $receipt['pmo_number'] }}</span>
            <span class="or-value payment-detail-date">{{ $receipt['payment_date_detail'] ? \Illuminate\Support\Carbon::parse($receipt['payment_date_detail'])->format('m/d/Y') : '' }}</span>
            <span class="or-value received-by">{{ $receipt['received_by'] }}</span>
        </div>
    </div>

    <style>
        .or-card {
            border: 1px solid rgb(229 231 235);
            border-radius: 12px;
            background: white;
            padding: 20px;
            box-shadow: 0 1px 2px rgb(0 0 0 / 5%);
        }

        .dark .or-card {
            border-color: rgb(55 65 81);
            background: rgb(17 24 39);
        }

        .or-card h2 {
            margin: 0 0 16px;
            font-size: 16px;
            font-weight: 650;
            color: rgb(17 24 39);
        }

        .dark .or-card h2 {
            color: white;
        }

        .or-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .or-form-grid > label,
        .or-received-input {
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: rgb(55 65 81);
        }

        .dark .or-form-grid > label,
        .dark .or-received-input {
            color: rgb(209 213 219);
        }

        .or-entry input[type="text"],
        .or-entry input[type="number"],
        .or-entry input[type="date"],
        .or-entry select {
            width: 100%;
            min-height: 40px;
            border: 1px solid rgb(209 213 219);
            border-radius: 8px;
            background: white;
            padding: 8px 10px;
            color: rgb(17 24 39);
            font-size: 14px;
            font-weight: 400;
        }

        .dark .or-entry input[type="text"],
        .dark .or-entry input[type="number"],
        .dark .or-entry input[type="date"],
        .dark .or-entry select {
            border-color: rgb(75 85 99);
            background: rgb(3 7 18);
            color: white;
        }

        .or-form-grid .or-wide {
            grid-column: 1 / -1;
        }

        .or-form-grid fieldset {
            min-width: 0;
            border: 1px solid rgb(209 213 219);
            border-radius: 8px;
            padding: 10px 12px;
        }

        .dark .or-form-grid fieldset {
            border-color: rgb(75 85 99);
        }

        .or-form-grid legend {
            padding: 0 4px;
            font-size: 13px;
            font-weight: 600;
        }

        .or-options {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px 20px;
        }

        .or-options label {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 14px;
        }

        .or-options input[type="checkbox"],
        .or-options input[type="radio"] {
            width: 16px;
            height: 16px;
            accent-color: rgb(37 99 235);
        }

        .or-options > input[type="text"] {
            width: min(240px, 100%) !important;
        }

        .or-payment-options {
            margin-bottom: 16px;
        }

        .or-payment-details {
            padding-top: 4px;
        }

        .or-received-input {
            max-width: 520px;
        }

        .or-print-page {
            display: none;
        }

        .or-sheet {
            position: relative;
            width: 100%;
            aspect-ratio: 1428 / 920;
            color: #111;
        }

        .or-value,
        .or-mark {
            position: absolute;
            z-index: 1;
            color: #111;
            font: 12px Arial, sans-serif;
            line-height: 1.15;
            overflow: hidden;
        }

        .or-value {
            white-space: nowrap;
        }

        .or-mark {
            font-weight: bold;
            text-align: center;
        }

        .payment-date {
            left: 68.5%;
            top: 16.7%;
            width: 24%;
        }

        .or-bar {
            display: block;
            position: absolute;
            z-index: 10;
            border-top: 15px solid #000;
        }

        .payment-date-bar {
            left: 45%;
            top: 9%;
            width: 24%;
            height: 100px;
        }

        /* OLD
        .payment-date-bar {
            left: 48%;
            top: 7%;
            width: 24%;
            height: 100px;
        } */

        .service-invoice {
            left: 45%;
            top: 4%;
            width: 24%;
            font-size: 12pt;
            font-weight: 500;
        }

        .received-from {
            left: 5%;
            top: 27.3%;
            width: 75%;
        }

        .tin {
            left: 0%;
            top: 33%;
            width: 22%;
        }

        .business-name {
            left: 48%;
            top: 32%;
            width: 51%;
        }

        .address {
            left: 0%;
            top: 38%;
            width: 77%;
        }

        .amount-words {
            left: 5%;
            top: 42%;
            width: 68%;
        }

        .amount {
            left: 73%;
            top: 47%;
            width: 16%;
        }

        .fiscal-year {
            left: 13%;
            top: 61%;
            width: 16%;
        }

        .payment-for {
            left: 13%;
            top: 53.5%;
            width: 60%;
        }

        .regular-amount {
            left: 30%;
            top: 56.8%;
            width: 14%;
        }

        .life {
            left: 12.4%;
            top: 59.1%;
            width: 1.55%;
            height: 2.4%;
        }

        .non-life {
            left: 12.4%;
            top: 61.8%;
            width: 1.55%;
            height: 2.4%;
        }

        .active {
            left: 21.2%;
            top: 59.1%;
            width: 1.55%;
            height: 2.4%;
        }

        .retired {
            left: 21.2%;
            top: 61.8%;
            width: 1.55%;
            height: 2.4%;
        }

        .below-65 {
            left: 28.4%;
            top: 59.1%;
            width: 1.55%;
            height: 2.4%;
        }

        .above-65 {
            left: 28.4%;
            top: 61.8%;
            width: 1.55%;
            height: 2.4%;
        }

        .trainee-amount {
            left: 28%;
            top: 64.4%;
            width: 14%;
        }

        .trainee-level-1 {
            left: 20.1%;
            top: 68.7%;
            width: 1.55%;
            height: 2.4%;
        }

        .trainee-level-2 {
            left: 25%;
            top: 68.7%;
            width: 1.55%;
            height: 2.4%;
        }

        .trainee-level-3 {
            left: 29.6%;
            top: 68.7%;
            width: 1.55%;
            height: 2.4%;
        }

        .registration-1 {
            left: 12.4%;
            top: 76.5%;
            width: 1.55%;
            height: 2.4%;
        }

        .registration-2 {
            left: 12.4%;
            top: 79.2%;
            width: 1.55%;
            height: 2.4%;
        }

        .registration-3 {
            left: 28.5%;
            top: 76.5%;
            width: 1.55%;
            height: 2.4%;
        }

        .registration-4 {
            left: 28.5%;
            top: 79.2%;
            width: 1.55%;
            height: 2.4%;
        }

        .other-registration {
            left: 38%;
            top: 78.7%;
            width: 12%;
        }

        .other-assessment {
            left: 21%;
            top: 87.4%;
            width: 24%;
        }

        .other-assessment-amount {
            left: 18%;
            top: 90.2%;
            width: 15%;
        }

        /* CASH */
        .payment-method-1 {
            left: 40.85%;
            top: 61.9%;
            width: 1.55%;
            height: 2.4%;
        }
        

        .payment-method-2 {
            left: 50.7%;
            top: 56.5%;
            width: 1.55%;
            height: 2.4%;
        }

        .payment-method-3 {
            left: 50.7%;
            top: 59.2%;
            width: 1.55%;
            height: 2.4%;
        }

        .payment-method-4 {
            left: 50.7%;
            top: 61.9%;
            width: 1.55%;
            height: 2.4%;
        }

        .payment-method-5 {
            left: 50.7%;
            top: 64.6%;
            width: 1.55%;
            height: 2.4%;
        }

        .bank {
            left: 72.3%;
            top: 56.8%;
            width: 20%;
        }

        .payment-number {
            left: 72.3%;
            top: 59.5%;
            width: 20%;
        }

        .pmo-number {
            left: 59.8%;
            top: 62.2%;
            width: 6%;
        }

        .payment-detail-date {
            left: 72.3%;
            top: 62.2%;
            width: 20%;
        }

        .received-by {
            left: 68%;
            top: 78.1%;
            width: 25%;
        }

        @media (max-width: 700px) {
            .or-form-grid {
                grid-template-columns: 1fr;
            }

            .or-form-grid .or-wide {
                grid-column: auto;
            }
        }

        @media print {
            @page {
                size: letter landscape;
                margin: 0;
            }

            html,
            body {
                width: 11in !important;
                height: 8.5in !important;
                min-height: 0 !important;
                max-height: 8.5in !important;
                margin: 0 !important;
                overflow: hidden !important;
            }

            .or-entry {
                display: none !important;
            }

            body * {
                visibility: hidden !important;
            }

            .or-print-page,
            .or-print-page * {
                visibility: visible !important;
            }

            .or-print-page {
                display: block !important;
                position: fixed;
                top: 0;
                left: 0;
                width: 11in;
                height: 8.5in;
                overflow: hidden;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }

            .or-sheet {
                position: absolute;
                top: calc(25.36% - 1.2in);
                left: 39.1%;
                width: 59.09%;
                height: 49.26%;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }

            .or-value,
            .or-mark {
                font-family: "Courier New", monospace !important;
                font-size: 9pt;
                color: #000 !important;
            }

            .or-mark {
                font-weight: bold;
            }

            .or-print-page .service-invoice {
                font-size: 12pt;
                font-weight: 500;
            }
        }
    </style>
</x-filament-panels::page>
