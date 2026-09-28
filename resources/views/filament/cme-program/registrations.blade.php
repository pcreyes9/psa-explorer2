@php
    use Illuminate\Support\Facades\DB;

    /*
    |--------------------------------------------------------------------------
    | PSA Member Registrations
    | RM / TM / LM
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
    | Non-Member Registrations
    |--------------------------------------------------------------------------
    */
    $nm = DB::table('cme_program_registrationNM')
        ->where('cme_program_code', $record->cme_program_code)
        ->count();


    /*
    |--------------------------------------------------------------------------
    | Total
    |--------------------------------------------------------------------------
    */
    $total = $rm + $tm + $lm + $nm;
@endphp


<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

    {{-- RM --}}
    <div class="rounded-xl border bg-white dark:bg-gray-900 p-5">

        <div class="text-sm text-gray-500">
            Regular Members
        </div>

        <div class="mt-2 text-3xl font-bold">
            {{ $rm }}
        </div>

        <div class="mt-1 text-xs text-gray-400">
            RM
        </div>

    </div>


    {{-- TM --}}
    <div class="rounded-xl border bg-white dark:bg-gray-900 p-5">

        <div class="text-sm text-gray-500">
            Trainee Members
        </div>

        <div class="mt-2 text-3xl font-bold">
            {{ $tm }}
        </div>

        <div class="mt-1 text-xs text-gray-400">
            TM
        </div>

    </div>


    {{-- LM --}}
    <div class="rounded-xl border bg-white dark:bg-gray-900 p-5">

        <div class="text-sm text-gray-500">
            Life Members
        </div>

        <div class="mt-2 text-3xl font-bold">
            {{ $lm }}
        </div>

        <div class="mt-1 text-xs text-gray-400">
            LM
        </div>

    </div>


    {{-- NM --}}
    <div class="rounded-xl border bg-white dark:bg-gray-900 p-5">

        <div class="text-sm text-gray-500">
            Non-Members
        </div>

        <div class="mt-2 text-3xl font-bold">
            {{ $nm }}
        </div>

        <div class="mt-1 text-xs text-gray-400">
            NM
        </div>

    </div>

</div>


{{-- TOTAL --}}
<div class="mt-4 rounded-xl border bg-gray-50 dark:bg-gray-800 p-5">

    <div class="flex items-center justify-between">

        <div>
            <div class="text-sm font-medium text-gray-600 dark:text-gray-300">
                Total Registrations
            </div>

            <div class="mt-1 text-xs text-gray-400">
                RM + TM + LM + NM
            </div>
        </div>

        <div class="text-3xl font-bold">
            {{ $total }}
        </div>

    </div>

</div>