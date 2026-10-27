<div class="flex items-center px-2 py-1 text-xs text-gray-500 dark:text-gray-400">
    <span class="mr-1">DB:</span>

    <span class="font-medium text-gray-700 dark:text-gray-200">
        {{ config('database.connections.' . config('database.default') . '.database') ?? 'Not configured' }}
    </span>
</div>