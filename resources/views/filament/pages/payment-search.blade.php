<x-filament-panels::page>

    <x-filament::section>

        <x-slot name="heading">
            Payment Search
        </x-slot>

        <x-slot name="description">
            Search payments by selecting one search criteria below.
        </x-slot>

        <div class="space-y-6 grid grid-cols-2 gap-3">

            <div>

                <label class="block text-sm font-medium mb-3">
                    Search By
                </label>

                <div class="grid grid-cols-2 gap-3">

                    <label class="flex items-center gap-2 cursor-pointer">

                        <input
                            type="radio"
                            wire:model.live="searchBy"
                            value="payment_ref_no"
                        >

                        <span>
                            Reference Number
                        </span>

                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">

                        <input
                            type="radio"
                            wire:model.live="searchBy"
                            value="or_no"
                        >

                        <span>
                            OR Number
                        </span>

                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">

                        <input
                            type="radio"
                            wire:model.live="searchBy"
                            value="member_id_no"
                        >

                        <span>
                            PSA ID Number
                        </span>

                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">

                        <input
                            type="radio"
                            wire:model.live="searchBy"
                            value="last_name"
                        >

                        <span>
                            Last Name
                        </span>

                    </label>

                </div>

            </div>

            <div>

                <x-filament::input.wrapper>

                    <x-filament::input
                        wire:model.live="searchValue"
                        wire:keydown.enter="search"
                        placeholder="{{ $this->searchPlaceholder }}"
                    />

                </x-filament::input.wrapper>

                 <div class="flex justify-end gap-3 mt-5">

                    <x-filament::button
                        icon="heroicon-m-magnifying-glass"
                        wire:click="search"
                    >
                        Search
                    </x-filament::button>

                    <x-filament::button
                        color="gray"
                        icon="heroicon-m-arrow-path"
                        wire:click="resetSearch"
                    >
                        Reset
                    </x-filament::button>

                </div>

            </div>



        </div>

    </x-filament::section>

    @if($searched)

        <x-filament::section>

            <x-slot name="heading">
                Search Results
            </x-slot>

            {{ $this->table }}

        </x-filament::section>

    @endif

</x-filament-panels::page>
