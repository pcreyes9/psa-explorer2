<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <title>Voucher - {{ $voucherNo }}</title>

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


        /*
        |--------------------------------------------------------------------------
        | Pay To
        |--------------------------------------------------------------------------
        */

        .pay-to {
            position: absolute;

            top: 1.55in;
            left: 1.10in;

            width: 4.8in;

            font-size: 11pt;
        }


        /*
        |--------------------------------------------------------------------------
        | Voucher Number
        |--------------------------------------------------------------------------
        */

        .voucher-number {
            position: absolute;

            top: 1.50in;
            right: 0.90in;

            width: 1.4in;

            text-align: center;

            font-size: 11pt;

            white-space: nowrap;
        }


        /*
        |--------------------------------------------------------------------------
        | Address
        |--------------------------------------------------------------------------
        */

        .address {
            position: absolute;

            top: 2.00in;
            left: 1.10in;

            width: 4.8in;

            font-size: 11pt;
        }


        /*
        |--------------------------------------------------------------------------
        | Date
        |--------------------------------------------------------------------------
        */

        .date {
            position: absolute;

            top: 2.00in;
            right: 1.40in;

            width: 1.4in;

            text-align: right;

            font-size: 11pt;

            white-space: nowrap;
        }


        /*
        |--------------------------------------------------------------------------
        | Items
        |--------------------------------------------------------------------------
        */

        .items {
            position: absolute;

            top: 2.85in;

            left: 0.60in;
            right: 0.86in;
        }


        /*
        |--------------------------------------------------------------------------
        | Items Table
        |--------------------------------------------------------------------------
        */

        .items-table {
            width: 100%;

            border-collapse: collapse;

            table-layout: fixed;
        }


        .description-cell {
            width: 68%;
            padding: 0 0.15in 0 0;
            vertical-align: bottom;
            line-height: 0.20in;
            text-align: left;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .amount-cell {
            width: 32%;
            padding: 0 0 2px 0;
            vertical-align: bottom;
            text-align: right;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            line-height: 0.20in;
            white-space: nowrap;
            /* border-bottom: 1px solid #000000; */
        }

        .amount-underline {
            display: inline-block;
            border-bottom: 1px solid #000;
            padding: 0 0.05in 2px 0.05in;
        }


        /*
        |--------------------------------------------------------------------------
        | Item Spacing
        |--------------------------------------------------------------------------
        */

        .item-row td {
            padding-bottom: 0.08in;
        }


        /*
        |--------------------------------------------------------------------------
        | Total
        |--------------------------------------------------------------------------
        */

        .total {
            position: absolute;

            top: 4.65in;

            right: 0.85in;

            width: 1.55in;

            text-align: right;

            font-weight: bold;

            font-size: 12pt;

            font-family: 'DejaVu Sans', sans-serif;

            white-space: nowrap;

            border-bottom: 3px double #000000;

            padding-bottom: 3px;
        }


        /*
        |--------------------------------------------------------------------------
        | Approval Section
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Check Number
        |--------------------------------------------------------------------------
        */

        .check-number {
            position: absolute;

            top: 1.90in;
            right: 1.10in;

            width: 1.4in;

            text-align: right;

            white-space: nowrap;
        }


        /*
        |--------------------------------------------------------------------------
        | General Amount
        |--------------------------------------------------------------------------
        */

        .amount {
            white-space: nowrap;
        }


        /*
        |--------------------------------------------------------------------------
        | Description
        |--------------------------------------------------------------------------
        */

        .description {
            line-height: 1.25;
        }

    </style>

</head>


<body>

<div class="voucher">


    {{-- ============================================================
         PAY TO
         ============================================================ --}}

    <div class="pay-to">
        {{ $payTo }}
    </div>


    {{-- ============================================================
         VOUCHER NUMBER
         ============================================================ --}}

    <div class="voucher-number">
        {{ $checkNo }}
    </div>


    {{-- ============================================================
         ADDRESS
         ============================================================ --}}

    <div class="address">
        {{ $address }}
    </div>


    {{-- ============================================================
         DATE
         ============================================================ --}}

    <div class="date">
        {{ \Carbon\Carbon::parse($date)->format('m/d/Y') }}
    </div>


    {{-- ============================================================
         ITEMS
         ============================================================ --}}

    <div class="items">

        <table class="items-table">

            <tbody>

                @foreach($items as $item)

                    <tr class="item-row">

                        {{-- Description --}}

                        <td class="description-cell">

                            {!! nl2br(
                                e($item['description'] ?? '')
                            ) !!}

                        </td>


                        {{-- Amount --}}

                        <td class="amount-cell">
                            <span class="amount-underline">
                                ₱{{ number_format((float) ($item['amount'] ?? 0), 2) }}
                            </span>
                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>


    {{-- ============================================================
         TOTAL
         ============================================================ --}}

    <div class="total">

        ₱{{ number_format(
            collect($items)->sum(function ($item) {
                return (float) ($item['amount'] ?? 0);
            }),
            2
        ) }}

    </div>


    {{-- ============================================================
         APPROVED BY
         ============================================================ --}}

    <div class="approved-by">

        {{ $approvedBy }}

    </div>


    {{-- ============================================================
         CHECKED BY
         ============================================================ --}}

    <div class="checked-by">

        {{ $checkedBy }}

    </div>


    {{-- ============================================================
         RECEIVED BY
         ============================================================ --}}

    <div class="received-by">

        {{ $receivedBy }}

    </div>


</div>

</body>

</html>