<x-filament-panels::page>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Member Information --}}
        <x-filament::section heading="Member Information">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                {{-- Member ID --}}
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Member ID
                    </div>

                    <div class="font-semibold">
                        {{ $this->member->member_id_no }}
                    </div>
                </div>

                {{-- Member Name --}}
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Member Name
                    </div>

                    <div class="font-semibold">
                        {{ $this->member->mem_last_name }},
                        {{ $this->member->mem_first_name }}
                        {{ $this->member->mem_middle_name }}
                    </div>
                </div>

                {{-- Membership Status --}}
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Membership Status
                    </div>

                    <div class="font-semibold">
                        {{ $this->member->psa_mem_stat }}
                    </div>
                </div>

                {{-- Membership Type --}}
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Membership Type
                    </div>

                    <div class="font-semibold">
                        {{ $this->member->membershipType?->Memtype ?? '-' }}
                    </div>
                </div>

                {{-- Chapter --}}
                <div class="col-span-1 sm:col-span-2">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Chapter
                    </div>

                    <div class="font-semibold">
                        {{ $this->member->chapter?->psa_chapter_desc ?? '-' }}
                    </div>
                </div>

            </div>

        </x-filament::section>


        {{-- Certificate Details --}}
        <x-filament::section heading="Certificate Details">

            {{-- Purpose --}}
            <div>

                <label
                    for="purpose"
                    class="block text-sm font-medium mb-2"
                >
                    Purpose
                </label>

                <select
                    id="purpose"
                    wire:model.live="purpose"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800"
                >

                    <option value="">
                        -- Select Purpose --
                    </option>

                    <option value="PBA Written Exam">
                        PBA Written Exam
                    </option>

                    <option value="PBA Oral Exam">
                        PBA Oral Exam
                    </option>

                    <option value="PhilHealth Purposes">
                        PhilHealth Purposes
                    </option>

                    <option value="PhilHealth Renewal">
                        PhilHealth Renewal
                    </option>

                    <option value="PhilHealth Accreditation Renewal">
                        PhilHealth Accreditation Renewal
                    </option>

                    <option value="Whatever purpose it may serve her best">
                        Whatever purpose it may serve her best
                    </option>

                    <option value="Whatever purpose it may serve him best">
                        Whatever purpose it may serve him best
                    </option>

                    <option value="custom">
                        Custom Purpose
                    </option>

                </select>

                @error('purpose')
                    <p class="mt-1 text-sm text-danger-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Custom Purpose --}}
            @if ($purpose === 'custom')

                <div class="mt-5">

                    {{-- <label
                        for="customPurpose"
                        class="block text-sm font-medium mb-2"
                    >
                        Custom Purpose
                    </label> --}}

                    <textarea
                        id="customPurpose"
                        wire:model.live="customPurpose"
                        rows="4"
                        maxlength="500"
                        placeholder="Enter the purpose of the certificate..."
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800"
                    ></textarea>

                    @error('customPurpose')
                        <p class="mt-1 text-sm text-danger-600">
                            {{ $message }}
                        </p>
                    @enderror

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Maximum 500 characters.
                    </p>

                </div>

            @endif


            {{-- Generate Button --}}
            <div class="mt-6">

                <x-filament::button
                    color="success"
                    wire:click="generate"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="generate">
                        Generate Certificate
                    </span>

                    <span wire:loading wire:target="generate">
                        Generating...
                    </span>
                </x-filament::button>

            </div>

        </x-filament::section>

    </div>

</x-filament-panels::page>