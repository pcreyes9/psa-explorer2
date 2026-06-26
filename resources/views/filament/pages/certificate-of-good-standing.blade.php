<x-filament-panels::page>

    <x-filament::section heading="Member Information">

        <div class="grid grid-cols-2 gap-6">

            <div>
                <div class="text-sm text-gray-500">
                    Member ID
                </div>

                <div class="font-semibold">
                    {{ $this->member->member_id_no }}
                </div>
            </div>

            <div>
                <div class="text-sm text-gray-500">
                    Membership Status
                </div>

                <div class="font-semibold">
                    {{ $this->member->psa_mem_stat }}
                </div>
            </div>

            <div>
                <div class="text-sm text-gray-500">
                    Member Name
                </div>

                <div class="font-semibold">
                    {{ $this->member->mem_last_name }},
                    {{ $this->member->mem_first_name }}
                    {{ $this->member->mem_middle_name }}
                </div>
            </div>

            <div>
                <div class="text-sm text-gray-500">
                    Membership Type
                </div>

                <div class="font-semibold">
                    {{ $this->member->membershipType?->Memtype }}
                </div>
            </div>

            <div class="col-span-2">
                <div class="text-sm text-gray-500">
                    Chapter
                </div>

                <div class="font-semibold">
                    {{ $this->member->chapter?->psa_chapter_desc }}
                </div>
            </div>

        </div>

    </x-filament::section>

    <x-filament::section
        heading="Certificate Details"
        class="">

        <div>
        <label class="block text-sm font-medium mb-2">
            Purpose
        </label>

        <select
            wire:model.live="purpose"
            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800"
        >
            <option value="">-- Select Purpose --</option>

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
        </select>
    </div>

    </x-filament::section>

    <div class="mt-6">

        <x-filament::button
            color="success"
            wire:click="generate">

            Generate Certificate

        </x-filament::button>

    </div>

</x-filament-panels::page>
