
<div class="space-y-5">

    {{-- SUMMARY --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

        <div class="rounded-xl bg-success-50 dark:bg-success-400/10 p-4">
            <div class="text-2xl font-bold text-success-600">
                {{ $preview['summary']['eligible'] }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-300">
                Eligible
            </div>
        </div>

        <div class="rounded-xl bg-warning-50 dark:bg-warning-400/10 p-4">
            <div class="text-2xl font-bold text-warning-600">
                {{ $preview['summary']['already_registered'] }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-300">
                Already Registered
            </div>
        </div>

        <div class="rounded-xl bg-danger-50 dark:bg-danger-400/10 p-4">
            <div class="text-2xl font-bold text-danger-600">
                {{ $preview['summary']['invalid'] }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-300">
                Invalid / Ineligible
            </div>
        </div>

        <div class="rounded-xl bg-gray-100 dark:bg-gray-800 p-4">
            <div class="text-2xl font-bold text-gray-700 dark:text-gray-200">
                {{ $preview['summary']['duplicate_input'] }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-300">
                Duplicate IDs
            </div>
        </div>

    </div>

    {{-- ELIGIBLE MEMBERS --}}
    <div class="space-y-2">

        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-gray-900 dark:text-white">
                Eligible Members
            </h3>

            <span class="text-sm text-success-600 font-medium">
                {{ count($preview['eligible']) }} members
            </span>
        </div>

        @if (count($preview['eligible']) > 0)

            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">

                <table class="w-full text-sm">

                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left">PSA ID</th>
                            <th class="px-4 py-3 text-left">Member Name</th>
                            <th class="px-4 py-3 text-center">Type</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">

                        @foreach ($preview['eligible'] as $member)

                            <tr>
                                <td class="px-4 py-3">
                                    {{ $member['member_id_no'] }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $member['member_name'] }}
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex rounded-md bg-primary-50 dark:bg-primary-400/10 px-2 py-1 text-xs font-medium text-primary-600">
                                        {{ $member['membership_type'] }}
                                    </span>
                                </td>
                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4 text-center text-sm text-gray-500">
                No eligible members found.
            </div>

        @endif

    </div>

    {{-- ALREADY REGISTERED --}}
    @if (count($preview['already_registered']) > 0)

        <details class="rounded-lg border border-warning-200 dark:border-warning-800 p-3">

            <summary class="cursor-pointer font-medium text-warning-600">
                Already Registered
                ({{ count($preview['already_registered']) }})
            </summary>

            <div class="mt-3 overflow-x-auto">

                <table class="w-full text-sm">

                    <thead>
                        <tr>
                            <th class="px-3 py-2 text-left">PSA ID</th>
                            <th class="px-3 py-2 text-left">Member Name</th>
                            <th class="px-3 py-2 text-center">Type</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($preview['already_registered'] as $member)

                            <tr>
                                <td class="px-3 py-2">
                                    {{ $member['member_id_no'] }}
                                </td>

                                <td class="px-3 py-2">
                                    {{ $member['member_name'] }}
                                </td>

                                <td class="px-3 py-2 text-center">
                                    {{ $member['membership_type'] }}
                                </td>
                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </details>

    @endif

    {{-- INVALID PSA IDS --}}
    @if (count($preview['invalid']) > 0)

        <details class="rounded-lg border border-danger-200 dark:border-danger-800 p-3">

            <summary class="cursor-pointer font-medium text-danger-600">
                Invalid / Ineligible PSA IDs
                ({{ count($preview['invalid']) }})
            </summary>

            <div class="mt-3 flex flex-wrap gap-2">

                @foreach ($preview['invalid'] as $id)

                    <span class="rounded-md bg-danger-50 dark:bg-danger-400/10 px-2 py-1 text-xs text-danger-600">
                        {{ $id }}
                    </span>

                @endforeach

            </div>

        </details>

    @endif

    {{-- DUPLICATE INPUT --}}
    @if ($preview['summary']['duplicate_input'] > 0)

        <div class="text-sm text-gray-500">
            {{ $preview['summary']['duplicate_input'] }}
            duplicate PSA ID(s) in the submitted list were automatically removed.
        </div>

    @endif

    {{-- CONFIRMATION MESSAGE --}}
    @if ($preview['summary']['eligible'] > 0)

        <div class="rounded-lg border border-success-200 dark:border-success-800 p-3 text-sm">
            <strong>
                {{ $preview['summary']['eligible'] }}
                member(s) are ready for registration.
            </strong>

            <p class="mt-1 text-gray-600 dark:text-gray-300">
                Only eligible members who are not yet registered will be processed.
                Payment reference numbers will be generated automatically.
            </p>
        </div>

    @else

        <div class="rounded-lg border border-warning-200 dark:border-warning-800 p-3 text-sm text-warning-700 dark:text-warning-400">
            There are no new eligible members to register.
        </div>

    @endif

</div>
