<x-filament-panels::page>
    <x-slot name="description">
        Select a chapter to view its membership totals and standing summary.
    </x-slot>

    <form wire:submit="generateReport" class="space-y-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3 md:items-end">
            <div>
                <label for="asOfDate" class="mb-2 block text-sm font-medium">Report Date</label>
                <input
                    id="asOfDate"
                    type="date"
                    wire:model="asOfDate"
                    max="{{ now()->toDateString() }}"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800"
                >
                @error('asOfDate')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <x-filament::button type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="generateReport">Refresh Report</span>
                <span wire:loading wire:target="generateReport">Refreshing...</span>
            </x-filament::button>
        </div>
    </form>

    <x-filament::section class="mt-6" heading="Chapters">
        <div class="flex flex-wrap gap-2">
            @forelse ($chapters as $chapter)
                <x-filament::button
                    color="{{ $selectedChapter === $chapter['code'] ? 'primary' : 'gray' }}"
                    wire:click="selectChapter('{{ $chapter['code'] }}')"
                    wire:loading.attr="disabled"
                    wire:target="selectChapter"
                >
                    {{ $chapter['name'] }}
                </x-filament::button>
            @empty
                <p class="text-sm text-gray-500">No chapters found.</p>
            @endforelse
        </div>

        <div wire:loading wire:target="selectChapter" class="mt-3 text-sm text-gray-500">
            Loading chapter summary...
        </div>
    </x-filament::section>

    <x-filament::section class="mt-6" heading="Chapter Summary">
        @if ($selectedChapter)
            <p class="mb-4 text-sm text-gray-500">
                {{ collect($chapters)->firstWhere('code', $selectedChapter)['name'] ?? $selectedChapter }}
            </p>
        @else
            <p class="mb-4 text-sm text-gray-500">Select a chapter to display its summary.</p>
        @endif

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Members</div>
                <div class="mt-2 text-2xl font-semibold">{{ number_format($summary['total_members']) }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Regular Members (RM)</div>
                <div class="mt-2 text-2xl font-semibold">{{ number_format($summary['regular_members']) }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Trainee Members (TM)</div>
                <div class="mt-2 text-2xl font-semibold">{{ number_format($summary['trainee_members']) }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Life Members (LM)</div>
                <div class="mt-2 text-2xl font-semibold">{{ number_format($summary['life_members']) }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Good Standing</div>
                <div class="mt-2 text-2xl font-semibold text-success-600">
                    {{ $summary['good_standing'] === null ? '—' : number_format($summary['good_standing']) }}
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Not Good Standing</div>
                <div class="mt-2 text-2xl font-semibold text-danger-600">
                    {{ $summary['not_good_standing'] === null ? '—' : number_format($summary['not_good_standing']) }}
                </div>
            </x-filament::section>
        </div>
    </x-filament::section>
</x-filament-panels::page>
