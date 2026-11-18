<x-filament-panels::page>
    <form wire:submit="generateReport" class="space-y-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3 md:items-end">
            <div>
                <label for="fromDate" class="mb-2 block text-sm font-medium">From</label>
                <input
                    id="fromDate"
                    type="date"
                    wire:model="fromDate"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800"
                >
                @error('fromDate')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="toDate" class="mb-2 block text-sm font-medium">To</label>
                <input
                    id="toDate"
                    type="date"
                    wire:model="toDate"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800"
                >
                @error('toDate')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <x-filament::button
                type="submit"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="generateReport">Generate Report</span>
                <span wire:loading wire:target="generateReport">Generating...</span>
            </x-filament::button>
        </div>
    </form>

    <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">Payments</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($summary['payment_count']) }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Collected</div>
            <div class="mt-2 text-2xl font-semibold">₱{{ number_format($summary['total_amount'], 2) }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">Average Payment</div>
            <div class="mt-2 text-2xl font-semibold">₱{{ number_format($summary['average_amount'], 2) }}</div>
        </x-filament::section>
    </div>

    <x-filament::section class="mt-6" heading="Collection by Payment Method">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Payment Method</th>
                        <th class="px-4 py-3 text-right font-semibold">Payments</th>
                        <th class="px-4 py-3 text-right font-semibold">Collected</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($breakdown as $row)
                        <tr>
                            <td class="px-4 py-3">{{ $row['payment_type'] }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($row['payment_count']) }}</td>
                            <td class="px-4 py-3 text-right font-medium">₱{{ number_format($row['total_amount'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-gray-500">No payments found for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
