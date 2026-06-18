<x-filament::section>
    <div class="flex items-start gap-10">

        {{-- Profile Photo --}}
        <div class="shrink-0">
            <img
                src="{{ $record->photo_url }}"
                alt="{{ $record->mem_last_name }}"
                class="h-40 w-40 rounded-full object-cover shrink-0"
        >
        </div>

        {{-- Member Details --}}
        <div class="flex-1 pt-2">

            <h2 class="text-3xl font-bold tracking-tight">
                {{ strtoupper($record->mem_first_name . ' ' . $record->mem_last_name) }}
            </h2>

            <p class="mt-2 text-sm text-gray-500">
                PSA ID #{{ $record->member_id_no }}
            </p>

            {{-- Membership Badges --}}
            <div class="mt-5 flex flex-wrap gap-2">

                <x-filament::badge color="success">
                    {{ $record->psa_mem_stat }}
                </x-filament::badge>

                <x-filament::badge color="primary">
                    {{ $record->membershipType?->Memtype ?? $record->psa_mem_type }}
                </x-filament::badge>

                <x-filament::badge color="gray">
                    {{ $record->chapter?->psa_chapter_desc ?? $record->psa_chapter_code }}
                </x-filament::badge>

            </div>

            {{-- Quick Information --}}
            <div class="mt-8 grid grid-cols-2 gap-x-16 gap-y-5">

                <div>
                    <div class="text-sm text-gray-500">
                        PRC No.
                    </div>

                    <div class="text-lg font-semibold">
                        {{ $record->mem_prc_no ?: '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">
                        PMA ID
                    </div>

                    <div class="text-lg font-semibold">
                        {{ $record->mem_pma_id_no ?: '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">
                        Date Joined
                    </div>

                    <div class="text-lg font-semibold">
                        {{ $record->datejoin ?: '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">
                        Fellow No.
                    </div>

                    <div class="text-lg font-semibold">
                        {{ $record->mem_fellow_no ?: '-' }}
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-filament::section>
