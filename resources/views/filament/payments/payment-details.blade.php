@php
    $items = $payment->paymentItems;
    // dd($items);
@endphp

<div class="space-y-6">

    <div class="grid grid-cols-2 gap-6">

        <div>

            <h3 class="font-semibold mb-2">
                Payment Information
            </h3>

            <table class="w-full text-sm">

                <tr>
                    <td class="font-medium w-40">Reference No.</td>
                    <td>{{ $payment->payment_ref_no }}</td>
                </tr>

                <tr>
                    <td class="font-medium">OR Number</td>
                    <td>{{ $payment->or_no }}</td>
                </tr>

                <tr>
                    <td class="font-medium">Payment Date</td>
                    <td>{{ $payment->payment_date }}</td>
                </tr>

                <tr>
                    <td class="font-medium">Payment Type</td>
                    <td>{{ $payment->payment_type }}</td>
                </tr>

                <tr>
                    <td class="font-medium">Processed By</td>
                    <td>{{ $payment->userid }}</td>
                </tr>

            </table>

        </div>

        <div>

            <h3 class="font-semibold mb-2">
                Member Information
            </h3>

            <table class="w-full text-sm">

                <tr>
                    <td class="font-medium w-40">PSA ID</td>
                    <td>{{ $payment->member->member_id_no }}</td>
                </tr>

                <tr>
                    <td class="font-medium">Member</td>
                    <td>{{ $payment->payment_name }}</td>
                </tr>

            </table>

        </div>

    </div>

    <div>

        <h3 class="font-semibold mb-3">
            Payment Breakdown
        </h3>

        <table class="w-full text-sm border rounded-lg">

            <thead>

                <tr class="bg-gray-100">

                    <th class="text-left p-2">Description</th>

                    <th class="text-left p-2">Transaction Code</th>

                    <th class="text-right p-2">Amount</th>

                </tr>

            </thead>

            <tbody>

                @foreach($items as $item)

                    <tr>

                        <td class="p-2">
                            {{ $item->item_code }}
                        </td>

                        <td class="p-2">
                            {{ $item->tran_code }}
                        </td>

                        <td class="text-right p-2">
                            ₱{{ number_format($item->amount_due,2) }}
                        </td>

                    </tr>

                @endforeach

            </tbody>

            <tfoot>

                <tr class="font-bold">
                    <td class="p-2 text-right">
                      
                    </td>

                    <td class="p-2 text-right">
                        TOTAL
                    </td>

                    <td class="text-right p-2">
                        ₱{{ number_format($payment->payment_total_amt,2) }}
                    </td>

                </tr>

            </tfoot>

        </table>

    </div>

</div>