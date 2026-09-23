<footer
    class="mt-16 bg-green-950/90"
    style="color:#FFFFFF !important;"
>
    <div class="mx-auto grid kp-content gap-10 px-4 py-12 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">

        <div>
            <div class="text-2xl font-black" style="color:#FFFFFF !important;">
                KP<span class="text-emerald-500">.</span>
            </div>
            <p
                class="mt-4 max-w-xs text-sm leading-6"
                style="color:#FFFFFF !important;"
            >
                Everyday clothing made for movement, comfort and confidence.
            </p>

            <div class="mt-5 flex items-center gap-3">
                <a
                    href="https://wa.me/message/M7BGTYG4GWTEJ1"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="KP Wear on WhatsApp"
                    class="flex h-11 w-11 items-center justify-center rounded-full bg-white/10 transition-colors hover:bg-emerald-500"
                    style="color:#25D366 !important;"
                >
                    <x-tabler-brand-whatsapp size="23" stroke-width="1.9" />
                </a>
                <a
                    href="https://www.instagram.com/kpwear_/"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="KP Wear on Instagram"
                    class="flex h-11 w-11 items-center justify-center rounded-full bg-white/10 transition-colors hover:bg-emerald-500"
                    style="color:#E4405F !important;"
                >
                    <x-tabler-brand-instagram size="23" stroke-width="1.9" />
                </a>
            </div>
        </div>

        <div>
            <h4
                class="mb-4 text-sm font-semibold uppercase tracking-wider"
                style="color:#FFFFFF !important;"
            >
                Shop
            </h4>

            <div class="space-y-2.5 text-sm">
                <a
                    href="{{ route('shop') }}"
                    class="flex items-center gap-2 transition-colors hover:text-emerald-400"
                    style="color:#FFFFFF !important;"
                >
                    <x-tabler-shopping-bag size="15" class="flex-shrink-0" />
                    All products
                </a>

                <a
                    href="{{ route('collections') }}"
                    class="flex items-center gap-2 transition-colors hover:text-emerald-400"
                    style="color:#FFFFFF !important;"
                >
                    <x-tabler-sparkles size="15" class="flex-shrink-0" />
                    Collections
                </a>

                <a
                    href="{{ route('wishlist') }}"
                    class="flex items-center gap-2 transition-colors hover:text-emerald-400"
                    style="color:#FFFFFF !important;"
                >
                    <x-tabler-heart size="15" class="flex-shrink-0" />
                    Wishlist
                </a>
            </div>
        </div>

        <div>
            <h4
                class="mb-4 text-sm font-semibold uppercase tracking-wider"
                style="color:#FFFFFF !important;"
            >
                Account
            </h4>

            <div class="space-y-2.5 text-sm">
                <a
                    href="{{ route('account') }}"
                    class="flex items-center gap-2 transition-colors hover:text-emerald-400"
                    style="color:#FFFFFF !important;"
                >
                    <x-tabler-user size="15" class="flex-shrink-0" />
                    My account
                </a>

                <a
                    href="{{ route('account.orders') }}"
                    class="flex items-center gap-2 transition-colors hover:text-emerald-400"
                    style="color:#FFFFFF !important;"
                >
                    <x-tabler-package size="15" class="flex-shrink-0" />
                    Orders
                </a>

                <a
                    href="{{ route('account.addresses') }}"
                    class="flex items-center gap-2 transition-colors hover:text-emerald-400"
                    style="color:#FFFFFF !important;"
                >
                    <x-tabler-map-pin size="15" class="flex-shrink-0" />
                    Addresses
                </a>
            </div>
        </div>

        {{-- Was a duplicate of the brand tagline in column 1 — replaced with
             actual contact info, which does more trust-building work here. --}}
        <div>
            <h4
                class="mb-4 text-sm font-semibold uppercase tracking-wider"
                style="color:#FFFFFF !important;"
            >
                Get in touch
            </h4>

            <div class="space-y-2.5 text-sm">
                <a
                    href="tel:+255757829130"
                    class="flex items-center gap-2 transition-colors hover:text-emerald-400"
                    style="color:#FFFFFF !important;"
                >
                    <x-tabler-phone size="15" class="flex-shrink-0" />
                    0757 829 130
                </a>

                <a
                    href="tel:+255796829131"
                    class="flex items-center gap-2 transition-colors hover:text-emerald-400"
                    style="color:#FFFFFF !important;"
                >
                    <x-tabler-phone size="15" class="flex-shrink-0" />
                    0796 829 131
                </a>

                <p class="flex items-center gap-2" style="color:#FFFFFF !important;">
                    <x-tabler-map-pin size="15" class="flex-shrink-0" />
                    Dar es Salaam, Tanzania
                </p>
            </div>
        </div>
    </div>

    {{-- Secure payment methods --}}
    <div
        class="border-t"
        style="border-color:rgba(255,255,255,.15);"
    >
        <div class="mx-auto kp-content px-4 py-7 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="shrink-0">
                    <h4
                        class="text-sm font-semibold uppercase tracking-wider"
                        style="color:#FFFFFF !important;"
                    >
                        Secure payments
                    </h4>
                    <div class="mt-2 h-1 w-12 rounded-full bg-emerald-500"></div>
                </div>

                <div class="flex flex-wrap items-center justify-start gap-x-5 gap-y-4 lg:justify-end">
                    <img src="{{ asset('assets/wear/payments/mpesa.png') }}" alt="Vodacom M-Pesa" class="h-10 w-auto object-contain sm:h-11" loading="lazy">
                    <img src="{{ asset('assets/wear/payments/mastercard.png') }}" alt="Mastercard" class="h-10 w-auto object-contain sm:h-11" loading="lazy">
                    <img src="{{ asset('assets/wear/payments/halopesa.png') }}" alt="HaloPesa" class="h-10 w-auto object-contain sm:h-11" loading="lazy">
                    <img src="{{ asset('assets/wear/payments/airtel-money.png') }}" alt="Airtel Money" class="h-10 w-auto object-contain sm:h-11" loading="lazy">
                    <img src="{{ asset('assets/wear/payments/yas.png') }}" alt="Yas" class="h-10 w-auto object-contain sm:h-11" loading="lazy">
                    <img src="{{ asset('assets/wear/payments/visa.png') }}" alt="Visa" class="h-9 w-auto object-contain sm:h-10" loading="lazy">
                    <img src="{{ asset('assets/wear/payments/selcom.png') }}" alt="Selcom" class="h-10 w-auto object-contain sm:h-11" loading="lazy">
                </div>
            </div>
        </div>
    </div>

    <div
        class="border-t"
        style="border-color:rgba(255,255,255,.15);"
    >
        <div
            class="mx-auto flex kp-content flex-col gap-2 px-4 py-6 text-sm sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8"
            style="color:#FFFFFF !important;"
        >
            <p style="color:#FFFFFF !important;">
                © {{ date('Y') }} KP Wear. All rights reserved.
            </p>

            <p style="color:#FFFFFF !important;">
                Tanzania
            </p>
        </div>
    </div>
</footer>
