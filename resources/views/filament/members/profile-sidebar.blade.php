<x-filament::section>
    <div class="flex flex-col items-center gap-4 text-center">

        <img
            src="{{ $record->photo_url }}"
            alt="{{ $record->mem_last_name }}"
            class="h-32 w-32 rounded-full object-cover ring-4 ring-gray-200 dark:ring-gray-700"
        >

        <div>
            <h2 class="text-xl font-bold">
                {{ strtoupper($record->mem_first_name . ' ' . $record->mem_last_name) }}
            </h2>

            <p class="text-sm text-gray-500">
                PSA ID #{{ $record->member_id_no }}
            </p>
        </div>

        <div class="flex flex-wrap justify-center gap-2">

            <span class="rounded-full bg-success-100 px-3 py-1 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">
                {{ $record->psa_mem_stat }}
            </span>

            <span class="rounded-full bg-primary-100 px-3 py-1 text-xs font-medium text-primary-700 dark:bg-primary-500/10 dark:text-primary-400">
                {{ $record->membershipType?->Memtype ?? $record->psa_mem_type }}
            </span>

            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-500/10 dark:text-gray-400">
                {{ $record->chapter?->psa_chapter_desc ?? $record->psa_chapter_code }}
            </span>

        </div>

        <div class="w-full border-t border-gray-200 pt-4 dark:border-gray-700">

            <dl class="space-y-3 text-sm">

                <div class="flex justify-between">
                    <dt class="text-gray-500">Member Since</dt>
                    <dd>
                        {{ $record->datejoin ? \Carbon\Carbon::parse($record->datejoin)->format('M d, Y') : '-' }}
                    </dd>
                </div>

                <div class="flex justify-between">
                    <dt class="text-gray-500">PRC No.</dt>
                    <dd>{{ $record->mem_prc_no ?: '-' }}</dd>
                </div>

                <div class="flex justify-between">
                    <dt class="text-gray-500">PMA ID</dt>
                    <dd>{{ $record->mem_pma_id_no ?: '-' }}</dd>
                </div>

                <div class="flex justify-between">
                    <dt class="text-gray-500">Fellow No.</dt>
                    <dd>{{ $record->mem_fellow_no ?: '-' }}</dd>
                </div>

            </dl>

        </div>

    </div>
</x-filament::section>
