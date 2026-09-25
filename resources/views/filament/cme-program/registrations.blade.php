@php
    $registrations = $record->registrations()->count();
@endphp

<div class="p-4">
    Total registrations: {{ $registrations }}
</div>