@php
    use Illuminate\Support\Facades\DB;

    /*
    |--------------------------------------------------------------------------
    | RM / TM / LM registrations
    |--------------------------------------------------------------------------
    */
    $memberCounts = $record->registrations()
        ->join(
            'member',
            'member.member_id_no',
            '=',
            'cme_program_registration.member_id_no'
        )
        ->selectRaw('member.psa_mem_type, COUNT(*) as total')
        ->whereIn('member.psa_mem_type', ['RM', 'TM', 'LM'])
        ->groupBy('member.psa_mem_type')
        ->pluck('total', 'member.psa_mem_type');

    $rm = (int) ($memberCounts['RM'] ?? 0);
    $tm = (int) ($memberCounts['TM'] ?? 0);
    $lm = (int) ($memberCounts['LM'] ?? 0);


    /*
    |--------------------------------------------------------------------------
    | NM registrations
    |--------------------------------------------------------------------------
    */
    $nm = DB::table('cme_program_registrationNM')
        ->where(
            'cme_program_code',
            $record->cme_program_code
        )
        ->count();


    /*
    |--------------------------------------------------------------------------
    | Total registrations
    |--------------------------------------------------------------------------
    */
    $total = $rm + $tm + $lm + $nm;
@endphp


<div class="space-y-4">

    {{-- ================================================================
         EXPORT BUTTON
    ================================================================= --}}
    {{-- <div class="flex justify-end">

        <a
            href="{{ route(
                'cme-program.registrations.export',
                $record->cme_program_code
            ) }}"
            class="inline-flex items-center gap-2 px-4 py-2
                   text-sm font-medium
                   text-white
                   bg-success-600
                   hover:bg-success-700
                   rounded-lg
                   shadow-sm
                   transition"
        >

            <x-heroicon-o-arrow-down-tray class="w-5 h-5" />

            Export Excel

        </a>

    </div> --}}


    {{-- ================================================================
         REGISTRATION SUMMARY
    ================================================================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">


        {{-- RM --}}
        <div class="rounded-xl border bg-white dark:bg-gray-900 p-5">

            <div class="flex items-center justify-between">

                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Regular Members
                    </div>

                    <div class="mt-2 text-3xl font-bold">
                        {{ number_format($rm) }}
                    </div>
                </div>

                <div class="flex items-center justify-center
                            w-10 h-10 rounded-lg
                            bg-blue-50 dark:bg-blue-900/30">

                    <x-heroicon-o-user-group
                        class="w-5 h-5 text-blue-600"
                    />

                </div>

            </div>

            <div class="mt-2 text-xs text-gray-400">
                RM
            </div>

        </div>


        {{-- TM --}}
        <div class="rounded-xl border bg-white dark:bg-gray-900 p-5">

            <div class="flex items-center justify-between">

                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Trainee Members
                    </div>

                    <div class="mt-2 text-3xl font-bold">
                        {{ number_format($tm) }}
                    </div>
                </div>

                <div class="flex items-center justify-center
                            w-10 h-10 rounded-lg
                            bg-amber-50 dark:bg-amber-900/30">

                    <x-heroicon-o-academic-cap
                        class="w-5 h-5 text-amber-600"
                    />

                </div>

            </div>

            <div class="mt-2 text-xs text-gray-400">
                TM
            </div>

        </div>


        {{-- LM --}}
        <div class="rounded-xl border bg-white dark:bg-gray-900 p-5">

            <div class="flex items-center justify-between">

                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Life Members
                    </div>

                    <div class="mt-2 text-3xl font-bold">
                        {{ number_format($lm) }}
                    </div>
                </div>

                <div class="flex items-center justify-center
                            w-10 h-10 rounded-lg
                            bg-green-50 dark:bg-green-900/30">

                    <x-heroicon-o-heart
                        class="w-5 h-5 text-green-600"
                    />

                </div>

            </div>

            <div class="mt-2 text-xs text-gray-400">
                LM
            </div>

        </div>


        {{-- NM --}}
        <div class="rounded-xl border bg-white dark:bg-gray-900 p-5">

            <div class="flex items-center justify-between">

                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Non-Members
                    </div>

                    <div class="mt-2 text-3xl font-bold">
                        {{ number_format($nm) }}
                    </div>
                </div>

                <div class="flex items-center justify-center
                            w-10 h-10 rounded-lg
                            bg-purple-50 dark:bg-purple-900/30">

                    <x-heroicon-o-users
                        class="w-5 h-5 text-purple-600"
                    />

                </div>

            </div>

            <div class="mt-2 text-xs text-gray-400">
                NM
            </div>

        </div>

    </div>


    {{-- ================================================================
         TOTAL REGISTRATIONS
    ================================================================= --}}
    <div class="rounded-xl border bg-gray-50 dark:bg-gray-800 p-5">

        <div class="flex items-center justify-between">

            <div>

                <div class="text-sm font-medium text-gray-600
                            dark:text-gray-300">

                    Total Registrations

                </div>

                <div class="mt-1 text-xs text-gray-400">

                    RM + TM + LM + NM

                </div>

            </div>

            <div class="text-3xl font-bold">

                {{ number_format($total) }}

            </div>

        </div>

    </div>

</div>
