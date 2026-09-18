@extends('layouts.app')

@push('head')
<style nonce="{{ Vite::cspNonce() }}">
  .kp-reveal {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 600ms cubic-bezier(0.4,0,0.2,1),
                transform 600ms cubic-bezier(0.4,0,0.2,1);
  }
  .kp-reveal.is-visible { opacity: 1; transform: translateY(0); }

  .kp-stagger > * { opacity: 0; transform: translateY(24px); transition: opacity 500ms cubic-bezier(0.4,0,0.2,1), transform 500ms cubic-bezier(0.4,0,0.2,1); }
  .kp-stagger.is-visible > * { opacity: 1; transform: translateY(0); }
  .kp-stagger.is-visible > *:nth-child(1) { transition-delay: 0ms; }
  .kp-stagger.is-visible > *:nth-child(2) { transition-delay: 100ms; }
  .kp-stagger.is-visible > *:nth-child(3) { transition-delay: 200ms; }

  .kp-contact-card { transition: transform 400ms cubic-bezier(0.4,0,0.2,1), border-color 300ms ease, box-shadow 400ms ease; }
  .kp-contact-card:hover { transform: translateY(-6px); }
  .kp-contact-card .kp-card-icon { transition: transform 400ms cubic-bezier(0.4,0,0.2,1), background 300ms ease; }
  .kp-contact-card:hover .kp-card-icon { transform: scale(1.1) rotate(-4deg); }
  .kp-contact-card .kp-card-arrow { transition: transform 300ms ease; }
  .kp-contact-card:hover .kp-card-arrow { transform: translateX(6px); }

  .kp-input { transition: border-color 200ms ease, box-shadow 200ms ease; }
  .kp-input:focus { border-color: #059669; box-shadow: 0 0 0 3px rgba(16,185,129,0.15); outline: none; }

  .kp-faq summary { list-style: none; cursor: pointer; }
  .kp-faq summary::-webkit-details-marker { display: none; }
  .kp-faq[open] .kp-faq-chevron { transform: rotate(180deg); }
  .kp-faq-chevron { transition: transform 250ms ease; }

  @media (prefers-reduced-motion: reduce) {
    .kp-reveal, .kp-stagger > * { opacity: 1 !important; transform: none !important; transition: none !important; }
    .kp-contact-card, .kp-contact-card .kp-card-icon, .kp-contact-card .kp-card-arrow,
    .kp-input, .kp-faq-chevron { transition: none !important; }
  }
</style>
@endpush

@section('content')
<div class="bg-white text-gray-950">

    {{-- ── Hero ─────────────────────────────────────────────────── --}}
    <section class="relative overflow-hidden border-b border-gray-200 bg-gray-950 text-white">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_82%_15%,rgba(16,185,129,.22),transparent_38%)]"></div>
        <div class="relative mx-auto max-w-[1600px] px-6 py-20 sm:px-10 lg:px-14 lg:py-28 xl:px-20">
            <div class="max-w-3xl">
                <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3.5 py-1.5 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-400">
                    <x-tabler-message-circle-2 size="14" stroke-width="2" />
                    Contact KP Wear
                </p>
                <h1 class="mt-6 text-5xl font-black leading-[0.95] tracking-[-0.04em] sm:text-6xl lg:text-7xl">
                    Let's talk.
                </h1>
                <p class="mt-6 max-w-xl text-base leading-7 text-white/70 sm:text-lg sm:leading-8">
                    Whether it's a question about a product, an order, sizing, returns, or working with us — our team reads every message.
                </p>

                <div class="mt-9 flex flex-wrap items-center gap-x-8 gap-y-3 border-t border-white/10 pt-7 text-sm text-white/60">
                    <span class="flex items-center gap-2">
                        <x-tabler-clock size="16" class="text-emerald-400" />
                        Mon–Sat, 9:00–18:00 EAT
                    </span>
                    <span class="flex items-center gap-2">
                        <x-tabler-map-pin size="16" class="text-emerald-400" />
                        Dar es Salaam, Tanzania
                    </span>
                    <span class="flex items-center gap-2">
                        <x-tabler-mail size="16" class="text-emerald-400" />
                        hello@kpwear.co.tz
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Contact cards ─────────────────────────────────────────── --}}
    <section class="bg-white text-gray-950">
        <div class="kp-stagger mx-auto max-w-[1400px] px-6 py-16 sm:px-10 lg:px-14 lg:py-20 xl:px-20">
            <div class="mb-10 max-w-xl">
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-emerald-600">How can we help</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">Choose what fits best</h2>
            </div>

            <div class="grid gap-5 md:grid-cols-3">

                <a id="customer-support" href="#message" class="kp-contact-card group rounded-2xl border border-gray-200 bg-white p-7 shadow-sm hover:border-emerald-300 hover:shadow-lg">
                    <span class="kp-card-icon flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <x-tabler-message-circle-2 size="20" stroke-width="1.8" />
                    </span>
                    <p class="mt-6 text-[11px] font-bold uppercase tracking-[0.22em] text-gray-500">Customer support</p>
                    <h3 class="mt-2 text-2xl font-bold tracking-tight">Need help?</h3>
                    <p class="mt-3 text-sm leading-6 text-gray-600">Questions about products, sizing, returns or your shopping experience.</p>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-gray-950">
                        Send an enquiry
                        <span aria-hidden="true" class="kp-card-arrow">→</span>
                    </span>
                </a>

                <a id="orders" href="#message" class="kp-contact-card group rounded-2xl border border-gray-200 bg-white p-7 shadow-sm hover:border-emerald-300 hover:shadow-lg">
                    <span class="kp-card-icon flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <x-tabler-package size="20" stroke-width="1.8" />
                    </span>
                    <p class="mt-6 text-[11px] font-bold uppercase tracking-[0.22em] text-gray-500">Orders</p>
                    <h3 class="mt-2 text-2xl font-bold tracking-tight">Order enquiries</h3>
                    <p class="mt-3 text-sm leading-6 text-gray-600">Need help with an existing order? Include your order number so we can identify it quickly.</p>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-gray-950">
                        Ask about an order
                        <span aria-hidden="true" class="kp-card-arrow">→</span>
                    </span>
                </a>

                <a id="partnerships" href="#message" class="kp-contact-card group rounded-2xl border border-gray-200 bg-white p-7 shadow-sm hover:border-emerald-300 hover:shadow-lg">
                    <span class="kp-card-icon flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <x-tabler-briefcase size="20" stroke-width="1.8" />
                    </span>
                    <p class="mt-6 text-[11px] font-bold uppercase tracking-[0.22em] text-gray-500">Business</p>
                    <h3 class="mt-2 text-2xl font-bold tracking-tight">Work with us</h3>
                    <p class="mt-3 text-sm leading-6 text-gray-600">For collaborations, wholesale opportunities and other business enquiries.</p>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-gray-950">
                        Start a conversation
                        <span aria-hidden="true" class="kp-card-arrow">→</span>
                    </span>
                </a>

            </div>
        </div>
    </section>

    {{-- ── Message form + info panel ───────────────────────────────── --}}
    <section id="message" class="border-t border-gray-200 bg-gray-50">
        <div class="kp-reveal mx-auto grid max-w-[1400px] gap-10 px-6 py-16 sm:px-10 lg:grid-cols-[.75fr_1.25fr] lg:gap-14 lg:px-14 lg:py-24 xl:px-20">

            {{-- Info panel --}}
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-emerald-600">Get in touch</p>
                <h2 class="mt-3 text-4xl font-black tracking-[-0.04em] sm:text-5xl">Tell us what you need.</h2>
                <p class="mt-5 max-w-md text-sm leading-7 text-gray-600 sm:text-base">
                    Choose the enquiry type that best matches your message and give us enough detail to understand how we can help.
                </p>

                <div class="mt-9 divide-y divide-gray-200 rounded-2xl border border-gray-200 bg-white">
                    <a href="tel:+255700123456" class="group flex items-center gap-4 px-5 py-4 transition hover:bg-emerald-50/60">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <x-tabler-phone size="20" stroke-width="1.8" />
                        </span>
                        <span>
                            <span class="block text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500">Call us</span>
                            <span class="mt-1 block text-sm font-semibold text-gray-950 group-hover:text-emerald-600">+255 700 123 456</span>
                        </span>
                    </a>

                    <a href="mailto:hello@kpwear.co.tz" class="group flex items-center gap-4 px-5 py-4 transition hover:bg-emerald-50/60">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <x-tabler-mail size="20" stroke-width="1.8" />
                        </span>
                        <span>
                            <span class="block text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500">Email us</span>
                            <span class="mt-1 block text-sm font-semibold text-gray-950 group-hover:text-emerald-600">hello@kpwear.co.tz</span>
                        </span>
                    </a>

                    <div class="flex items-center gap-4 px-5 py-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <x-tabler-map-pin size="20" stroke-width="1.8" />
                        </span>
                        <span>
                            <span class="block text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500">Based in</span>
                            <span class="mt-1 block text-sm font-semibold text-gray-950">Dar es Salaam, Tanzania</span>
                        </span>
                    </div>

                    <div class="flex items-center gap-4 px-5 py-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <x-tabler-clock size="20" stroke-width="1.8" />
                        </span>
                        <span>
                            <span class="block text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500">Hours</span>
                            <span class="mt-1 block text-sm font-semibold text-gray-950">Mon–Sat, 9:00–18:00 EAT</span>
                        </span>
                    </div>
                </div>

                {{-- Social links --}}
                <div class="mt-6 flex items-center gap-3">
                    <a href="#" aria-label="Instagram" class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 transition hover:border-emerald-300 hover:text-emerald-600">
                        <x-tabler-brand-instagram size="18" stroke-width="1.8" />
                    </a>
                    <a href="#" aria-label="Facebook" class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 transition hover:border-emerald-300 hover:text-emerald-600">
                        <x-tabler-brand-facebook size="18" stroke-width="1.8" />
                    </a>
                    <a href="#" aria-label="X (Twitter)" class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 transition hover:border-emerald-300 hover:text-emerald-600">
                        <x-tabler-brand-x size="18" stroke-width="1.8" />
                    </a>
                </div>
            </div>

            {{-- Form --}}
            <form data-contact-form class="h-fit rounded-3xl border border-gray-200 bg-white p-6 shadow-[0_16px_48px_rgba(0,0,0,0.04)] sm:p-8">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-gray-400">Send a message</p>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-[0.16em] text-gray-600">Name</span>
                        <input type="text" name="name" autocomplete="name" required class="kp-input mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3.5 text-sm text-gray-950 placeholder:text-gray-400" placeholder="Your name">
                    </label>
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-[0.16em] text-gray-600">Email</span>
                        <input type="email" name="email" autocomplete="email" required spellcheck="false" class="kp-input mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3.5 text-sm text-gray-950 placeholder:text-gray-400" placeholder="you@example.com">
                    </label>
                </div>
                <label class="mt-5 block">
                    <span class="text-xs font-bold uppercase tracking-[0.16em] text-gray-600">Enquiry type</span>
                    <select name="type" class="kp-input mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3.5 text-sm text-gray-950">
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
                    <span class="text-xs font-bold uppercase tracking-[0.16em] text-gray-600">Message</span>
                    <textarea name="message" rows="6" required class="kp-input mt-2 w-full resize-y rounded-xl border border-gray-200 bg-white px-4 py-3.5 text-sm text-gray-950 placeholder:text-gray-400" placeholder="How can we help?"></textarea>
                </label>
                <p data-contact-feedback class="mt-4 hidden rounded-xl px-4 py-3 text-sm" role="status" aria-live="polite"></p>
                <button data-contact-submit type="submit" class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 py-3.5 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto">
                    Send message
                    <span aria-hidden="true">→</span>
                </button>
                <p class="mt-4 flex items-center gap-1.5 text-xs text-gray-400">
                    <x-tabler-shield-check size="14" class="text-gray-400" />
                    Your details are only used to respond to your enquiry.
                </p>
            </form>
        </div>
    </section>

    {{-- ── FAQ ──────────────────────────────────────────────────────── --}}
    <section class="border-t border-gray-200 bg-white">
        <div class="mx-auto max-w-[1000px] px-6 py-16 sm:px-10 lg:py-20">
            <div class="mb-10 max-w-xl">
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-emerald-600">Before you write in</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">Common questions</h2>
            </div>

            <div class="divide-y divide-gray-200 rounded-2xl border border-gray-200">
                <details class="kp-faq group p-5 sm:p-6">
                    <summary class="flex items-center justify-between gap-4">
                        <span class="text-sm font-semibold text-gray-950 sm:text-base">How long does delivery take?</span>
                        <x-tabler-chevron-down size="18" class="kp-faq-chevron flex-shrink-0 text-gray-400" />
                    </summary>
                    <p class="mt-3 text-sm leading-6 text-gray-600">Delivery time depends on your location and is calculated at checkout. Orders within Dar es Salaam typically arrive faster than upcountry deliveries.</p>
                </details>

                <details class="kp-faq group p-5 sm:p-6">
                    <summary class="flex items-center justify-between gap-4">
                        <span class="text-sm font-semibold text-gray-950 sm:text-base">Can I return or exchange an item?</span>
                        <x-tabler-chevron-down size="18" class="kp-faq-chevron flex-shrink-0 text-gray-400" />
                    </summary>
                    <p class="mt-3 text-sm leading-6 text-gray-600">Yes — eligible items can be returned or exchanged within 7 days of delivery. Start a request from your account under Returns &amp; Support.</p>
                </details>

                <details class="kp-faq group p-5 sm:p-6">
                    <summary class="flex items-center justify-between gap-4">
                        <span class="text-sm font-semibold text-gray-950 sm:text-base">How do I track my order?</span>
                        <x-tabler-chevron-down size="18" class="kp-faq-chevron flex-shrink-0 text-gray-400" />
                    </summary>
                    <p class="mt-3 text-sm leading-6 text-gray-600">Once your order is placed, you can follow its status from your account under My Orders, or use the order number we send you.</p>
                </details>

                <details class="kp-faq group p-5 sm:p-6">
                    <summary class="flex items-center justify-between gap-4">
                        <span class="text-sm font-semibold text-gray-950 sm:text-base">Do you offer wholesale or collaborations?</span>
                        <x-tabler-chevron-down size="18" class="kp-faq-chevron flex-shrink-0 text-gray-400" />
                    </summary>
                    <p class="mt-3 text-sm leading-6 text-gray-600">Yes — select "Wholesale" or "Collaboration" from the enquiry type above and tell us a bit about what you have in mind.</p>
                </details>
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
      const response = await fetch('/api/v1/contact', { method:'POST', headers:{Accept:'application/json','Content-Type':'application/json'}, body:JSON.stringify({name:fd.get('name'),email:fd.get('email'),type:fd.get('type'),message:fd.get('message')}) });
      const data = await response.json().catch(() => null);
      if (!response.ok) throw new Error(data?.message || Object.values(data?.errors || {}).flat?.()?.[0] || `Request failed (${response.status})`);
      feedback.textContent = data?.message || 'Your message has been sent.';
      feedback.className = 'mt-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700';
      form.reset();
    } catch (error) {
      feedback.textContent = error.message || 'Unable to send your message right now.';
      feedback.className = 'mt-4 rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700';
    } finally { button.disabled=false; button.innerHTML=original; }
  });
})();
</script>
<script nonce="{{ Vite::cspNonce() }}">
(function () {
  const reveals  = document.querySelectorAll('.kp-reveal');
  const staggers = document.querySelectorAll('.kp-stagger');

  if (!('IntersectionObserver' in window)) {
    reveals.forEach(el => el.classList.add('is-visible'));
    staggers.forEach(el => el.classList.add('is-visible'));
    return;
  }

  const obs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        obs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });

  reveals.forEach(el => obs.observe(el));
  staggers.forEach(el => obs.observe(el));
})();
</script>
@endpush
