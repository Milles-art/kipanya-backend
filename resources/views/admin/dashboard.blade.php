@extends('admin.layouts.app', ['title' => 'Dashboard', 'heading' => 'Control Panel'])

@section('content')
<div class="mb-8">
    <p class="max-w-2xl text-sm leading-6 text-gray-500">One command center for the entire Kipanya platform. Kipanya Wear is the first commerce app being taken to full operational readiness.</p>
</div>
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ([['Users','users'],['Cartoon Categories','categories'],['Cartoons','cartoons'],['Published Cartoons','published_cartoons'],['Draft Cartoons','draft_cartoons'],['Episodes','episodes'],['Collections','collections']] as [$label,$key])
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">{{ $label }}</p><p id="stat-{{ $key }}" class="mt-2 text-3xl font-black">—</p>
        </div>
    @endforeach
</div>
<div class="mt-8 grid gap-4 lg:grid-cols-5">
    @foreach ([['cartoon','Cartoon','Content management'],['wear','Kipanya Wear','Products, inventory, drops & orders'],['book','Kipanya Book','Book operations'],['motors','Kaypee Motors','Vehicle & lead operations'],['tv','Kipanya TV','Video & sync operations']] as [$slug,$name,$desc])
        <a href="{{ route('admin.module',$slug) }}" class="rounded-2xl border border-gray-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-sm">
            <p class="text-xs font-bold uppercase tracking-widest text-gray-400">App</p><h2 class="mt-2 font-bold">{{ $name }}</h2><p class="mt-1 text-sm leading-5 text-gray-500">{{ $desc }}</p>
        </a>
    @endforeach
</div>
<script>
(async()=>{
 const token=document.querySelector('meta[name="csrf-token"]')?.content;
 const response=await fetch('/api/v1/admin/dashboard',{headers:{Accept:'application/json'}});
 if(!response.ok)return; const json=await response.json(); const data=json.data||{};
 Object.entries(data).forEach(([key,value])=>{const el=document.getElementById('stat-'+key);if(el)el.textContent=value;});
})();
</script>
@endsection
