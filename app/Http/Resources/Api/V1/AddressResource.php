<?php
namespace App\Http\Resources\Api\V1;
use Illuminate\Http\Resources\Json\JsonResource;
class AddressResource extends JsonResource { public function toArray($request):array{return ['id'=>$this->id,'type'=>$this->type,'label'=>$this->label,'recipient_name'=>$this->recipient_name,'phone'=>$this->phone,'region'=>$this->region,'district'=>$this->district,'ward'=>$this->ward,'street'=>$this->street,'notes'=>$this->notes,'is_default'=>(bool)$this->is_default];} }
