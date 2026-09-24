@extends('layouts.app')

@push('head')
<style nonce="{{ Vite::cspNonce() }}">
    .kp-contact-card { transition: transform 350ms cubic-bezier(.4,0,.2,1), border-color 250ms ease, box-shadow 350ms ease; }
    .kp-contact-card:hover { transform: translateY(-5px); }
    .kp-contact-card .kp-card-icon { transition: transform 350ms cubic-bezier(.4,0,.2,1); }
    .kp-contact-card:hover .kp-card-icon { transform: scale(1.06); }
    .kp-contact-card .kp-card-arrow { transition: transform 250ms ease; }
    .kp-contact-card:hover .kp-card-arrow { transform: translateX(5px); }
    .kp-input { transition: border-color 200ms ease, box-shadow 200ms ease; }
    .kp-input:focus { border-color: var(--kp-emerald); box-shadow: 0 0 0 3px color-mix(in srgb, var(--kp-emerald) 16%, transparent); outline: none; }
    .kp-faq summary { list-style: none; cursor: pointer; }
    .kp-faq summary::-webkit-details-marker { display: none; }
    .kp-faq[open] .kp-faq-chevron { transform: rotate(180deg); }
    .kp-faq-chevron { transition: transform 250ms ease; }
</style>
@endpush

@section('content')
<div class="bg-white text-black">

    {{-- Hero: image-led composition matching the approved Contact direction --}}
    <section class="relative isolate min-h-[580px] overflow-hidden bg-black text-white sm:min-h-[650px]">
        <div
            class="absolute inset-0 bg-cover bg-[center_35%]"
            style="background-image:url('{{ asset('assets/wear/contact/kp-wear-contact-hero.webp') }}');"
            aria-hidden="true"
        ></div>
        <div class="absolute inset-0 bg-gradient-to-r from-black via-black/80 to-black/10"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-transparent to-black/10"></div>

        <div class="relative z-10 mx-auto flex min-h-[580px] kp-content-wide items-center px-5 py-20 sm:min-h-[650px] sm:px-8 lg:px-12 xl:px-16">
            <div class="max-w-[680px]">
                <p class="inline-flex items-center gap-2 border-b border-emerald-400/50 pb-2 text-xs font-bold uppercase tracking-[0.24em] text-emerald-400">
                    <x-tabler-message-circle-2 size="15" stroke-width="2" />
                    Contact KP Wear
                </p>

                <h1 class="mt-6 text-[clamp(3.5rem,7vw,7rem)] font-black leading-[.88] tracking-[-.055em]">
                    Let’s <span class="text-emerald-400">talk.</span>
                </h1>

                <p class="mt-6 max-w-xl text-base leading-7 text-white/80 sm:text-lg sm:leading-8">
                    Whether it's a question about a product, an order, sizing, returns, or working with us — our team reads every message.
                </p>

                <div class="mt-9 grid max-w-2xl gap-4 border-t border-white/15 pt-6 text-sm text-white/75 sm:grid-cols-3 sm:gap-6">
                    <span class="flex items-center gap-2">
                        <x-tabler-clock size="17" class="shrink-0 text-emerald-400" />
                        Mon–Sat, 9:00–18:00 EAT
                    </span>
                    <span class="flex items-center gap-2">
                        <x-tabler-map-pin size="17" class="shrink-0 text-emerald-400" />
                        Dar es Salaam, Tanzania
                    </span>
                    <a href="mailto:hello@kpwear.co.tz" class="flex items-center gap-2 transition hover:text-emerald-300">
                        <x-tabler-mail size="17" class="shrink-0 text-emerald-400" />
                        hello@kpwear.co.tz
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Contact paths --}}
    <section class="bg-white">
        <div class="mx-auto kp-content px-5 py-14 sm:px-8 sm:py-18 lg:px-12 lg:py-20 xl:px-16">
            <div class="mb-9 flex flex-col justify-between gap-5 md:flex-row md:items-end">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-emerald-600">How can we help?</p>
                    <h2 class="mt-2 text-3xl font-black tracking-[-.035em] sm:text-4xl">Choose what fits best.</h2>
                </div>
                <p class="max-w-md text-sm leading-6 text-black/60">
                    Quickly find the right way to reach us, or use the message form below to tell us exactly what you need.
                </p>
            </div>

            <div class="grid gap-5 md:grid-cols-3">
                <a id="customer-support" href="#message" class="kp-contact-card group rounded-2xl border border-emerald-950/10 bg-white p-7 shadow-[0_12px_35px_rgba(0,0,0,.045)] hover:border-emerald-300 hover:shadow-[0_18px_45px_rgba(0,0,0,.08)]">
                    <span class="kp-card-icon flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <x-tabler-message-circle-2 size="21" stroke-width="1.8" />
                    </span>
                    <p class="mt-6 text-[10px] font-bold uppercase tracking-[0.22em] text-black/55">Customer support</p>
                    <h3 class="mt-2 text-2xl font-black tracking-tight">Need help?</h3>
                    <p class="mt-3 text-sm leading-6 text-black/65">Questions about products, sizing, your order or your shopping experience.</p>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-emerald-700">Send an enquiry <span aria-hidden="true" class="kp-card-arrow">→</span></span>
                </a>

                <a id="orders" href="#message" class="kp-contact-card group rounded-2xl border border-emerald-950/10 bg-white p-7 shadow-[0_12px_35px_rgba(0,0,0,.045)] hover:border-emerald-300 hover:shadow-[0_18px_45px_rgba(0,0,0,.08)]">
                    <span class="kp-card-icon flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <x-tabler-package size="21" stroke-width="1.8" />
                    </span>
                    <p class="mt-6 text-[10px] font-bold uppercase tracking-[0.22em] text-black/55">Orders</p>
                    <h3 class="mt-2 text-2xl font-black tracking-tight">Order enquiries</h3>
                    <p class="mt-3 text-sm leading-6 text-black/65">Need help with an existing order? Include your order number so we can identify it quickly.</p>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-emerald-700">Ask about an order <span aria-hidden="true" class="kp-card-arrow">→</span></span>
                </a>

                <a id="partnerships" href="#message" class="kp-contact-card group rounded-2xl border border-emerald-950/10 bg-white p-7 shadow-[0_12px_35px_rgba(0,0,0,.045)] hover:border-emerald-300 hover:shadow-[0_18px_45px_rgba(0,0,0,.08)]">
                    <span class="kp-card-icon flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <x-tabler-briefcase size="21" stroke-width="1.8" />
                    </span>
                    <p class="mt-6 text-[10px] font-bold uppercase tracking-[0.22em] text-black/55">Partnerships</p>
                    <h3 class="mt-2 text-2xl font-black tracking-tight">Work with us</h3>
                    <p class="mt-3 text-sm leading-6 text-black/65">For collaborations, wholesale opportunities and other business enquiries.</p>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-emerald-700">Start a conversation <span aria-hidden="true" class="kp-card-arrow">→</span></span>
                </a>
            </div>
        </div>
    </section>

    {{-- Message + contact details: the main editorial split from the approved direction --}}
    <section id="message" class="border-y border-emerald-950/10 bg-emerald-50/45">
        <div class="mx-auto grid kp-content gap-10 px-5 py-14 sm:px-8 lg:grid-cols-[1.12fr_.88fr] lg:gap-14 lg:px-12 lg:py-20 xl:px-16">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-emerald-600">Send us a message</p>
                <h2 class="mt-3 text-4xl font-black tracking-[-.045em] sm:text-5xl">We’d love to hear from you.</h2>
                <p class="mt-5 max-w-xl text-sm leading-7 text-black/65 sm:text-base">Fill in the form below and we’ll get back to you as soon as possible.</p>

                <form data-contact-form class="mt-8 rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-[0_18px_55px_rgba(0,0,0,.055)] sm:p-8">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-xs font-bold uppercase tracking-[0.16em] text-black">Name</span>
                            <input type="text" name="name" autocomplete="name" required aria-label="Name" class="kp-input mt-2 w-full rounded-xl border border-emerald-950/12 bg-white px-4 py-3.5 text-sm text-black placeholder:text-black/35" placeholder="Your name">
                        </label>
                        <label class="block">
                            <span class="text-xs font-bold uppercase tracking-[0.16em] text-black">Email</span>
                            <input type="email" name="email" autocomplete="email" required spellcheck="false" aria-label="Email" class="kp-input mt-2 w-full rounded-xl border border-emerald-950/12 bg-white px-4 py-3.5 text-sm text-black placeholder:text-black/35" placeholder="you@example.com">
                        </label>
                    </div>

                    <label class="mt-5 block">
                        <span class="text-xs font-bold uppercase tracking-[0.16em] text-black">Enquiry type</span>
                        <select name="type" aria-label="Enquiry type" class="kp-input mt-2 w-full rounded-xl border border-emerald-950/12 bg-white px-4 py-3.5 text-sm text-black">
                            <option>Customer support</option>
                            <option>Order enquiry</option>
                            <option>Product / sizing</option>
                            <option>Returns</option>
                            <option>Wholesale</option>
                            <option>Collaboration</option>
                            <option>Other</option>
                        </select>
                    </label>

                    <label class="mt-5 block">
                        <span class="text-xs font-bold uppercase tracking-[0.16em] text-black">Message</span>
                        <textarea name="message" rows="7" required aria-label="Message" class="kp-input mt-2 w-full resize-y rounded-xl border border-emerald-950/12 bg-white px-4 py-3.5 text-sm text-black placeholder:text-black/35" placeholder="How can we help?"></textarea>
                    </label>

                    <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
                        <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                    </div>

                    <p data-contact-feedback class="mt-4 hidden rounded-xl px-4 py-3 text-sm" role="status" aria-live="polite"></p>

                    <button data-contact-submit type="submit" class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 py-3.5 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto">
                        Send message <span aria-hidden="true">→</span>
                    </button>

                    <p class="mt-4 flex items-center gap-1.5 text-xs text-black/55">
                        <x-tabler-shield-check size="14" class="text-emerald-700" />
                        Your details are only used to respond to your enquiry.
                    </p>
                </form>
            </div>

            <aside class="lg:pt-12">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-black/55">Other ways to reach us</p>
                <h2 class="mt-2 text-3xl font-black tracking-[-.035em]">Contact details</h2>

                <div class="mt-7 divide-y divide-emerald-950/10 overflow-hidden rounded-2xl border border-emerald-950/10 bg-white shadow-sm">
                    <a href="mailto:hello@kpwear.co.tz" class="group flex items-center gap-4 px-5 py-4 transition hover:bg-emerald-50/60">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700"><x-tabler-mail size="20" stroke-width="1.8" /></span>
                        <span><span class="block text-[10px] font-bold uppercase tracking-[0.2em] text-black/55">Email</span><span class="mt-1 block text-sm font-semibold group-hover:text-emerald-700">hello@kpwear.co.tz</span></span>
                    </a>
                    <a href="tel:+255700123456" class="group flex items-center gap-4 px-5 py-4 transition hover:bg-emerald-50/60">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700"><x-tabler-phone size="20" stroke-width="1.8" /></span>
                        <span><span class="block text-[10px] font-bold uppercase tracking-[0.2em] text-black/55">Phone</span><span class="mt-1 block text-sm font-semibold group-hover:text-emerald-700">+255 700 123 456</span></span>
                    </a>
                    <div class="flex items-center gap-4 px-5 py-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700"><x-tabler-map-pin size="20" stroke-width="1.8" /></span>
                        <span><span class="block text-[10px] font-bold uppercase tracking-[0.2em] text-black/55">Location</span><span class="mt-1 block text-sm font-semibold">Dar es Salaam, Tanzania</span></span>
                    </div>
                    <div class="flex items-center gap-4 px-5 py-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700"><x-tabler-clock size="20" stroke-width="1.8" /></span>
                        <span><span class="block text-[10px] font-bold uppercase tracking-[0.2em] text-black/55">Working hours</span><span class="mt-1 block text-sm font-semibold">Mon–Sat, 9:00–18:00 EAT</span></span>
                    </div>
                </div>

                <div class="relative mt-6 min-h-[280px] overflow-hidden rounded-3xl bg-black">
                    <img src="{{ asset('assets/wear/contact/kp-wear-contact-story.webp') }}" alt="KP Wear creative brand artwork and clothing" class="absolute inset-0 h-full w-full object-cover object-center" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/35 to-transparent"></div>
                    <div class="relative z-10 flex min-h-[280px] items-end p-7 text-white">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.24em] text-emerald-400">More than clothing</p>
                            <h3 class="mt-3 max-w-[310px] text-3xl font-black leading-tight tracking-[-.035em]">Art. People. Clothing. A bigger story.</h3>
                            <span class="mt-6 block h-1.5 w-16 rounded-full bg-emerald-400"></span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    {{-- FAQ --}}
    <section class="bg-white">
        <div class="mx-auto kp-content px-5 py-14 sm:px-8 lg:px-12 lg:py-20 xl:px-16">
            <div class="grid gap-10 lg:grid-cols-[.75fr_1.25fr] lg:gap-16">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-emerald-600">Frequently asked questions</p>
                    <h2 class="mt-2 text-3xl font-black tracking-[-.04em] sm:text-4xl">Quick answers.</h2>
                    <p class="mt-4 max-w-sm text-sm leading-6 text-black/60">Can’t find what you’re looking for? Send us a message and we’ll help you directly.</p>
                </div>

                <div class="divide-y divide-emerald-950/10 rounded-2xl border border-emerald-950/10">
                    <details class="kp-faq group p-5 sm:p-6">
                        <summary class="flex items-center justify-between gap-4"><span class="text-sm font-semibold sm:text-base">How long does delivery take?</span><x-tabler-chevron-down size="18" class="kp-faq-chevron flex-shrink-0 text-emerald-700" /></summary>
                        <p class="mt-3 text-sm leading-6 text-black/65">Delivery time depends on your location and is calculated at checkout. Orders within Dar es Salaam typically arrive faster than upcountry deliveries.</p>
                    </details>
                    <details class="kp-faq group p-5 sm:p-6">
                        <summary class="flex items-center justify-between gap-4"><span class="text-sm font-semibold sm:text-base">How do I track my order?</span><x-tabler-chevron-down size="18" class="kp-faq-chevron flex-shrink-0 text-emerald-700" /></summary>
                        <p class="mt-3 text-sm leading-6 text-black/65">Once your order is placed, you can follow its status from your account under My Orders, or use the order number we send you.</p>
                    </details>
                    <details class="kp-faq group p-5 sm:p-6">
                        <summary class="flex items-center justify-between gap-4"><span class="text-sm font-semibold sm:text-base">Do you offer wholesale or collaborations?</span><x-tabler-chevron-down size="18" class="kp-faq-chevron flex-shrink-0 text-emerald-700" /></summary>
                        <p class="mt-3 text-sm leading-6 text-black/65">Yes — select “Wholesale” or “Collaboration” from the enquiry type above and tell us a bit about what you have in mind.</p>
                    </details>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script nonce="{{ Vite::cspNonce() }}">
(function () {
    const form = document.querySelector('[data-contact-form]');
    if (!form) return;
    const button = form.querySelector('[data-contact-submit]');
    const feedback = form.querySelector('[data-contact-feedback]');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        feedback.className = 'mt-4 hidden rounded-xl px-4 py-3 text-sm';
        button.disabled = true;
        const original = button.innerHTML;
        button.textContent = 'Sending…';
        const fd = new FormData(form);

        try {
            const response = await fetch('/api/v1/contact', {
                method: 'POST',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: fd.get('name'),
                    email: fd.get('email'),
                    type: fd.get('type'),
                    message: fd.get('message'),
                    website: fd.get('website')
                })
            });
            const data = await response.json().catch(() => null);
            if (!response.ok) throw new Error(data?.message || Object.values(data?.errors || {}).flat?.()?.[0] || `Request failed (${response.status})`);
            feedback.textContent = data?.message || 'Your message has been sent.';
            feedback.className = 'mt-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700';
            form.reset();
        } catch (error) {
            feedback.textContent = error.message || 'Unable to send your message right now.';
            feedback.className = 'mt-4 rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700';
        } finally {
            button.disabled = false;
            button.innerHTML = original;
        }
    });
})();
</script>
@endpush
