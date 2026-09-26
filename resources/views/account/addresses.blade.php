@extends('layouts.app')
@section('content')
<div data-account-addresses class="w-full px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[300px_minmax(0,1fr)]">
        @include('components.account.sidebar')
        <section class="min-w-0">
            <div class="border-b border-emerald-950/10 pb-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Your account</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-black">Addresses</h1>
                <p class="mt-2 text-sm text-black">Save delivery addresses for faster checkout.</p>
            </div>
            <div data-address-error class="mt-6 hidden rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert"></div>
            <div data-addresses-list class="mt-7 grid gap-4 md:grid-cols-2">
                @forelse($addresses as $address)
                <article class="rounded-2xl border {{ $address->is_default ? 'border-emerald-200' : 'border-emerald-950/12' }} bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-black">{{ $address->label ?: 'Shipping address' }}</p>
                            <h2 class="mt-1 font-bold text-black">{{ $address->recipient_name }}</h2>
                        </div>
                        @if($address->is_default)<span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Default</span>@endif
                    </div>
                    <p class="mt-4 text-sm leading-6 text-black">{{ $address->phone }}<br>{{ $address->street }}<br>{{ collect([$address->ward, $address->district, $address->region])->filter()->implode(', ') }}</p>
                    @if($address->notes)<p class="mt-3 text-xs text-black">{{ $address->notes }}</p>@endif
                    <div class="mt-5 flex gap-4 border-t border-emerald-950/10 pt-4">
                        <button type="button" data-edit-address="{{ $address->id }}" class="text-sm font-semibold text-black hover:text-emerald-700">Edit</button>
                        <button type="button" data-delete-address="{{ $address->id }}" class="text-sm font-semibold text-rose-600 hover:text-rose-700">Delete</button>
                    </div>
                </article>
                @empty
                <div class="md:col-span-2 rounded-2xl border border-dashed border-emerald-950/12 bg-emerald-50/50/60 px-6 py-14 text-center text-sm text-black">No saved addresses yet.</div>
                @endforelse
            </div>
            <form data-address-form class="mt-8 hidden max-w-3xl rounded-2xl border border-emerald-950/12 bg-white p-6 shadow-sm sm:p-7">
                <div class="flex items-start justify-between gap-4"><div><h2 data-address-form-title class="text-lg font-bold">Add address</h2><p class="mt-1 text-sm text-black">Use this address for delivery.</p></div><button type="button" data-address-cancel class="text-sm font-medium text-black hover:text-black">Cancel</button></div>
                <input type="hidden" name="id">
                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <input name="label" class="field" placeholder="Label (e.g. Home)">
                    <input name="recipient_name" class="field" placeholder="Recipient name" required>
                    <input name="phone" class="field" placeholder="Phone number" required>
                    <input name="region" class="field" placeholder="Region" required>
                    <input name="district" class="field" placeholder="District" required>
                    <input name="ward" class="field" placeholder="Ward">
                    <input name="street" class="field sm:col-span-2" placeholder="Street / house address" required>
                    <textarea name="notes" class="field sm:col-span-2 min-h-24" placeholder="Delivery notes (optional)"></textarea>
                    <label class="flex items-center gap-2 text-sm text-black sm:col-span-2"><input name="is_default" type="checkbox" class="rounded border-black"> Make this my default shipping address</label>
                </div>
                <button type="submit" class="button-dark mt-6 inline-flex items-center justify-center px-6">Save address</button>
            </form>
            <button data-address-add type="button" class="button-dark mt-7 px-6"><x-tabler-plus size="16" /> Add address</button>
        </section>
    </div>
</div>

{{-- Custom destructive confirmation modal --}}
<div data-address-delete-modal class="fixed inset-0 z-[80] hidden items-end justify-center bg-black/50 p-4 sm:items-center" role="dialog" aria-modal="true" aria-labelledby="address-delete-title" aria-describedby="address-delete-description">
    <div data-address-delete-panel class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl sm:p-7">
        <div class="flex items-start gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">
                <x-tabler-trash size="20" />
            </div>
            <div class="min-w-0">
                <h2 id="address-delete-title" class="text-lg font-bold text-black">Delete this address?</h2>
                <p id="address-delete-description" class="mt-1.5 text-sm leading-6 text-black">This saved delivery address will be permanently removed.</p>
            </div>
        </div>
        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button type="button" data-address-delete-cancel class="kp-button-secondary min-h-11 rounded-xl px-5 py-3 text-sm font-semibold">Cancel</button>
            <button type="button" data-address-delete-confirm class="min-h-11 rounded-xl bg-black px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">Delete address</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ Vite::cspNonce() }}">
(() => {
    const page = document.querySelector('[data-account-addresses]'); if (!page) return;
    if (document.querySelector('meta[name="kp-signed-in"]')?.getAttribute('content') !== '1') { location.href = '/login'; return; }
    const list = page.querySelector('[data-addresses-list]'), form = page.querySelector('[data-address-form]'), error = page.querySelector('[data-address-error]');
    const add = page.querySelector('[data-address-add]'), cancel = page.querySelector('[data-address-cancel]'), title = page.querySelector('[data-address-form-title]');
    const deleteModal = page.querySelector('[data-address-delete-modal]');
    const deleteCancel = deleteModal?.querySelector('[data-address-delete-cancel]');
    const deleteConfirm = deleteModal?.querySelector('[data-address-delete-confirm]');
    let pendingDeleteId = null;
    let deleteTrigger = null;
    const api = async (path, options = {}) => { const response = await fetch(`/api/v1${path}`, { ...options, headers: { Accept:'application/json', 'Content-Type':'application/json', ...(options.headers||{}) } }); const data = await response.json().catch(()=>null); if(!response.ok){ if(response.status===401){ location.href='/login'; return null; } throw new Error(data?.message || Object.values(data?.errors||{}).flat?.()?.[0] || `Request failed (${response.status})`); } return data; };
    const field = n => form.querySelector(`[name="${n}"]`);
    const showError = e => { error.textContent = e.message || 'Unable to update addresses.'; error.classList.remove('hidden'); };
    const hideError = () => error.classList.add('hidden');
    const openForm = (address=null) => { hideError(); form.classList.remove('hidden'); add.classList.add('hidden'); title.textContent = address ? 'Edit address' : 'Add address'; ['id','label','recipient_name','phone','region','district','ward','street','notes'].forEach(n => field(n).value = address?.[n] || ''); field('is_default').checked = Boolean(address?.is_default); form.scrollIntoView({behavior:'smooth', block:'start'}); };
    const closeForm = () => { form.reset(); field('id').value=''; form.classList.add('hidden'); add.classList.remove('hidden'); };
    const render = addresses => { if(!addresses.length){ list.innerHTML='<div class="md:col-span-2 rounded-2xl border border-dashed border-emerald-950/12 bg-emerald-50/50/60 px-6 py-14 text-center text-sm text-black">No saved addresses yet.</div>'; return; } list.innerHTML = addresses.map(a => `<article class="rounded-2xl border ${a.is_default?'border-emerald-200':'border-emerald-950/12'} bg-white p-5 shadow-sm"><div class="flex items-start justify-between gap-4"><div><p class="text-xs font-semibold uppercase tracking-wider text-black">${a.label ? escapeHtml(a.label) : 'Shipping address'}</p><h2 class="mt-1 font-bold text-black">${escapeHtml(a.recipient_name)}</h2></div>${a.is_default?'<span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Default</span>':''}</div><p class="mt-4 text-sm leading-6 text-black">${escapeHtml(a.phone)}<br>${escapeHtml(a.street)}<br>${escapeHtml([a.ward,a.district,a.region].filter(Boolean).join(', '))}</p>${a.notes?`<p class="mt-3 text-xs text-black">${escapeHtml(a.notes)}</p>`:''}<div class="mt-5 flex gap-4 border-t border-emerald-950/10 pt-4"><button type="button" data-edit-address="${a.id}" class="text-sm font-semibold text-black hover:text-emerald-700">Edit</button><button type="button" data-delete-address="${a.id}" class="text-sm font-semibold text-rose-600 hover:text-rose-700">Delete</button></div></article>`).join(''); };
    const load = async () => { try { const data=await api('/addresses'); render(data.data||[]); } catch(e){ showError(e); list.innerHTML=''; } };
    const closeDeleteModal = () => {
        if (!deleteModal) return;
        deleteModal.classList.add('hidden');
        deleteModal.classList.remove('flex');
        pendingDeleteId = null;
        deleteTrigger = null;
    };
    const openDeleteModal = trigger => {
        if (!deleteModal) return;
        pendingDeleteId = trigger.dataset.deleteAddress;
        deleteTrigger = trigger;
        deleteModal.classList.remove('hidden');
        deleteModal.classList.add('flex');
        requestAnimationFrame(() => deleteConfirm?.focus());
    };

    add.addEventListener('click',()=>openForm()); cancel.addEventListener('click',closeForm);
    list.addEventListener('click', async e => { const edit=e.target.closest('[data-edit-address]'), del=e.target.closest('[data-delete-address]'); if(edit){ try{ const data=await api('/addresses'); const a=(data.data||[]).find(x=>String(x.id)===String(edit.dataset.editAddress)); if(a) openForm(a); }catch(err){showError(err);} } if(del){ openDeleteModal(del); } });
    deleteCancel?.addEventListener('click', closeDeleteModal);
    deleteModal?.addEventListener('click', e => { if (e.target === deleteModal) closeDeleteModal(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && deleteModal && !deleteModal.classList.contains('hidden')) closeDeleteModal(); });
    deleteConfirm?.addEventListener('click', async () => {
        if (!pendingDeleteId || !deleteTrigger) return;
        deleteConfirm.disabled = true;
        deleteTrigger.disabled = true;
        try {
            await api(`/addresses/${pendingDeleteId}`, {method:'DELETE'});
            closeDeleteModal();
            await load();
            window.dispatchEvent(new CustomEvent('kp:toast',{detail:'Address deleted.'}));
        } catch(err) {
            showError(err);
            deleteTrigger.disabled = false;
        } finally {
            deleteConfirm.disabled = false;
        }
    });
    form.addEventListener('submit', async e => { e.preventDefault(); hideError(); const submit=form.querySelector('button[type="submit"]'); submit.disabled=true; const body={type:'shipping',label:field('label').value.trim()||null,recipient_name:field('recipient_name').value.trim(),phone:field('phone').value.trim(),region:field('region').value.trim(),district:field('district').value.trim(),ward:field('ward').value.trim()||null,street:field('street').value.trim(),notes:field('notes').value.trim()||null,is_default:field('is_default').checked}; try{const id=field('id').value; await api(id?`/addresses/${id}`:'/addresses',{method:id?'PUT':'POST',body:JSON.stringify(body)}); closeForm(); await load(); window.dispatchEvent(new CustomEvent('kp:toast',{detail:'Address saved.'}));}catch(err){showError(err);}finally{submit.disabled=false;} });
    load();
    function escapeHtml(value){return String(value??'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'",'&#039;');}
})();
</script>
@endpush
