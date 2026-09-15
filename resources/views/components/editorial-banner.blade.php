{{-- ── Editorial banner ───────────────────────────────────────── --}}
<section class="w-full px-4 py-16 sm:px-6 lg:px-8">
    <div class="relative mx-auto max-w-[190rem] overflow-hidden rounded-3xl">

        <img
            src="{{ asset('assets/wear/editorial/everyday-edit.png') }}"
            alt="KP Wear — The Everyday Edit"
            class="block h-[420px] w-full object-cover sm:h-[500px] lg:h-[560px]"
        >

        <div class="absolute inset-0 bg-black/25"></div>

        <div class="absolute inset-0 flex items-center">
            <div class="px-6 text-white sm:px-10 lg:px-14">

                <p class="text-xs font-semibold uppercase tracking-[0.3em]">
                    KP Wear
                </p>

                <h2 class="mt-4 text-4xl font-semibold tracking-tight sm:text-5xl lg:text-6xl">
                    The Everyday Edit
                </h2>

                <p class="mt-4 max-w-md text-base text-white/90 sm:text-lg">
                    Everyday pieces designed to move with you.
                </p>

                <a
                    href="{{ url('/shop') }}"
                    class="mt-7 inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-semibold text-gray-950 transition hover:bg-gray-100"
                >
                    Explore the Collection

                    <x-tabler-arrow-right
                        class="h-4 w-4"
                        stroke-width="1.8"
                    />
                </a>

            </div>
        </div>

    </div>
</section>
