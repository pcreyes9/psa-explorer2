<div class="space-y-6">

    {{-- Payment & Member Information --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <x-filament::section heading="Payment Information">

            <dl class="space-y-2 text-sm">

                <div class="flex justify-between">
                    <dt class="font-medium text-gray-600">Reference No.</dt>
                    <dd>{{ $payment->payment_ref_no }}</dd>
                </div>

                <div class="flex justify-between">
                    <dt class="font-medium text-gray-600">OR Number</dt>
                    <dd>{{ $payment->or_no }}</dd>
                </div>

                <div class="flex justify-between">
                    <dt class="font-medium text-gray-600">Payment Date</dt>
                    <dd>{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</dd>
                </div>

                <div class="flex justify-between">
                    <dt class="font-medium text-gray-600">Payment Type</dt>
                    <dd>{{ $payment->payment_type }}</dd>
                </div>

                <div class="flex justify-between">
                    <dt class="font-medium text-gray-600">Processed By</dt>
                    <dd>{{ $payment->userid }}</dd>
                </div>

            </dl>

        </x-filament::section>

        <x-filament::section heading="Member Information">

            <dl class="space-y-2 text-sm">

                <div class="flex justify-between">
                    <dt class="font-medium text-gray-600">PSA ID</dt>
                    <strong>{{ $payment->member->member_id_no }}</strong>
                </div>

                <div class="flex justify-between">
                    <dt class="font-medium text-gray-600">Name</dt>
                    <strong>{{ $payment->payment_name }}</strong>
                </div>

            </dl>

        </x-filament::section>

    </div>

    {{-- Payment Breakdown --}}
    <x-filament::section heading="Payment Breakdown">

        <div class="overflow-auto max-h-80 rounded-lg border">

            <table class="w-full text-sm">

                <thead class="sticky top-0 bg-gray-100 dark:bg-gray-800 z-10">

                    <tr>

                        <th class="p-3 text-left font-semibold">
                            Description
                        </th>

                        <th class="p-3 text-left font-semibold">
                            Transaction Code
                        </th>

                        <th class="p-3 text-right font-semibold">
                            Amount
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse ($payment->paymentItems as $item)

                        <tr class="border-t">

                            <td class="p-3">
                                {{ $item->item_code }}
                            </td>

                            <td class="p-3">
                                {{ $item->tran_code }}
                            </td>

                            <td class="p-3 text-right font-medium">
                                ₱{{ number_format($item->amount_due, 2) }}
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="3" class="p-6 text-center text-gray-500">

                                No payment items found.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

                <tfoot class="sticky bottom-0 bg-gray-50 dark:bg-gray-900">

                    <tr class="border-t font-bold">

                        <td colspan="2" class="p-3 text-right">
                            TOTAL
                        </td>

                        <td class="p-3 text-right">
                            ₱{{ number_format($payment->payment_total_amt, 2) }}
                        </td>

                    </tr>

                </tfoot>

            </table>

        </div>

    </x-filament::section>

</div>
