@php
    $cmeRecords = $record->cmeRegistrations()
        ->with('cmeProgram')
        ->get()
        ->sortByDesc(
            fn ($registration) =>
                $registration->cmeProgram?->cme_startdate
        );
@endphp

<div class="overflow-x-auto border rounded-xl">

    <table class="w-full text-sm">

        <thead class="bg-gray-50 dark:bg-gray-800">

            <tr>

                <th class="p-3 text-left">
                    Date
                </th>

                <th class="p-3 text-left">
                    CME Program
                </th>

                <th class="p-3 text-left">
                    Topic
                </th>

                <th class="p-3 text-left">
                    Type
                </th>

                <th class="p-3 text-left">
                    Payment Ref. No.
                </th>

                <th class="p-3 text-left">
                    Venue
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse ($cmeRecords as $registration)

                @php
                    $cme = $registration->cmeProgram;
                @endphp

                @if ($cme)

                    <tr
                        class="border-t cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                        onclick="window.location.href='{{ route('filament.admin.resources.c-m-e-programs.view', ['record' => $cme->cme_program_code]) }}'"
                    >

                        {{-- Date --}}
                        <td class="p-3 whitespace-nowrap">

                            @if ($cme->cme_startdate)

                                {{ $cme->cme_startdate->format('M d, Y') }}

                                @if (
                                    $cme->cme_enddate &&
                                    $cme->cme_enddate->ne($cme->cme_startdate)
                                )
                                    -
                                    {{ $cme->cme_enddate->format('M d, Y') }}
                                @endif

                            @else
                                -
                            @endif

                        </td>

                        {{-- CME Program --}}
                        <td class="p-3 font-semibold">
                            {{ $cme->cme_title ?: '-' }}
                        </td>

                        {{-- Topic --}}
                        <td class="p-3">
                            {{ $cme->cme_topic ?: '-' }}
                        </td>

                        {{-- Type --}}
                        <td class="p-3">
                            {{ $cme->cme_program_type ?: '-' }}
                        </td>

                        {{-- Payment Reference --}}
                        <td class="p-3">
                            {{ $registration->payment_ref_no ?: '-' }}
                        </td>

                        {{-- Venue --}}
                        <td class="p-3">
                            {{ $cme->cme_venue ?: '-' }}
                        </td>

                    </tr>

                @endif

            @empty

                <tr>
                    <td
                        colspan="6"
                        class="p-8 text-center text-gray-500"
                    >
                        <div class="flex flex-col items-center gap-2">

                            <x-heroicon-o-academic-cap
                                class="w-8 h-8 text-gray-400"
                            />

                            <span>
                                No CME registration records found.
                            </span>

                        </div>
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</div>