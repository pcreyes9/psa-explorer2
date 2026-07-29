<div
    x-data="{ loading: false }"
    x-init="
        Livewire.hook('request', ({ respond }) => {
            loading = true

            respond(() => {
                loading = false
            })
        })
    "
    x-show="loading"
    x-transition.opacity
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-white/90 backdrop-blur-sm"
    style="display:none;"
>

    <div class="text-center">

        <img
            src="{{ asset('images/PSA_LOGO.png') }}"
            class="w-28 mx-auto logo-float"
        >

        <h1 class="mt-5 text-2xl font-bold tracking-wide">
            PSA Explorer
        </h1>

        <p class="mt-1 text-gray-500">
            Philippine Society of Anesthesiologists
        </p>

        <div class="mt-8 flex justify-center gap-2">

            <span class="loading-dot"></span>
            <span class="loading-dot"></span>
            <span class="loading-dot"></span>

        </div>

        <p class="mt-5 text-gray-600">

            Loading...

        </p>

    </div>

</div>