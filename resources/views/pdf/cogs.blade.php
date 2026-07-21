<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "Helvetica";
            font-size: 11pt;
        }

        .page {
            position: relative;
            width: 210mm;
            height: 297mm;
        }

        /*
        |--------------------------------------------------------------------------
        | Background Letterhead
        |--------------------------------------------------------------------------
        */

        .background {
            position: absolute;
            inset: 0;
            width: 210mm;
            height: 297mm;
            z-index: -1;
        }

        /*
        |--------------------------------------------------------------------------
        | Certificate Content
        |--------------------------------------------------------------------------
        */

        .certificate {

            position: absolute;

            left: 82mm;
            top: 65mm;

            width: 118mm;

        }

        h1 {
            margin: 0 0 25px;
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            letter-spacing: 6px; /* Adjust as needed */
        }

        p {

            margin: 0;

            line-height: 1.8;

            text-align: justify;

        }

        .space {
            height: 18px;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .underline {

            display: block;

            width: 100%;

            text-align: center;

            border-bottom: 1px solid #000;

            padding-bottom: 3px;

            margin: 8px 0 16px;

            font-weight: bold;

        }

        .inline-underline {

            display: inline-block;

            border-bottom: 1px solid #000;

            padding: 0 30px 0 30px;

            font-weight: bold;

        }
        .signature-block {

            position: absolute;

            left: 118mm;
            top: 212mm;

            width: 150mm;

            z-index: 2;
        }

        .dry-seal {

            position: absolute;

            left: 25%;
            top: 2mm;

            width: 60mm;

            transform: translateX(-50%);

            opacity: .18;

            z-index: 1;
        }

        .signatories {

            position: relative;

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            z-index: 2;

        }

        .signatory {

            width: 45%;

            text-align: center;

        }

        .signatory img {

            width: 100%;

            height: auto;

        }

        .signatory-name {

            margin-top: 5px;

            font-weight: bold;

            font-size: 11pt;

        }

        .signatory-title {

            font-size: 10pt;

        }
    </style>

</head>

<body>

    <div class="page">

        <img
            class="background"
            src="{{ public_path('images/letterhead-2026.png') }}">

        <div class="certificate">

            <h1>
                CERTIFICATION
            </h1>

            <div class="space"></div>

            <p>
                To Whom It May Concern:
            </p>

            <div class="space"></div>
            <div class="space"></div>

            <p>
                This certifies that <span class="inline-underline">
                    &emsp; {{ $memberName }} &emsp;
                </span>

                is a member of Good Standing for Fiscal Year

                <strong>  {{ $fiscalYear }} </strong>

                and is classified as <span class="inline-underline">&emsp; {{ $membershipType }} &emsp;</span>


                of the Philippine Society of
                Anesthesiologists, Inc. He/She is also a member of the
                World Federation of Societies of
                Anaesthesiologists (WFSA).
            </p>

            <div class="space"></div>

            <p>

                This certification is being issued upon the request of

                <span class="inline-underline">

                    &emsp; {{ $requestedBy }} &emsp;

                </span>

                for

                <span class="inline-underline">

                    &emsp; {{ $purpose }} &emsp;

                </span>.

            </p>

            <div class="space"></div>

            <p>

                Given this

                <span class="inline-underline">
                    &emsp;
                    {{ $issueDate }}
                    &emsp;
                </span>

                at Suite 102, PSA Secretariat Office,
                PMA Building, North Avenue,
                Quezon City, Philippines.

            </p>

        </div>

        <div class="signature-block">

            <img
                src="{{ public_path('images/dry_seal.png') }}"
                class="dry-seal">

            <div class="signatories">

                <div class="signatory">

                    <img
                        src="{{ public_path('images/morete sig.png') }}">

                    <div class="signatory-name">
                        {{ $secretary }}
                    </div>

                    <div class="signatory-title">
                        Secretary, PSA Inc.
                    </div>

                </div>

                <div class="signatory">

                    <img
                        src="{{ public_path('images/mayuga sig.png') }}">

                    <div class="signatory-name">
                        {{ $president }}
                    </div>

                    <div class="signatory-title">
                        President, PSA Inc.
                    </div>

                </div>

            </div>

        </div>

    </div>

</body>

</html>
