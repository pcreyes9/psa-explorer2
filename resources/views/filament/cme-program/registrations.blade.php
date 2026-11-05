
@php
    use Illuminate\Support\Facades\DB;
    use Carbon\Carbon;

    /*
    |--------------------------------------------------------------------------
    | REGISTRATION SUMMARY: RM / TM / LM
    |--------------------------------------------------------------------------
    */

    $memberCounts = $record->registrations()
        ->join(
            'member',
            'member.member_id_no',
            '=',
            'cme_program_registration.member_id_no'
        )
        ->selectRaw("
            UPPER(LTRIM(RTRIM(member.psa_mem_type))) AS membership_type,
            COUNT(*) AS total
        ")
        ->whereRaw("
            UPPER(LTRIM(RTRIM(member.psa_mem_type)))
            IN ('RM', 'TM', 'LM')
        ")
        ->groupByRaw("
            UPPER(LTRIM(RTRIM(member.psa_mem_type)))
        ")
        ->pluck('total', 'membership_type');

    $rm = (int) ($memberCounts['RM'] ?? 0);
    $tm = (int) ($memberCounts['TM'] ?? 0);
    $lm = (int) ($memberCounts['LM'] ?? 0);


    /*
    |--------------------------------------------------------------------------
    | REGISTRATION SUMMARY: NM
    |--------------------------------------------------------------------------
    */

    $nm = DB::table('cme_program_registrationNM')
        ->where('cme_program_code', $record->cme_program_code)
        ->count();


    /*
    |--------------------------------------------------------------------------
    | TOTAL REGISTRATIONS
    |--------------------------------------------------------------------------
    */

    $total = $rm + $tm + $lm + $nm;


    /*
    |--------------------------------------------------------------------------
    | GET RM / TM / LM REGISTRATIONS
    |--------------------------------------------------------------------------
    */

    $memberRegistrations = DB::table('cme_program_registration as r')
        ->join(
            'member as m',
            'm.member_id_no',
            '=',
            'r.member_id_no'
        )
        ->where('r.cme_program_code', $record->cme_program_code)
        ->whereRaw("
            UPPER(LTRIM(RTRIM(m.psa_mem_type)))
            IN ('RM', 'TM', 'LM')
        ")
        ->select([
            'r.member_id_no',
            'm.mem_last_name',
            'm.mem_first_name',
            'm.mem_middle_name',
            'm.psa_mem_type',
            'r.cme_reg_date',
            'r.payment_ref_no',
        ])
        ->get()
        ->map(function ($registration) {

            $middleName = trim($registration->mem_middle_name ?? '');

            return [
                'member_id_no' => $registration->member_id_no,

                'member_name' => trim(
                    ($registration->mem_last_name ?? '') . ', ' .
                    ($registration->mem_first_name ?? '') . ' ' .
                    $middleName
                ),

                'membership_type' => strtoupper(
                    trim($registration->psa_mem_type ?? '')
                ),

                'registration_date' => $registration->cme_reg_date,

                'payment_ref_no' => $registration->payment_ref_no,
            ];
        });


    /*
    |--------------------------------------------------------------------------
    | GET NM REGISTRATIONS
    |--------------------------------------------------------------------------
    */

    $nonMemberRegistrations = DB::table('cme_program_registrationNM')
        ->where('cme_program_code', $record->cme_program_code)
        ->select([
            'nm_id_no',
            'nm_last_name',
            'nm_first_name',
            'nm_middle_name',
            'cme_reg_date',
            'payment_ref_no',
        ])
        ->get()
        ->map(function ($registration) {

            $middleName = trim($registration->nm_middle_name ?? '');

            return [
                'member_id_no' => $registration->nm_id_no,

                'member_name' => trim(
                    ($registration->nm_last_name ?? '') . ', ' .
                    ($registration->nm_first_name ?? '') . ' ' .
                    $middleName
                ),

                'membership_type' => 'NM',

                'registration_date' => $registration->cme_reg_date,

                'payment_ref_no' => $registration->payment_ref_no,
            ];
        });


    /*
    |--------------------------------------------------------------------------
    | COMBINE REGISTRATIONS
    |--------------------------------------------------------------------------
    */

    $registrations = $memberRegistrations
        ->concat($nonMemberRegistrations)
        ->sortByDesc('registration_date')
        ->values();

@endphp


<style>
    [x-cloak] {
        display: none !important;
    }
</style>


<div class="space-y-4">

    {{-- ================================================================
         REGISTRATION SUMMARY CARDS
    ================================================================= --}}

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

        {{-- REGULAR MEMBERS --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">

            <div class="flex items-center justify-between">

                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Regular Members
                    </div>

                    <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($rm) }}
                    </div>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                    <x-heroicon-o-user-group class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                </div>

            </div>

            <div class="mt-2 text-xs text-gray-400">
                RM
            </div>

        </div>


        {{-- TRAINEE MEMBERS --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">

            <div class="flex items-center justify-between">

                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Trainee Members
                    </div>

                    <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($tm) }}
                    </div>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                    <x-heroicon-o-academic-cap class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                </div>

            </div>

            <div class="mt-2 text-xs text-gray-400">
                TM
            </div>

        </div>


        {{-- LIFE MEMBERS --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">

            <div class="flex items-center justify-between">

                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Life Members
                    </div>

                    <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($lm) }}
                    </div>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                    <x-heroicon-o-heart class="h-5 w-5 text-green-600 dark:text-green-400" />
                </div>

            </div>

            <div class="mt-2 text-xs text-gray-400">
                LM
            </div>

        </div>


        {{-- NON-MEMBERS --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">

            <div class="flex items-center justify-between">

                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Non-Members
                    </div>

                    <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($nm) }}
                    </div>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/30">
                    <x-heroicon-o-users class="h-5 w-5 text-purple-600 dark:text-purple-400" />
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

    <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-800">

        <div class="flex items-center justify-between">

            <div>
                <div class="text-sm font-medium text-gray-600 dark:text-gray-300">
                    Total Registrations
                </div>

                <div class="mt-1 text-xs text-gray-400">
                    RM + TM + LM + NM
                </div>
            </div>

            <div class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ number_format($total) }}
            </div>

        </div>

    </div>


    {{-- ================================================================
         SEARCH REGISTRATIONS
    ================================================================= --}}

    <div
        x-data="{
            search: '',

            get filteredRows() {
                if (!this.search.trim()) {
                    return [];
                }

                const term = this.search.toLowerCase().trim();

                return Array.from(
                    this.$refs.rows.querySelectorAll('[data-registration-row]')
                ).filter(row =>
                    row.innerText.toLowerCase().includes(term)
                );
            }
        }"
        class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900"
    >

        {{-- SEARCH HEADER --}}
        <div class="border-b border-gray-200 p-5 dark:border-gray-700">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        Search Registrations
                    </h3>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Find a registered member or non-member.
                    </p>
                </div>


                {{-- SEARCH INPUT --}}
                <div class="w-full sm:max-w-sm">

                    <div class="relative">

                        <x-heroicon-o-magnifying-glass
                            class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"
                        />

                        <input
                            type="text"
                            x-model.debounce.250ms="search"
                            placeholder="Search PSA ID, name, or reference..."
                            class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-10 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />

                        <button
                            type="button"
                            x-show="search.length > 0"
                            x-cloak
                            @click="search = ''"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            title="Clear search"
                        >
                            <x-heroicon-m-x-mark class="h-4 w-4" />
                        </button>

                    </div>

                </div>

            </div>

        </div>


        {{-- ============================================================
             RESULTS TABLE
        ============================================================= --}}

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">

                {{-- TABLE HEADER --}}
                <thead class="bg-gray-50 dark:bg-gray-800">

                    <tr>

                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            #
                        </th>

                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            PSA ID
                        </th>

                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Member Name
                        </th>

                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Type
                        </th>

                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Registration Date
                        </th>

                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Payment Reference
                        </th>

                    </tr>

                </thead>


                {{-- TABLE BODY --}}
                <tbody
                    x-ref="rows"
                    class="divide-y divide-gray-100 dark:divide-gray-800"
                >

                    @foreach ($registrations as $index => $registration)

                        <tr
                            data-registration-row
                            x-show="
                                search.trim() &&
                                $el.innerText.toLowerCase().includes(search.toLowerCase().trim())
                            "
                            x-cloak
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/60"
                        >

                            {{-- NUMBER --}}
                            <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-500">
                                {{ $index + 1 }}
                            </td>


                            {{-- PSA ID --}}
                            <td class="whitespace-nowrap px-5 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                {{ $registration['member_id_no'] }}
                            </td>


                            {{-- MEMBER NAME --}}
                            <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-700 dark:text-gray-300">
                                {{ $registration['member_name'] }}
                            </td>


                            {{-- MEMBERSHIP TYPE --}}
                            <td class="whitespace-nowrap px-5 py-3">

                                @php
                                    $typeClasses = match ($registration['membership_type']) {
                                        'RM' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                        'TM' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                        'LM' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                        'NM' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                    };
                                @endphp

                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $typeClasses }}">
                                    {{ $registration['membership_type'] }}
                                </span>

                            </td>


                            {{-- REGISTRATION DATE --}}
                            <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-500 dark:text-gray-400">

                                {{ $registration['registration_date']
                                    ? Carbon::parse($registration['registration_date'])->format('M d, Y')
                                    : '-' }}

                            </td>


                            {{-- PAYMENT REFERENCE --}}
                            <td class="whitespace-nowrap px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $registration['payment_ref_no'] ?: '-' }}
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>


            {{-- ========================================================
                 INITIAL SEARCH MESSAGE
            ========================================================= --}}

            <div
                x-show="!search.trim()"
                class="px-5 py-12 text-center"
            >

                <x-heroicon-o-magnifying-glass class="mx-auto h-9 w-9 text-gray-400" />

                <p class="mt-3 text-sm font-medium text-gray-600 dark:text-gray-300">
                    Search for a registration
                </p>

                <p class="mt-1 text-xs text-gray-400">
                    Enter a PSA ID, member name, membership type, or payment reference number.
                </p>

            </div>


            {{-- ========================================================
                 NO SEARCH RESULTS
            ========================================================= --}}

            <div
                x-show="search.trim() && filteredRows.length === 0"
                x-cloak
                class="px-5 py-12 text-center"
            >

                <x-heroicon-o-exclamation-circle class="mx-auto h-9 w-9 text-gray-400" />

                <p class="mt-3 text-sm font-medium text-gray-600 dark:text-gray-300">
                    No matching registrations found.
                </p>

                <p class="mt-1 text-xs text-gray-400">
                    Please check your search keyword and try again.
                </p>

            </div>

        </div>


        {{-- ============================================================
             SEARCH RESULTS FOOTER
        ============================================================= --}}

        <div
            x-show="search.trim() && filteredRows.length > 0"
            x-cloak
            class="border-t border-gray-200 bg-gray-50 px-5 py-3 dark:border-gray-700 dark:bg-gray-800"
        >

            <div class="text-xs text-gray-500 dark:text-gray-400">

                Showing
                <span
                    class="font-semibold text-gray-900 dark:text-white"
                    x-text="filteredRows.length"
                ></span>

                matching registration(s).

            </div>

        </div>

    </div>

</div>