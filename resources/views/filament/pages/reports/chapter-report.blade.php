<tbody class="divide-y divide-gray-200 dark:divide-gray-700">
    @forelse ($chapterReport as $row)
        <tr
            @class([
                'bg-gray-100 font-semibold dark:bg-gray-800' =>
                    $row['row_type'] === 'subtotal',
                'hover:bg-gray-50 dark:hover:bg-gray-800' =>
                    $row['row_type'] === 'detail',
            ])
        >
            <td class="px-4 py-3">
                {{ $row['chapter'] }}
            </td>

            <td class="px-4 py-3">
                {{ $row['membership_type'] }}
            </td>

            <td class="px-4 py-3 text-right text-success-600">
                {{ number_format($row['good_standing']) }}
            </td>

            <td class="px-4 py-3 text-right text-danger-600">
                {{ number_format($row['delinquent']) }}
            </td>

            <td class="px-4 py-3 text-right font-semibold">
                {{ number_format($row['total']) }}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                No report data available for the selected date.
            </td>
        </tr>
    @endforelse
</tbody>
