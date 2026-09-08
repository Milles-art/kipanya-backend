<?php
namespace App\Http\Requests\Api\V1\Commerce;
use Illuminate\Foundation\Http\FormRequest;
class AddressRequest extends FormRequest { public function authorize():bool{return true;} public function rules():array{return ['type'=>['required','in:shipping,billing'],'label'=>['nullable','string','max:50'],'recipient_name'=>['required','string','max:120'],'phone'=>['required','string','max:20'],'region'=>['required','string','max:80'],'district'=>['required','string','max:80'],'ward'=>['nullable','string','max:80'],'street'=>['required','string','max:160'],'notes'=>['nullable','string','max:500'],'is_default'=>['sometimes','boolean']];} }
