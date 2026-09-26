@extends('layouts.app')
@section('content')
<div class="w-full px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[300px_minmax(0,1fr)]">
        @include('components.account.sidebar')
        <section data-security-page class="space-y-6">
            <div class="border-b border-emerald-950/12 pb-6">
                <p class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-black"><x-tabler-shield-lock size="14" /> Account</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-black sm:text-3xl">Security &amp; login</h1>
                <p class="mt-2 text-sm text-black">Your KP Wear account uses your verified phone number and one-time verification codes to sign in.</p>
            </div>

            <section class="rounded-2xl border border-emerald-950/12 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50/70"><x-tabler-device-mobile size="18" class="text-black" /></div>
                    <div><p class="text-sm font-medium text-black">Verified phone number</p><p data-security-phone class="text-sm text-black">{{ $user->phone }}</p></div>
                </div>
                <div class="mt-5 rounded-xl bg-emerald-50/50 p-4 text-sm leading-6 text-black">Sign-in is protected by a one-time code sent to this number. Phone-number changes require a separate verified OTP workflow and are not exposed as a fake action here.</div>
            </section>

            <section class="rounded-2xl border border-emerald-950/12 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50/70"><x-tabler-lock size="18" class="text-black" /></div>
                    <div><p class="text-sm font-medium text-black">Active sessions</p><p class="text-sm text-black">Devices currently signed in to your account.</p></div>
                </div>
                <ul class="mt-4 space-y-3">
                    @forelse($sessions as $session)
                    <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-emerald-950/10 p-4">
                        <div>
                            <p class="text-sm font-medium text-black">{{ $session['device'] }}</p>
                            <p class="mt-0.5 text-xs text-black">Last active {{ $session['last_active'] instanceof \DateTimeInterface ? $session['last_active']->format('j M Y, H:i') : $session['last_active'] }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            @if($session['is_current'])<span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">This device</span>@endif
                            <button type="button" data-revoke-session data-session-id="{{ $session['id'] }}" class="rounded-full border border-rose-200 px-4 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50">Revoke</button>
                        </div>
                    </li>
                    @empty
                    <li class="rounded-xl border border-dashed border-emerald-950/12 p-4 text-sm text-black">No active sessions found.</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-2xl border border-emerald-950/12 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center gap-3"><div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50/70"><x-tabler-logout size="18" class="text-black" /></div><div><p class="text-sm font-medium text-black">Sign out</p><p class="text-sm text-black">End your current authenticated session.</p></div></div>
                <button data-logout type="button" class="mt-5 rounded-full border border-emerald-950/12 px-5 py-2.5 text-sm font-semibold text-black hover:bg-emerald-50/50">Sign out</button>
            </section>

            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 sm:p-6">
                <div class="flex items-start gap-3"><x-tabler-info-circle size="18" class="mt-0.5 flex-shrink-0 text-amber-600" /><div><p class="text-sm font-semibold text-amber-900">Account security controls</p><p class="mt-1 text-sm leading-6 text-amber-800">Session management, phone changes and account deletion are intentionally not presented as active controls until their verified backend workflows are available.</p></div></div>
            </section>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ Vite::cspNonce() }}">
(() => {
    const page=document.querySelector('[data-security-page]'); if(!page)return; if(document.querySelector('meta[name="kp-signed-in"]')?.getAttribute('content')!=='1'){location.href='/login';return;}
    fetch('/api/v1/auth/me',{headers:{Accept:'application/json'}}).then(async r=>{const d=await r.json().catch(()=>null);if(!r.ok){if(r.status===401){location.href='/login';return;}throw new Error(d?.message||'Session expired.');}page.querySelector('[data-security-phone]').textContent=d.data?.phone||'—';}).catch(e=>{page.querySelector('[data-security-phone]').textContent=e.message;});
    const getCookie=name=>{const m=document.cookie.match(new RegExp('(?:^|;\\s*)'+name+'=([^;]*)'));return m?decodeURIComponent(m[1]):null;};
    page.addEventListener('click',async e=>{
        const btn=e.target.closest('[data-revoke-session]'); if(!btn||btn.disabled)return;
        const id=btn.dataset.sessionId; if(!id)return;
        btn.disabled=true;
        try{
            const headers=new Headers({Accept:'application/json'});
            const xsrf=getCookie('XSRF-TOKEN'); if(xsrf)headers.set('X-XSRF-TOKEN',xsrf);
            const r=await fetch(`/api/v1/sessions/${encodeURIComponent(id)}`,{method:'DELETE',credentials:'include',headers});
            if(!r.ok){let m=`Request failed (${r.status})`; try{const d=await r.json(); m=d?.message||m;}catch{} throw new Error(m);}
            window.location.reload();
        }catch(err){btn.disabled=false;window.dispatchEvent(new CustomEvent('kp:toast',{detail:err.message||'Unable to revoke session.'}));}
    });
})();
</script>
@endpush
