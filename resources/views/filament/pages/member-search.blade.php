<x-filament-panels::page>

    <x-filament::section class="mt-8">

        <div class="flex items-end gap-4">

            <div class="flex-1">
                <x-filament::input.wrapper>
                    <x-filament::input
                        wire:model.defer="searchInput"
                        wire:keydown.enter="searchMembers"
                        placeholder="Enter Member ID or Last Name"
                    />
                </x-filament::input.wrapper>
            </div>

            <x-filament::button
                wire:click="searchMembers"
                icon="heroicon-m-magnifying-glass"
            >
                Search
            </x-filament::button>

        </div>

    </x-filament::section>

    {{ $this->table }}

</x-filament-panels::page>
