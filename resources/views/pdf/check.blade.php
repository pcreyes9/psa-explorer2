<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <style>

        @page {
            size: letter landscape;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;

            width: 279.4mm;
            height: 215.9mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000000;
            font-size: 11pt;
        }


        /* =========================================================
           CHECK PAGE
        ========================================================== */

        .check {
            position: relative;

            width: 279.4mm;
            height: 215.9mm;

            overflow: hidden;
        }


        /* =========================================================
        DATE
        ========================================================= */

        .date {
            position: absolute;

            /*
            |--------------------------------------------------------------------------
            | PDF page = 792pt × 612pt
            |
            | First date digit starts at:
            | X = 644.40pt
            |
            | Converted:
            | X = 227.31mm
            |
            | PDF Y of text top ≈ 242.23pt
            |
            | CSS/Dompdf top:
            | 612 - 242.23 = 369.77pt
            | 369.77pt = 130.44mm
            |--------------------------------------------------------------------------
            */

            left: 227.31mm;
            top: 80.0mm;
            width: 42mm;
            height: 5mm;

            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            font-weight: normal;

            white-space: nowrap;
        }


        /*
        |--------------------------------------------------------------------------
        | INDIVIDUAL DATE DIGITS
        |--------------------------------------------------------------------------
        */

        .date-digit {
            position: absolute;

            top: 0;

            width: 3mm;
            height: 5mm;

            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            font-weight: normal;

            line-height: 5mm;

            text-align: left;
        }


        /*
        |--------------------------------------------------------------------------
        | 08
        |--------------------------------------------------------------------------
        |
        | PDF:
        | 0 = 644.40pt
        | 8 = 655.92pt
        |
        | Difference = 11.52pt = 4.06mm
        |
        */

        .date-d1 {
            left: -1mm;
        }

        .date-d2 {
            left: 3.30mm;
        }


        /*
        |--------------------------------------------------------------------------
        | 11
        |--------------------------------------------------------------------------
        |
        | PDF:
        | 1 = 678.24pt
        | 1 = 689.76pt
        |
        */

        .date-m1 {
            left: 10.93mm;
        }

        .date-m2 {
            left: 15.10mm;
        }


        /*
        |--------------------------------------------------------------------------
        | 2026
        |--------------------------------------------------------------------------
        |
        | PDF:
        | 2 = 711.36pt
        | 0 = 725.04pt
        | 2 = 738.72pt
        | 6 = 752.40pt
        |
        */

        .date-y1 {
            left: 23.51mm;
        }

        .date-y2 {
            left: 28.34mm;
        }

        .date-y3 {
            left: 33.17mm;
        }

        .date-y4 {
            left: 38.0mm;
        }


        /* =========================================================
           PAYEE
        ========================================================== */

        .payee {
            position: absolute;

            top: 89.9mm;
            left: 105.2mm;

            width: 100mm;

            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            font-weight: bold;

            line-height: 1;

            text-align: left;

            white-space: nowrap;
        }


        /* =========================================================
           NUMERIC AMOUNT
        ========================================================== */

        .amount {
            position: absolute;

            top: 89.2mm;
            left: 226.2mm;

            width: 35mm;

            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            font-weight: bold;

            line-height: 1;

            text-align: left;

            white-space: nowrap;
        }


        /* =========================================================
           AMOUNT IN WORDS
        ========================================================== */

        .amount-words {
            position: absolute;

            top: 98.6mm;
            left: 102.0mm;

            width: 146mm;

            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            font-weight: normal;

            line-height: 1.25;

            text-align: left;

            white-space: nowrap;
        }


    </style>

</head>


<body>

    <div class="check">


        {{-- =====================================================
             DATE
        ====================================================== --}}

        @php
            $checkDate = \Carbon\Carbon::parse($date)->format('mdY');
        @endphp

        <div class="date">

            {{-- 08 --}}
            <span class="date-digit date-d1">
                {{ substr($checkDate, 0, 1) }}
            </span>

            <span class="date-digit date-d2">
                {{ substr($checkDate, 1, 1) }}
            </span>


            {{-- 11 --}}
            <span class="date-digit date-m1">
                {{ substr($checkDate, 2, 1) }}
            </span>

            <span class="date-digit date-m2">
                {{ substr($checkDate, 3, 1) }}
            </span>


            {{-- 2026 --}}
            <span class="date-digit date-y1">
                {{ substr($checkDate, 4, 1) }}
            </span>

            <span class="date-digit date-y2">
                {{ substr($checkDate, 5, 1) }}
            </span>

            <span class="date-digit date-y3">
                {{ substr($checkDate, 6, 1) }}
            </span>

            <span class="date-digit date-y4">
                {{ substr($checkDate, 7, 1) }}
            </span>

        </div>


        {{-- =====================================================
             PAYEE
        ====================================================== --}}

        <div class="payee">
            {{ $payTo }}
        </div>


        {{-- =====================================================
             NUMERIC AMOUNT
        ====================================================== --}}

        <div class="amount">
            {{ number_format((float) $amount, 2) }}
        </div>


        {{-- =====================================================
             AMOUNT IN WORDS
        ====================================================== --}}

        <div class="amount-words">
            {{ strtoupper($amountInWords) }}
        </div>


    </div>

</body>

</html>