<div>

    <div class="flex justify-end">
        <x-filament::button
            wire:click="openModal"
            icon="heroicon-o-user-plus"
        >
            Add Member
        </x-filament::button>
    </div>

    @if ($showModal)
        <div
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6"
            wire:key="add-member-modal-{{ $programCode }}"
        >

            <div
                class="absolute inset-0 bg-gray-950/60 backdrop-blur-sm"
                wire:click="closeModal"
            ></div>

            <div
                class="relative z-10 flex w-full max-w-lg max-h-[90vh] flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-gray-950/10 dark:bg-gray-900 dark:ring-white/10"
                wire:click.stop
            >

                <div class="flex items-start justify-between border-b border-gray-200 px-6 py-5 dark:border-gray-700">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-950 dark:text-white">
                            Add Member to Registration
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Register an existing PSA member in this CME program.
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="closeModal"
                        class="ml-4 rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-white"
                        aria-label="Close modal"
                    >
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div class="flex-1 space-y-5 overflow-y-auto px-6 py-6">

                    @if (!$selectedMemberId)

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Search PSA Member
                            </label>

                            <form wire:submit.prevent="searchMembers">
                                <div class="flex gap-2">
                                    <input
                                        type="text"
                                        wire:model="search"
                                        placeholder="Enter PSA ID or member name..."
                                        autocomplete="off"
                                        class="block w-full rounded-lg border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    />

                                    <x-filament::button
                                        type="submit"
                                        icon="heroicon-o-magnifying-glass"
                                    >
                                        Search
                                    </x-filament::button>
                                </div>
                            </form>

                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                Press Enter or click Search to find a member.
                                Only RM, TM, and LM members are eligible.
                            </p>
                        </div>

                        @if (strlen(trim($search)) >= 2)
                            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">

                                @forelse ($this->members as $member)
                                    <button
                                        type="button"
                                        wire:key="member-{{ $member->member_id_no }}"
                                        wire:click="selectMember('{{ $member->member_id_no }}')"
                                        class="flex w-full items-center justify-between gap-4 border-b border-gray-100 px-4 py-3 text-left transition last:border-b-0 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                                    >
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ $member->mem_last_name }},
                                                {{ $member->mem_first_name }}
                                                {{ $member->mem_middle_name }}
                                            </p>

                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                PSA ID: {{ $member->member_id_no }}
                                            </p>
                                        </div>

                                        <div class="flex shrink-0 items-center gap-2">
                                            <span class="rounded-md bg-primary-50 px-2.5 py-1 text-xs font-semibold text-primary-700 dark:bg-primary-500/10 dark:text-primary-400">
                                                {{ $member->psa_mem_type }}
                                            </span>

                                            <x-heroicon-o-chevron-right class="h-4 w-4 text-gray-400" />
                                        </div>
                                    </button>
                                @empty
                                    <div class="px-4 py-8 text-center">
                                        <x-heroicon-o-user-minus class="mx-auto h-8 w-8 text-gray-400" />

                                        <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            No eligible members found
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            Try another PSA ID or member name.
                                        </p>
                                    </div>
                                @endforelse

                            </div>
                        @else
                            <div class="rounded-xl border border-dashed border-gray-300 px-4 py-8 text-center dark:border-gray-700">
                                <x-heroicon-o-magnifying-glass class="mx-auto h-8 w-8 text-gray-400" />

                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Search using at least two characters.
                                </p>
                            </div>
                        @endif

                    @else

                        @if ($selectedMemberData)
                            <div class="rounded-xl border border-primary-200 bg-primary-50/70 p-4 dark:border-primary-500/30 dark:bg-primary-500/10">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex min-w-0 items-start gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-700 dark:bg-primary-500/20 dark:text-primary-400">
                                            <x-heroicon-o-user class="h-5 w-5" />
                                        </div>

                                        <div class="min-w-0">
                                            <p class="text-xs font-medium uppercase tracking-wide text-primary-700 dark:text-primary-400">
                                                Selected Member
                                            </p>

                                            <p class="mt-1 text-base font-semibold text-gray-950 dark:text-white">
                                                {{ $selectedMemberData['mem_last_name'] }},
                                                {{ $selectedMemberData['mem_first_name'] }}
                                                {{ $selectedMemberData['mem_middle_name'] }}
                                            </p>

                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                PSA ID: {{ $selectedMemberData['member_id_no'] }}
                                                <span class="mx-1">·</span>
                                                {{ $selectedMemberData['psa_mem_type'] }}
                                            </p>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        wire:click="$set('selectedMemberId', null)"
                                        class="shrink-0 text-sm font-medium text-primary-700 hover:text-primary-900 dark:text-primary-400"
                                    >
                                        Change
                                    </button>
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Payment Reference Number
                            </label>

                            <input
                                type="text"
                                wire:model="paymentRefNo"
                                maxlength="20"
                                class="block w-full rounded-lg border-gray-300 bg-white px-4 py-3 text-sm font-semibold tracking-wide text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />

                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                Automatically generated based on the CME program. You may edit this number before registration.
                            </p>

                            @error('paymentRefNo')
                                <p class="mt-2 text-sm text-danger-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    @endif

                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row sm:justify-end dark:border-gray-700 dark:bg-gray-900">
                    <x-filament::button
                        color="gray"
                        outlined
                        wire:click="closeModal"
                    >
                        Cancel
                    </x-filament::button>

                    @if ($selectedMemberId)
                        <x-filament::button
                            wire:click="save"
                            icon="heroicon-o-check"
                            wire:loading.attr="disabled"
                            wire:target="save"
                        >
                            <span wire:loading.remove wire:target="save">
                                Register Member
                            </span>

                            <span wire:loading wire:target="save">
                                Registering...
                            </span>
                        </x-filament::button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
