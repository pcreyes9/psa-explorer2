@php
    $archives = $record->archivedPayments()
        ->orderByDesc('payment_date')
        ->get();
@endphp

<div class="overflow-x-auto">

    <table class="w-full text-sm border border-gray-200 dark:border-gray-700">

        <thead class="bg-gray-100 dark:bg-gray-800">

            <tr>

                <th class="p-3 text-left">
                    Date
                </th>

                <th class="p-3 text-left">
                    OR Number
                </th>

                <th class="p-3 text-left">
                    Payment Type
                </th>

                <th class="p-3 text-right">
                    Amount
                </th>

                <th class="p-3 text-left">
                    Processed By
                </th>

                <th class="p-3 text-left">
                    Remarks
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse ($archives as $archive)

                <tr class="border-t border-gray-200 dark:border-gray-700">

                    <td class="p-3">
                        {{ $archive->payment_date }}
                    </td>

                    <td class="p-3">
                        {{ $archive->or_no }}
                    </td>

                    <td class="p-3">
                        {{ $archive->payment_type }}
                    </td>

                    <td class="p-3 text-right font-medium">
                        ₱{{ number_format($archive->payment_total_amt, 2) }}
                    </td>

                    <td class="p-3">
                        {{ $archive->userid }}
                    </td>

                    <td class="p-3">
                        {{ $archive->remarks }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td
                        colspan="6"
                        class="p-8 text-center text-gray-500"
                    >
                        No archived payment records found.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

</div>