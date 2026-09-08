<?php
namespace App\Http\Controllers\Api\V1\Commerce;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Commerce\AddressRequest;
use App\Http\Resources\Api\V1\AddressResource;
use App\Models\Commerce\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class AddressController extends Controller { public function index(Request $r){return AddressResource::collection($r->user()->addresses()->latest()->get());} public function store(AddressRequest $r){$data=$r->validated();$address=DB::transaction(function()use($r,$data){$a=$r->user()->addresses()->create($data);if(($data['is_default']??false))$r->user()->addresses()->whereKeyNot($a->id)->where('type',$a->type)->update(['is_default'=>false]);return $a;});return new AddressResource($address);} public function update(AddressRequest $r,Address $address){abort_unless($address->user_id===$r->user()->id,403);$data=$r->validated();$address=DB::transaction(function()use($r,$data,$address){$address->update($data);if(($data['is_default']??false))$r->user()->addresses()->whereKeyNot($address->id)->where('type',$address->type)->update(['is_default'=>false]);return $address;});return new AddressResource($address);} public function destroy(Request $r,Address $address){abort_unless($address->user_id===$r->user()->id,403);$address->delete();return response()->noContent();} }
