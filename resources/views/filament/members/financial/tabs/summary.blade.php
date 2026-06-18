@php
    $balances = $record->ledgerBalances()
    ->orderByDesc('fiscal_year')
    ->get();
@endphp

<div class="overflow-x-auto">

    <table class="w-full text-sm border border-gray-200 dark:border-gray-700">

        <thead class="bg-gray-100 dark:bg-gray-800">

            <tr>

                <th class="p-3 text-left">
                    Fiscal Year
                </th>

                <th class="p-3 text-left">
                    Description
                </th>

                <th class="p-3 text-right">
                    Debit
                </th>

                <th class="p-3 text-right">
                    Credit
                </th>

                <th class="p-3 text-right">
                    Balance
                </th>

                <th class="p-3 text-center">
                    Status
                </th>

            </tr>

        </thead>

        <tbody>

            @foreach ($balances as $balance)

                <tr class="border-t border-gray-200 dark:border-gray-700">

                    <td class="p-3 font-medium">
                        {{ $balance->fiscal_year }}
                    </td>

                    <td class="p-3">
                        {{ $balance->tran_desc }}
                    </td>

                    <td class="p-3 text-right">
                        ₱{{ number_format($balance->dbit, 2) }}
                    </td>

                    <td class="p-3 text-right">
                        ₱{{ number_format($balance->cbit, 2) }}
                    </td>

                    <td class="p-3 text-right font-semibold">
                        ₱{{ number_format($balance->bal, 2) }}
                    </td>

                    <td class="p-3 text-center">

                        @if ($balance->bal > 0)

                            <span class="font-semibold text-red-600">
                                Outstanding
                            </span>

                        @else

                            <span class="font-semibold text-green-600">
                                Paid
                            </span>

                        @endif

                    </td>

                </tr>

            @endforeach

        </tbody>

        <tfoot>

            <tr class="border-t bg-green-50 dark:bg-green-900/20 font-semibold">

                <td colspan="2" class="p-3">
                    Totals
                </td>

                <td class="p-3 text-right">
                    ₱{{ number_format($balances->sum('dbit'), 2) }}
                </td>

                <td class="p-3 text-right">
                    ₱{{ number_format($balances->sum('cbit'), 2) }}
                </td>

                <td class="p-3 text-right">
                    ₱{{ number_format($balances->sum('bal'), 2) }}
                </td>

                <td class="p-3 text-center">

                    @if ($balances->sum('bal') > 0)

                        <span class="font-semibold text-red-600">
                            With Balance
                        </span>

                    @else

                        <span class="font-semibold text-green-600">
                            Fully Paid
                        </span>

                    @endif

                </td>

            </tr>

        </tfoot>

    </table>
</div>
