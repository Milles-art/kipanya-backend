<?php
namespace App\Actions\Cart;
use App\Models\Cart\Cart;
use App\Models\Wear\WearProductVariant;
use App\Services\Cart\CartService;
final class AddCartItem { public function __construct(private readonly CartService $service){} public function execute(Cart $cart,WearProductVariant $variant,int $quantity):Cart{return $this->service->add($cart,$variant,$quantity);} }
