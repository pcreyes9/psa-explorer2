<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tailwind Test</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    <div class="bg-red-500 text-white p-6">
        TAILWIND TEST
    </div>

    <x-filament::button
        tag="a"
        :href="route('filament.admin.auth.login')"
    >
        Login
    </x-filament::button>


</body>
</html>