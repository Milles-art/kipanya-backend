@extends('layouts.app')

@push('head')
<style>
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

  @media (prefers-reduced-motion: reduce) {
    .kp-reveal, .kp-stagger > * { opacity: 1 !important; transform: none !important; transition: none !important; }
    .kp-contact-card, .kp-contact-card .kp-card-icon, .kp-contact-card .kp-card-arrow,
    .kp-input { transition: none !important; }
  }
</style>
@endpush

@section('content')
<div class="bg-[#f7f4ed] text-gray-950">

    {{-- ── Hero ─────────────────────────────────────────────────── --}}
    <section class="relative overflow-hidden border-b border-gray-200">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_78%_18%,rgba(16,185,129,.15),transparent_30%),linear-gradient(135deg,#f7f4ed_0%,#fffdf8_58%,#f3eadb_100%)]"></div>
        <div class="relative mx-auto max-w-[1600px] px-6 py-20 sm:px-10 lg:px-14 lg:py-28 xl:px-20">
            <div class="max-w-4xl">
                <p class="text-xs font-bold uppercase tracking-[0.28em] text-emerald-600">Contact KP Wear</p>
                <h1 class="mt-4 text-5xl font-black tracking-[-0.05em] sm:text-6xl lg:text-8xl">
                    Let's talk.
                </h1>
                <p class="mt-7 max-w-2xl text-base leading-7 text-gray-700 sm:text-lg sm:leading-8">
                    Whether you have a question about a product, an order, sizing, returns, or working with KP Wear, we're here to hear from you.
                </p>
                <nav class="mt-9 flex flex-wrap gap-3 text-xs font-semibold uppercase tracking-[0.16em]" aria-label="Enquiry categories">
                    <a href="#customer-support" class="rounded-full border border-gray-300 px-4 py-2.5 transition hover:border-emerald-500 hover:text-emerald-600">Customer support</a>
                    <a href="#orders" class="rounded-full border border-gray-300 px-4 py-2.5 transition hover:border-emerald-500 hover:text-emerald-600">Orders</a>
                    <a href="#partnerships" class="rounded-full border border-gray-300 px-4 py-2.5 transition hover:border-emerald-500 hover:text-emerald-600">Partnerships</a>
                </nav>
            </div>
        </div>
    </section>

    {{-- ── Contact cards ─────────────────────────────────────────── --}}
    <section class="bg-white text-gray-950">
        <div class="kp-stagger mx-auto max-w-[1400px] px-6 py-16 sm:px-10 lg:px-14 lg:py-24 xl:px-20">
            <div class="grid gap-5 md:grid-cols-3">

                <a id="customer-support" href="#message" class="kp-contact-card group rounded-2xl border border-gray-200 bg-white p-7 shadow-sm hover:border-emerald-300 hover:shadow-lg">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 11.5a7.5 7.5 0 0 1-8 7.5 8.4 8.4 0 0 1-3.4-.7L4 20l1.5-4A7.3 7.3 0 0 1 4.5 12 7.5 7.5 0 0 1 12 4.5c4.1 0 8 2.9 8 7Z"/><path d="M8 12h.01M12 12h.01M16 12h.01"/></svg>
                    </span>
                    <p class="mt-6 text-[11px] font-bold uppercase tracking-[0.22em] text-gray-500">Customer support</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-tight">Need help?</h2>
                    <p class="mt-3 text-sm leading-6 text-gray-600">Questions about products, sizing, returns or your shopping experience.</p>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-gray-950">
                        Send an enquiry
                        <span aria-hidden="true" class="kp-card-arrow">→</span>
                    </span>
                </a>

                <a id="orders" href="#message" class="kp-contact-card group rounded-2xl border border-gray-200 bg-white p-7 shadow-sm hover:border-emerald-300 hover:shadow-lg">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 7.5h14v12H5z"/><path d="M8 7.5V6a4 4 0 0 1 8 0v1.5M9 11h6"/></svg>
                    </span>
                    <p class="mt-6 text-[11px] font-bold uppercase tracking-[0.22em] text-gray-500">Orders</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-tight">Order enquiries</h2>
                    <p class="mt-3 text-sm leading-6 text-gray-600">Need help with an existing order? Include your order number so we can identify it quickly.</p>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-gray-950">
                        Ask about an order
                        <span aria-hidden="true" class="kp-card-arrow">→</span>
                    </span>
                </a>

                <a id="partnerships" href="#message" class="kp-contact-card group rounded-2xl border border-gray-200 bg-white p-7 shadow-sm hover:border-emerald-300 hover:shadow-lg">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m8.5 12.5 2 2a2.8 2.8 0 0 0 4 0l2-2"/><path d="m10 10 1.7-1.7a2.8 2.8 0 0 1 4 0l2 2a2.8 2.8 0 0 1 0 4"/><path d="m14 14-1.7 1.7a2.8 2.8 0 0 1-4 0l-2-2a2.8 2.8 0 0 1 0-4l2-2a2.8 2.8 0 0 1 4 0"/></svg>
                    </span>
                    <p class="mt-6 text-[11px] font-bold uppercase tracking-[0.22em] text-gray-500">Business</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-tight">Work with us</h2>
                    <p class="mt-3 text-sm leading-6 text-gray-600">For collaborations, wholesale opportunities and other business enquiries.</p>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-gray-950">
                        Start a conversation
                        <span aria-hidden="true" class="kp-card-arrow">→</span>
                    </span>
                </a>

            </div>
        </div>
    </section>

    {{-- ── Message form ────────────────────────────────────────────── --}}
    <section id="message" class="border-t border-gray-200 bg-[#efe9dd]">
        <div class="kp-reveal mx-auto grid max-w-[1400px] gap-12 px-6 py-16 sm:px-10 lg:grid-cols-[.8fr_1.2fr] lg:px-14 lg:py-24 xl:px-20">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.28em] text-emerald-600">Get in touch</p>
                <h2 class="mt-4 text-4xl font-black tracking-[-0.04em] sm:text-5xl">Tell us what you need.</h2>
                <p class="mt-5 max-w-md text-sm leading-7 text-gray-600 sm:text-base">
                    Choose the enquiry type that best matches your message and give us enough detail to understand how we can help.
                </p>
                <div class="mt-9 border-l-2 border-emerald-500 pl-5">
                    <p class="text-sm font-semibold text-gray-950">KP Wear</p>
                    <p class="mt-1 text-sm leading-6 text-gray-600">Everyday clothing made for movement, comfort and confidence.</p>
                </div>

                <div class="mt-8 space-y-3">
                    <a href="tel:+255700123456" class="group flex items-center gap-4 rounded-2xl border border-gray-200 bg-white px-4 py-4 transition hover:border-emerald-400 hover:bg-emerald-50">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6.7 4.5 9 3.5l2 4.8-1.9 1.5a14.2 14.2 0 0 0 5.1 5.1l1.5-1.9 4.8 2-1 2.3a2 2 0 0 1-2.2 1.2C10.7 17.4 6.6 13.3 5.5 6.7a2 2 0 0 1 1.2-2.2Z"/></svg>
                        </span>
                        <span>
                            <span class="block text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500">Call us</span>
                            <span class="mt-1 block text-sm font-semibold text-gray-950 group-hover:text-emerald-600">+255 700 123 456</span>
                        </span>
                    </a>

                    <a href="mailto:hello@kpwear.co.tz" class="group flex items-center gap-4 rounded-2xl border border-gray-200 bg-white px-4 py-4 transition hover:border-emerald-400 hover:bg-emerald-50">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3.5" y="5" width="17" height="14" rx="2"/><path d="m5 7 7 5 7-5"/></svg>
                        </span>
                        <span>
                            <span class="block text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500">Email us</span>
                            <span class="mt-1 block text-sm font-semibold text-gray-950 group-hover:text-emerald-600">hello@kpwear.co.tz</span>
                        </span>
                    </a>
                </div>
            </div>

            <form data-contact-form class="rounded-3xl border border-gray-200 bg-white p-6 shadow-[0_16px_48px_rgba(0,0,0,0.04)] sm:p-8">
                <div class="grid gap-5 sm:grid-cols-2">
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
                <button data-contact-submit type="submit" class="mt-6 inline-flex items-center justify-center rounded-xl bg-emerald-600 px-6 py-3.5 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60">
                    Send message
                </button>
            </form>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
  const form = document.querySelector('[data-contact-form]');
  if (!form) return;
  const button = form.querySelector('[data-contact-submit]');
  const feedback = form.querySelector('[data-contact-feedback]');
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    feedback.className = 'mt-4 hidden rounded-xl px-4 py-3 text-sm';
    button.disabled = true;
    const original = button.textContent;
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
    } finally { button.disabled=false; button.textContent=original; }
  });
})();
</script>
<script>
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
