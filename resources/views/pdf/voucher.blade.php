<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>Cash Voucher</title>

    <style>

        @page {
            size: letter portrait;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #000000;
        }

        .voucher {
            position: relative;
            width: 8.5in;
            height: 11in;
        }


        .pay-to {
            position: absolute;
            top: 1.55in;
            left: 1.10in;
            width: 4.8in;
            font-size: 11pt;
        }

        .voucher-number {
            position: absolute;
            top: 1.50in;
            right: 1.20in;
            width: 1.4in;
            text-align: right;
            font-size: 11pt;
        }

        .address {
            position: absolute;
            top: 2.00in;
            left: 1.10in;
            width: 4.8in;
            font-size: 11pt;
        }

        .date {
            position: absolute;
            top: 2.00in;
            right: 1.40in;
            width: 1.4in;
            text-align: right;
            font-size: 11pt;
        }

        .items {
            position: absolute;
            top: 2.85in;
            left: 0.60in;
            right: 0.86in;
        }

        .item-row {
            position: relative;
            width: 100%;
        }

        .item-description {
            width: 4.5in;
            padding-left: 0;
            padding-right: 0.15in;

            line-height: 0.20in;
            text-align: left;

            white-space: normal;
        }

        .item-amount {
            position: absolute;
            right: 0;
            width: 1.25in;
            line-height: 0.20in;

            text-align: right;
            font-weight: normal;
            font-family: 'DejaVu Sans', sans-serif;

            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }

        .total {
            position: absolute;

            right: 0.85in;

            width: 1.55in;

            text-align: right;

            font-weight: bold;
            font-size: 12pt;

            border-bottom: 3px double #000000;
            padding-bottom: 3px;
        }

        .item-amount,
        .total {
            font-family: 'DejaVu Sans', sans-serif;
        }

        .approved-by {
            position: absolute;
            left: 0.70in;
            top: 5.60in;
            width: 1.6in;
            text-align: left;
            font-size: 10pt;
        }

        .checked-by {
            position: absolute;
            left: 2.60in;
            top: 5.60in;
            width: 1.6in;
            text-align: left;
            font-size: 10pt;
        }

        .received-by {
            position: absolute;
            left: 4.85in;
            top: 5.60in;
            width: 1.8in;
            text-align: left;
            font-size: 10pt;
        }


        .check-number {
            position: absolute;
            top: 1.90in;
            right: 1.10in;
            width: 1.4in;
            text-align: right;
        }

        .amount {
            white-space: nowrap;
        }

        .description {
            line-height: 1.25;
        }

    </style>
</head>

<body>

<div class="voucher">

    <div class="pay-to">
        {{ $payTo }}
    </div>

    <div class="voucher-number">
        {{ $checkNo }}
    </div>

    <div class="address">
        {{ $address }}
    </div>

    <div class="date">
        {{ \Carbon\Carbon::parse($date)->format('m/d/Y') }}
    </div>

    <div class="items">

        @foreach($items as $item)

            @php
                $description = $item['description'] ?? '';

                /*
                |--------------------------------------------------------------------------
                | Estimate description lines
                |--------------------------------------------------------------------------
                */

                $charactersPerLine = 55;

                $paragraphs = preg_split("/\r\n|\n|\r/", $description);

                $lineCount = 0;

                foreach ($paragraphs as $line) {

                    $lineCount += max(
                        1,
                        (int) ceil(
                            mb_strlen($line) / $charactersPerLine
                        )
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Description line height
                |--------------------------------------------------------------------------
                */

                $lineHeight = 0.20;

                /*
                |--------------------------------------------------------------------------
                | Amount must sit on the LAST description line
                |--------------------------------------------------------------------------
                */

                $amountTop = ($lineCount - 1) * $lineHeight;

                /*
                |--------------------------------------------------------------------------
                | Height of this item
                |--------------------------------------------------------------------------
                */

                $itemHeight = $lineCount * $lineHeight;
            @endphp


            <div
                class="item-row"
                style="
                    height: {{ $itemHeight }}in;
                    margin-bottom: 0.07in;
                "
            >

                <div class="item-description">
                    {!! nl2br(e($item['description'])) !!}
                </div>

                <div
                    class="item-amount"
                    style="top: {{ $amountTop }}in;"
                >
                    ₱{{ number_format((float) $item['amount'], 2) }}
                </div>

            </div>

        @endforeach

    </div>

    @php
        $itemCount = count($items);

        // Starting position of the total.
        $baseTotalTop = 3.70;

        // Height of each item row.
        $itemHeight = 0.27;

        // Calculate total position based on number of items.
        $totalTop = $baseTotalTop + ($itemCount * $itemHeight);
    @endphp

    <div
        class="total"
        style="top: {{ $totalTop }}in;"
    >
        ₱{{ number_format(
            collect($items)->sum(function ($item) {
                return (float) $item['amount'];
            }),
            2
        ) }}
    </div>

    <div class="approved-by">
        {{ $approvedBy }}
    </div>

    <div class="checked-by">
        {{ $checkedBy }}
    </div>

    <div class="received-by">
        {{ $receivedBy }}
    </div>

</div>

</body>
</html>
