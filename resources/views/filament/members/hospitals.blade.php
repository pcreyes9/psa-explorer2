@php
    $hospitals = $record->hospitals()
        ->orderByDesc('hosp_primary')
        ->orderBy('hospital')
        ->get();
@endphp

<div class="overflow-x-auto border rounded-xl">

    <table class="w-full text-sm">

        <thead class="bg-gray-50 dark:bg-gray-800">

            <tr>

                <th class="p-3 text-left">
                    Hospital
                </th>

                <th class="p-3 text-left">
                    Designation
                </th>

                <th class="p-3 text-left">
                    Address
                </th>

                <th class="p-3 text-left">
                    Days
                </th>

                <th class="p-3 text-left">
                    Hours
                </th>

                <th class="p-3 text-left">
                    Telephone
                </th>

                <th class="p-3 text-center">
                    Primary
                </th>

            </tr>

        </thead>


        <tbody>

            @forelse ($hospitals as $hospital)

                <tr class="border-t hover:bg-gray-50 dark:hover:bg-gray-800 transition">

                    {{-- Hospital --}}
                    <td class="p-3">

                        <div class="font-semibold">

                            {{ $hospital->hospital ?: '-' }}

                        </div>

                    </td>


                    {{-- Designation --}}
                    <td class="p-3">

                        {{ $hospital->hosp_designation ?: '-' }}

                    </td>


                    {{-- Address --}}
                    <td class="p-3">

                        {{ $hospital->hosp_address ?: '-' }}

                    </td>


                    {{-- Days --}}
                    <td class="p-3 whitespace-nowrap">

                        {{ $hospital->hosp_days ?: '-' }}

                    </td>


                    {{-- Hours --}}
                    <td class="p-3 whitespace-nowrap">

                        {{ $hospital->hosp_hours ?: '-' }}

                    </td>


                    {{-- Telephone --}}
                    <td class="p-3 whitespace-nowrap">

                        {{ $hospital->hosp_tel_no ?: '-' }}

                    </td>


                    {{-- Primary --}}
                    <td class="p-3 text-center">

                        @if ($hospital->hosp_primary)

                            <span
                                class="inline-flex items-center gap-1
                                       rounded-full px-2.5 py-1
                                       text-xs font-medium
                                       bg-success-50
                                       text-success-700"
                            >

                                <x-heroicon-s-star class="w-3.5 h-3.5" />

                                Primary

                            </span>

                        @else

                            <span class="text-gray-400">
                                —
                            </span>

                        @endif

                    </td>

                </tr>

            @empty

                <tr>

                    <td
                        colspan="7"
                        class="p-8 text-center text-gray-500"
                    >

                        <div class="flex flex-col items-center gap-2">

                            <x-heroicon-o-building-office-2
                                class="w-8 h-8 text-gray-400"
                            />

                            <span>
                                No hospital affiliations found.
                            </span>

                        </div>

                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

</div>