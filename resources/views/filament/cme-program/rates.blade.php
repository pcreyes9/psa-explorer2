@php
    $rates = $record->rates()
        ->orderByRaw("
            CASE charge_code
                WHEN 'RM' THEN 1
                WHEN 'LM' THEN 2
                WHEN 'TM' THEN 3
                WHEN 'NM' THEN 4
                WHEN 'EM' THEN 5
                ELSE 6
            END
        ")
        ->get();

    $chargeNames = [
        'RM' => 'Regular Member',
        'LM' => 'Life Member',
        'TM' => 'Trainee Member',
        'NM' => 'Non-Member',
        'EM' => 'Employee',
        'HM' => 'Honorary Member',
        'AM' => 'Associate Member',
    ];
@endphp

<div class="overflow-x-auto border rounded-xl">

    <table class="w-full text-sm">

        <thead class="bg-gray-50 dark:bg-gray-800">

            <tr>

                <th class="p-3 text-left">
                    Membership Type
                </th>

                <th class="p-3 text-right">
                    Full Rate
                </th>

                <th class="p-3 text-right">
                    Daily Rate
                </th>

                <th class="p-3 text-right">
                    Pre-Registration
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse ($rates as $rate)

                <tr class="border-t hover:bg-gray-50 dark:hover:bg-gray-800">

                    <td class="p-3 font-semibold">
                        {{ $chargeNames[$rate->charge_code] ?? $rate->charge_code }}
                    </td>

                    <td class="p-3 text-right">
                        ₱{{ number_format($rate->fullrate, 2) }}
                    </td>

                    <td class="p-3 text-right">
                        ₱{{ number_format($rate->dailyrate, 2) }}
                    </td>

                    <td class="p-3 text-right">
                        ₱{{ number_format($rate->prerate, 2) }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td
                        colspan="4"
                        class="p-8 text-center text-gray-500"
                    >

                        <div class="flex flex-col items-center gap-2">

                            <x-heroicon-o-banknotes
                                class="w-8 h-8 text-gray-400"
                            />

                            <span>
                                No registration rates found.
                            </span>

                        </div>

                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

</div>