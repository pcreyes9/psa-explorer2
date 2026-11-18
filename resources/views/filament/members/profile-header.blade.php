<x-filament::section class="member-profile-hero overflow-hidden">
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary-600 via-primary-700 to-indigo-900 p-6 text-white shadow-lg sm:p-8">
        <div class="absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-24 left-1/3 h-64 w-64 rounded-full bg-indigo-300/10"></div>

        <div class="relative flex flex-col gap-8 lg:flex-row lg:items-center">
            <div class="flex shrink-0 justify-center lg:justify-start">
                <div class="rounded-full bg-white/20 p-2 ring-1 ring-white/30 backdrop-blur-sm">
                    <img
                        src="{{ $record->photo_url }}"
                        alt="{{ $record->mem_last_name }}"
                        class="h-32 w-32 rounded-full border-4 border-white/80 object-cover object-[50%_20%] shadow-xl sm:h-40 sm:w-40"
                    >
                </div>
            </div>

            <div class="min-w-0 flex-1 text-center lg:text-left">
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-primary-100">
                    Member Profile
                </p>

                <h2 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">
                    {{ strtoupper(trim($record->mem_first_name . ' ' . $record->mem_middle_name . ' ' . $record->mem_last_name)) }}
                </h2>

                <p class="mt-2 text-sm text-primary-100">
                    PSA ID #{{ $record->member_id_no }}
                </p>

                <div class="mt-5 flex flex-wrap justify-center gap-2 lg:justify-start">
                    <x-filament::badge color="success">
                        {{ $record->mem_stat ?: '—' }}
                    </x-filament::badge>
                    <x-filament::badge color="warning">
                        {{ $record->psa_mem_stat ?: '—' }}
                    </x-filament::badge>
                    <x-filament::badge color="info">
                        {{ $record->membershipType?->Memtype ?? $record->psa_mem_type ?? '—' }}
                    </x-filament::badge>
                    <x-filament::badge color="gray">
                        {{ $record->chapter?->psa_chapter_desc ?? $record->psa_chapter_code ?? '—' }}
                    </x-filament::badge>
                </div>
            </div>
        </div>

        <div class="relative mt-8 grid grid-cols-2 gap-3 border-t border-white/20 pt-6 sm:grid-cols-4">
            <div class="rounded-xl bg-white/10 p-3 backdrop-blur-sm">
                <div class="text-xs uppercase tracking-wide text-primary-100">PRC No.</div>
                <div class="mt-1 truncate text-sm font-semibold">{{ $record->mem_prc_no ?: '—' }}</div>
            </div>
            <div class="rounded-xl bg-white/10 p-3 backdrop-blur-sm">
                <div class="text-xs uppercase tracking-wide text-primary-100">PMA ID</div>
                <div class="mt-1 truncate text-sm font-semibold">{{ $record->mem_pma_id_no ?: '—' }}</div>
            </div>
            <div class="rounded-xl bg-white/10 p-3 backdrop-blur-sm">
                <div class="text-xs uppercase tracking-wide text-primary-100">Date Joined</div>
                <div class="mt-1 truncate text-sm font-semibold">{{ $record->datejoin ?: '—' }}</div>
            </div>
            <div class="rounded-xl bg-white/10 p-3 backdrop-blur-sm">
                <div class="text-xs uppercase tracking-wide text-primary-100">Fellow No.</div>
                <div class="mt-1 truncate text-sm font-semibold">{{ $record->mem_fellow_no ?: '—' }}</div>
            </div>
        </div>
    </div>
</x-filament::section>
