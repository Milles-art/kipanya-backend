@extends('layouts.app')
@section('content')
<div data-account-addresses class="mx-auto max-w-7xl px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[256px_minmax(0,1fr)]">
        @include('components.account-sidebar')
        <section class="min-w-0">
            <div class="border-b border-gray-100 pb-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Your account</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-950">Addresses</h1>
                <p class="mt-2 text-sm text-gray-500">Save delivery addresses for faster checkout.</p>
            </div>
            <div data-address-error class="mt-6 hidden rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert"></div>
            <div data-addresses-list class="mt-7 grid gap-4 md:grid-cols-2"><p class="text-sm text-gray-500">Loading…</p></div>
            <form data-address-form class="mt-8 hidden max-w-3xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-7">
                <div class="flex items-start justify-between gap-4"><div><h2 data-address-form-title class="text-lg font-bold">Add address</h2><p class="mt-1 text-sm text-gray-500">Use this address for delivery.</p></div><button type="button" data-address-cancel class="text-sm font-medium text-gray-500 hover:text-gray-900">Cancel</button></div>
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
                    <label class="flex items-center gap-2 text-sm text-gray-600 sm:col-span-2"><input name="is_default" type="checkbox" class="rounded border-gray-300"> Make this my default shipping address</label>
                </div>
                <button type="submit" class="button-dark mt-6 inline-flex items-center justify-center px-6">Save address</button>
            </form>
            <button data-address-add type="button" class="button-dark mt-7 inline-flex items-center gap-2 rounded-full px-6"><x-tabler-plus size="16" /> Add address</button>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const page = document.querySelector('[data-account-addresses]'); if (!page) return;
    const token = localStorage.getItem('kp_api_token'); if (!token) { location.href = '/login'; return; }
    const list = page.querySelector('[data-addresses-list]'), form = page.querySelector('[data-address-form]'), error = page.querySelector('[data-address-error]');
    const add = page.querySelector('[data-address-add]'), cancel = page.querySelector('[data-address-cancel]'), title = page.querySelector('[data-address-form-title]');
    const api = async (path, options = {}) => { const response = await fetch(`/api/v1${path}`, { ...options, headers: { Accept:'application/json', 'Content-Type':'application/json', Authorization:`Bearer ${token}`, ...(options.headers||{}) } }); const data = await response.json().catch(()=>null); if(!response.ok) throw new Error(data?.message || Object.values(data?.errors||{}).flat?.()?.[0] || `Request failed (${response.status})`); return data; };
    const field = n => form.querySelector(`[name="${n}"]`);
    const showError = e => { error.textContent = e.message || 'Unable to update addresses.'; error.classList.remove('hidden'); };
    const hideError = () => error.classList.add('hidden');
    const openForm = (address=null) => { hideError(); form.classList.remove('hidden'); add.classList.add('hidden'); title.textContent = address ? 'Edit address' : 'Add address'; ['id','label','recipient_name','phone','region','district','ward','street','notes'].forEach(n => field(n).value = address?.[n] || ''); field('is_default').checked = Boolean(address?.is_default); form.scrollIntoView({behavior:'smooth', block:'start'}); };
    const closeForm = () => { form.reset(); field('id').value=''; form.classList.add('hidden'); add.classList.remove('hidden'); };
    const render = addresses => { if(!addresses.length){ list.innerHTML='<div class="md:col-span-2 rounded-2xl border border-dashed border-gray-200 bg-gray-50/60 px-6 py-14 text-center text-sm text-gray-500">No saved addresses yet.</div>'; return; } list.innerHTML = addresses.map(a => `<article class="rounded-2xl border ${a.is_default?'border-emerald-200':'border-gray-200'} bg-white p-5 shadow-sm"><div class="flex items-start justify-between gap-4"><div><p class="text-xs font-semibold uppercase tracking-wider text-gray-400">${a.label ? escapeHtml(a.label) : 'Shipping address'}</p><h2 class="mt-1 font-bold text-gray-950">${escapeHtml(a.recipient_name)}</h2></div>${a.is_default?'<span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Default</span>':''}</div><p class="mt-4 text-sm leading-6 text-gray-600">${escapeHtml(a.phone)}<br>${escapeHtml(a.street)}<br>${escapeHtml([a.ward,a.district,a.region].filter(Boolean).join(', '))}</p>${a.notes?`<p class="mt-3 text-xs text-gray-400">${escapeHtml(a.notes)}</p>`:''}<div class="mt-5 flex gap-4 border-t border-gray-100 pt-4"><button type="button" data-edit-address="${a.id}" class="text-sm font-semibold text-gray-700 hover:text-emerald-700">Edit</button><button type="button" data-delete-address="${a.id}" class="text-sm font-semibold text-rose-600 hover:text-rose-700">Delete</button></div></article>`).join(''); };
    const load = async () => { try { const data=await api('/addresses'); render(data.data||[]); } catch(e){ showError(e); list.innerHTML=''; } };
    add.addEventListener('click',()=>openForm()); cancel.addEventListener('click',closeForm);
    list.addEventListener('click', async e => { const edit=e.target.closest('[data-edit-address]'), del=e.target.closest('[data-delete-address]'); if(edit){ try{ const data=await api('/addresses'); const a=(data.data||[]).find(x=>String(x.id)===String(edit.dataset.editAddress)); if(a) openForm(a); }catch(err){showError(err);} } if(del){ if(!confirm('Delete this address?')) return; del.disabled=true; try{await api(`/addresses/${del.dataset.deleteAddress}`,{method:'DELETE'}); await load();}catch(err){showError(err); del.disabled=false;} } });
    form.addEventListener('submit', async e => { e.preventDefault(); hideError(); const submit=form.querySelector('button[type="submit"]'); submit.disabled=true; const body={type:'shipping',label:field('label').value.trim()||null,recipient_name:field('recipient_name').value.trim(),phone:field('phone').value.trim(),region:field('region').value.trim(),district:field('district').value.trim(),ward:field('ward').value.trim()||null,street:field('street').value.trim(),notes:field('notes').value.trim()||null,is_default:field('is_default').checked}; try{const id=field('id').value; await api(id?`/addresses/${id}`:'/addresses',{method:id?'PUT':'POST',body:JSON.stringify(body)}); closeForm(); await load(); window.dispatchEvent(new CustomEvent('kp:toast',{detail:'Address saved.'}));}catch(err){showError(err);}finally{submit.disabled=false;} });
    load();
    function escapeHtml(value){return String(value??'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'",'&#039;');}
})();
</script>
@endpush
