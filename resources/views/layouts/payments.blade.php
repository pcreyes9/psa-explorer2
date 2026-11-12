<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }}</title>

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        [x-cloak] { display: none !important; }

        body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", sans-serif; font-size: 14px; color: #111827; background: #f3f4f6; }
        .topbar { display: flex; align-items: center; justify-content: space-between; padding: 12px 24px; background: #fff; border-bottom: 1px solid #e5e7eb; }
        .topbar a { color: #2563eb; text-decoration: none; font-weight: 500; }
        .topbar a:hover { text-decoration: underline; }
        .container { max-width: 1200px; margin: 0 auto; padding: 24px; }
        .stack > * + * { margin-top: 20px; }

        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 1px 2px rgba(0,0,0,.05); overflow: hidden; }
        .card-body { padding: 20px; }
        .card-head { padding: 16px 24px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; }
        h1, h2, h3 { margin: 0; }
        h2 { font-size: 18px; font-weight: 600; }
        .muted { color: #6b7280; }
        .small { font-size: 12px; }
        .mt { margin-top: 20px; }

        .grid { display: grid; gap: 16px; }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .span-3 { grid-column: span 3; }
        .span-all { grid-column: 1 / -1; }
        @media (max-width: 800px) { .grid-4, .grid-3 { grid-template-columns: 1fr; } .span-3 { grid-column: auto; } }

        label.lbl { display: block; margin-bottom: 4px; font-weight: 500; }
        input[type=text], input[type=number], input[type=date], select, textarea {
            width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; font: inherit; color: inherit;
        }
        input:focus, select:focus, textarea:focus { outline: 2px solid #93c5fd; border-color: #3b82f6; }
        .error { margin: 4px 0 0; font-size: 12px; font-weight: 500; color: #dc2626; }

        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; font: inherit; font-weight: 500; cursor: pointer; }
        .btn:hover:not(:disabled) { background: #f9fafb; }
        .btn:disabled { opacity: .5; cursor: not-allowed; }
        .btn-primary { background: #2563eb; border-color: #2563eb; color: #fff; }
        .btn-primary:hover:not(:disabled) { background: #1d4ed8; }
        .btn-success { background: #16a34a; border-color: #16a34a; color: #fff; }
        .btn-success:hover:not(:disabled) { background: #15803d; }
        .btn-link { border: 0; background: none; padding: 0; color: #dc2626; font-weight: 600; cursor: pointer; font: inherit; }
        .actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px; margin-top: 20px; }

        .member { display: flex; align-items: center; gap: 16px; }
        .member img { width: 128px; height: 128px; border-radius: 50%; border: 4px solid #d1d5db; object-fit: cover; object-position: 50% 20%; box-shadow: 0 1px 2px rgba(0,0,0,.1); }
        .member .name { font-size: 24px; font-weight: 700; }

        .chip { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; background: #f9fafb; }
        .combo { position: relative; }
        .dropdown { position: absolute; z-index: 20; left: 0; right: 0; margin: 4px 0 0; padding: 4px 0; list-style: none; max-height: 260px; overflow-y: auto; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,.12); }
        .dropdown li { padding: 8px 12px; cursor: pointer; }
        .dropdown li:hover { background: #eff6ff; }
        .dropdown li.empty { color: #6b7280; cursor: default; }
        .dropdown li.empty:hover { background: none; }

        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 12px; text-align: left; border-top: 1px solid #e5e7eb; }
        thead th { background: #f3f4f6; border-top: 0; }
        .right { text-align: right !important; }
        .center { text-align: center !important; }
        .nowrap { white-space: nowrap; }
        .amount { width: 128px; text-align: right; }
        tfoot td { background: #eff6ff; font-weight: 600; }
        tr.dim td { color: #9ca3af; background: #fafafa; }
        .badge { display: inline-block; padding: 1px 8px; border-radius: 999px; background: #e5e7eb; font-size: 11px; }

        .empty-state { padding: 32px; text-align: center; background: #fff; border: 2px dashed #d1d5db; border-radius: 12px; }

        .overlay { position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center; padding: 16px; background: rgba(17,24,39,.5); }
        .modal { width: 100%; max-height: 90vh; display: flex; flex-direction: column; background: #fff; border-radius: 12px; box-shadow: 0 20px 50px rgba(0,0,0,.3); }
        .modal.wide { max-width: 1100px; }
        .modal.narrow { max-width: 460px; }
        .modal-head, .modal-foot { padding: 16px 20px; }
        .modal-head { border-bottom: 1px solid #e5e7eb; }
        .modal-body { padding: 0; overflow: auto; }
        .modal-foot { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px; border-top: 1px solid #e5e7eb; }
        .modal-pad { padding: 20px; }

        .toasts { position: fixed; top: 16px; right: 16px; z-index: 100; display: flex; flex-direction: column; gap: 8px; width: 340px; max-width: calc(100vw - 32px); }
        .toast { display: flex; justify-content: space-between; gap: 8px; padding: 12px 14px; border-radius: 10px; background: #fff; border-left: 5px solid #16a34a; box-shadow: 0 8px 24px rgba(0,0,0,.18); }
        .toast.error { border-left-color: #dc2626; }
        .toast button { border: 0; background: none; font-size: 18px; line-height: 1; cursor: pointer; color: #6b7280; }
    </style>

    @livewireStyles
</head>
<body>

    <div class="topbar">
        <strong>{{ config('app.name') }}</strong>
        <a href="{{ filament()->getUrl() ?? url('/') }}">&larr; Back to admin panel</a>
    </div>

    <main class="container">
        {{ $slot }}
    </main>

    <div
        class="toasts"
        x-data="{
            toasts: [],
            add(toast) {
                toast.id = Date.now() + Math.random();
                this.toasts.push(toast);
                if (! toast.persistent) {
                    setTimeout(() => this.remove(toast.id), 6000);
                }
            },
            remove(id) {
                this.toasts = this.toasts.filter((toast) => toast.id !== id);
            },
        }"
        x-on:toast.window="add($event.detail)"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div class="toast" :class="toast.type">
                <div>
                    <strong x-text="toast.title"></strong>
                    <div class="muted" x-text="toast.message"></div>
                </div>
                <button type="button" x-on:click="remove(toast.id)" aria-label="Close">&times;</button>
            </div>
        </template>
    </div>

    @livewireScripts
</body>
</html>
