
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Statement of Account - {{ $member->member_id_no }} </title>
    <style>
        {!! file_get_contents(public_path('css/statement-of-account.css')) !!}
    </style>
</head>

<body>

    <div class="brand-bar"></div>

    {{-- PSA LETTERHEAD --}}
    
    <table class="letterhead">
        <tr>
            <td width="15%" class="logo-cell">
                <img
                    src="{{ public_path('images/PSA_LOGO.png') }}"
                    class="psa-logo"
                    alt="PSA Logo"
                >
            </td>

            <td width="75%">
                <div class="brand-name">
                    PHILIPPINE SOCIETY OF ANESTHESIOLOGISTS, INC.
                </div>

                <div class="brand-subtitle">
                    Room 102 Ground Floor, PMA Building,
                    North Avenue, Diliman, Quezon City, 1100
                </div>
            </td>

            <td width="10%" class="contact">
                psainc_sec@yahoo.com<br>
                0917 832 9069<br>
                0920 952 2120
            </td>
        </tr>
    </table>


    {{-- DOCUMENT TITLE --}}
    <div class="document-heading">
        <h1>STATEMENT OF ACCOUNT</h1>
        <p>
            Membership dues statement
            &nbsp;|&nbsp;
            Statement date: <strong>{{ $statementDate->format('F d, Y') }}</strong>
        </p>
    </div>

    {{-- MEMBER DETAILS --}}
    <table class="member-card">
        {{-- ROW 1: MEMBER NAME AND ADDRESS --}}
        <tr>
            <td width="40%">
                <span class="field-label">Member Name</span>
                <span class="field-value">
                    {{ strtoupper(trim(
                        $member->mem_last_name . ', ' .
                        $member->mem_first_name . ' ' .
                        ($member->mem_middle_name ?? '')
                    )) }}
                </span>
            </td>

            <td width="60%" colspan="3">
                <span class="field-label">Address</span>
                <span class="field-value">
                    {{ $member->mem_home_address ?: '-' }}
                </span>
            </td>
        </tr>

        {{-- ROW 2: OTHER MEMBER DETAILS --}}
        <tr>
            <td width="20%">
                <span class="field-label">Email Address</span>
                <span class="field-value">
                    {{ $member->mem_email_address ?: '-' }}
                </span>
            </td>

            <td width="35%">
                <span class="field-label">PSA Number</span>
                <span class="field-value">
                    {{ $member->member_id_no }}
                </span>
            </td>

            <td width="25%">
                <span class="field-label">Chapter</span>
                <span class="field-value">
                    {{ $member->psa_chapter_code ?: '-' }}
                </span>
            </td>

            <td width="20%">
                <span class="field-label">Membership Type</span>
                <span class="field-value">
                    {{ $member->psa_mem_type ?: '-' }}
                </span>
            </td>
        </tr>
    </table>

    {{-- DUES TABLE --}}
    <div class="section-heading">
        Outstanding Membership Dues
    </div>

    <table class="dues-table">
        <thead>
            <tr>
                <th class="year">Year</th>
                <th class="description">Description</th>
                <th class="amount">Charges</th>
                <th class="amount">Credit</th>
                <th class="amount">Balance</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($dues as $due)
                <tr>
                    <td class="year">
                        {{ $due->fiscal_year }}
                    </td>

                    <td class="description">
                        Membership Dues
                    </td>

                    <td class="amount">
                        {{ number_format((float) $due->charges, 2) }}
                    </td>

                    <td class="amount">
                        {{ number_format((float) $due->credit, 2) }}
                    </td>

                    <td class="amount balance">
                        {{ number_format((float) $due->balance, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty-state">
                        No outstanding membership dues found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($dues->isNotEmpty())
        <table class="total-box">
            <tr>
                <td class="total-label">
                    TOTAL OUTSTANDING DUES
                </td>

                <td class="total-value">
                    ₱{{ number_format($totalBalance, 2) }}
                </td>
            </tr>
        </table>
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        <div class="footer-rule"></div>

        <div class="notice-title">PAYMENT INSTRUCTIONS</div>

        <div class="notice">
            Please email your deposit slip to
            <strong>psainc_sec@yahoo.com</strong>.
            This is a system-generated notice. If you have already
            settled your outstanding balance, please disregard this
            notice. This Statement of Account reflects your account
            balance as of {{ $statementDate->format('F d, Y') }}.
        </div>

        <div class="notice">
            <strong>
                PAY YOUR DUES ON TIME TO AVAIL OF THE LEGAL AID AND
                DEATH BENEFIT.
            </strong>
            MEMBERS MUST PAY THE NATIONAL SOCIETY'S MEMBERSHIP FEE
            AND OTHER ASSESSMENT FEES FOR THE CURRENT FISCAL YEAR.
            FOR FISCAL YEAR 2026, MEMBERS MUST PAY THE ANNUAL
            MEMBERSHIP DUES ON OR BEFORE SEPTEMBER 30, 2026.
        </div>

        <div class="remit-heading">REMIT TO</div>

        <div class="account-name">
            Account Name: Philippine Society of Anesthesiologists, Inc.
        </div>

        <table class="bank-table">
            <tr>
                <td>
                    <span class="bank-name">
                        BPI - SM North EDSA Branch
                    </span>
                    <br>
                    Account No.:
                    <span class="account-number">
                        4433-1136-03
                    </span>
                </td>

                <td>
                    <span class="bank-name">
                        LANDBANK North Ave. Branch
                    </span>
                    <br>
                    Account No.:
                    <span class="account-number">
                        0711-0635-44
                    </span>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
