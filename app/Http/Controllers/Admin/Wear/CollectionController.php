<?php
namespace App\Http\Controllers\Admin\Wear;

use App\Http\Controllers\Controller;
use App\Models\Wear\WearCollection;
use App\Models\Wear\WearProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class CollectionController extends Controller
{
    private function authorize(Request $request): void { abort_unless($request->user()?->hasPermission('commerce.manage'), 403); }
    public function index(Request $request): View
    {
        $this->authorize($request);
        $collections=WearCollection::query()->withCount('products')->when($request->filled('q'),fn($q)=>$q->where('name','like','%'.trim((string)$request->string('q')).'%'))->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString();
        return view('admin.wear.collections.index',compact('collections'));
    }
    public function create(Request $request): View { $this->authorize($request); return view('admin.wear.collections.form',['collection'=>new WearCollection(),'products'=>WearProduct::query()->where('is_active',true)->orderBy('name')->get(['id','name'])]); }
    public function store(Request $request): RedirectResponse
    {
        $this->authorize($request); $data=$this->validateData($request); $productIds=$data['product_ids']??[]; unset($data['product_ids']);
        if ($request->hasFile('cover_image')) { $data['cover_path']='/storage/'.$request->file('cover_image')->store('wear/collections','public'); }
        $collection=WearCollection::create($data); $this->sync($collection,$productIds);
        app(\App\Support\AuditLogger::class)->log($request,'admin.wear.collection.created',$collection,['product_ids'=>$productIds]);
        return redirect()->route('admin.wear.collections.index')->with('success','Collection created.');
    }
    public function edit(Request $request,WearCollection $collection): View { $this->authorize($request); $collection->load('products:id,name'); return view('admin.wear.collections.form',['collection'=>$collection,'products'=>WearProduct::query()->where('is_active',true)->orderBy('name')->get(['id','name'])]); }
    public function update(Request $request,WearCollection $collection): RedirectResponse
    {
        $this->authorize($request); $data=$this->validateData($request,$collection); $productIds=$data['product_ids']??[]; unset($data['product_ids']);
        if ($request->hasFile('cover_image')) { $old=$collection->cover_path; $data['cover_path']='/storage/'.$request->file('cover_image')->store('wear/collections','public'); $this->deleteStoredImage($old); }
        $collection->update($data); $this->sync($collection,$productIds);
        app(\App\Support\AuditLogger::class)->log($request,'admin.wear.collection.updated',$collection,['product_ids'=>$productIds]);
        return redirect()->route('admin.wear.collections.index')->with('success','Collection updated.');
    }
    public function destroy(Request $request,WearCollection $collection): RedirectResponse { $this->authorize($request); $old=$collection->cover_path; $collection->products()->detach(); $collection->delete(); $this->deleteStoredImage($old); app(\App\Support\AuditLogger::class)->log($request,'admin.wear.collection.deleted',null,['collection_id'=>$collection->id,'name'=>$collection->name]); return back()->with('success','Collection deleted.'); }
    private function validateData(Request $request,?WearCollection $collection=null): array { return $request->validate(['name'=>['required','string','max:180'],'slug'=>['nullable','string','max:200',Rule::unique('wear_collections','slug')->ignore($collection?->id)],'description'=>['nullable','string','max:5000'],'cover_path'=>['nullable','string','max:2048',function(string $attribute,mixed $value,$fail):void{ $value=trim((string)$value); if($value==='') return; if(str_contains($value,'..')||str_contains($value,'\\')){ $fail('The cover path is invalid.'); return; } if(str_starts_with($value,'/storage/')||str_starts_with($value,'assets/')) return; if(preg_match('/^https?:\/\//i',$value) && filter_var($value,FILTER_VALIDATE_URL)) return; $fail('The cover path must be a local storage/assets path or an HTTP(S) URL.'); }],'cover_image'=>['nullable','image','mimes:jpg,jpeg,png,webp,avif','max:5120'],'is_active'=>['sometimes','boolean'],'sort_order'=>['required','integer','min:0'],'product_ids'=>['nullable','array'],'product_ids.*'=>['integer','exists:wear_products,id']]); }
    private function sync(WearCollection $collection,array $ids): void { $sync=[]; foreach(array_values(array_unique(array_map('intval',$ids))) as $i=>$id)$sync[$id]=['sort_order'=>$i]; $collection->products()->sync($sync); }
    private function deleteStoredImage(?string $path): void { if(!is_string($path)||!str_starts_with($path,'/storage/')||str_contains($path,'..')||str_contains($path,'\\')) return; Storage::disk('public')->delete(ltrim(substr($path,9),'/')); }
}
