<?php
namespace App\Http\Requests\Api\V1\Cart;
use Illuminate\Foundation\Http\FormRequest;
class AddCartItemRequest extends FormRequest { public function authorize():bool{return true;} public function rules():array{return ['variant_id'=>['required','integer','exists:wear_product_variants,id'],'quantity'=>['required','integer','min:1','max:50']];} }
