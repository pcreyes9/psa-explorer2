<x-filament-panels::page>

    @include('filament.members.profile-header', [
        'record' => $this->record,
    ])

    <div class="mt-2">
        {{ $this->infolist }}
    </div>

    {{-- @include('filament.members.financial.index', [
        'record' => $this->record,
    ]) --}}
</x-filament-panels::page>
