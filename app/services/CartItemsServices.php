<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItems;
use App\Repository\cartRepository;
session_start();

use App\Http\Controllers\api\apiResponse;
use App\Models\products;

class CartItemsServices
{
    use apiResponse;
    private $cart;

    public function __construct(cartRepository $cart)
    {
        $this->cart = $cart;
    }

    public function get_cart_items()
    {
        return $this->cart->get_cart_items();
    }

    public function add_to_cart()
    {
        $product = Products::find(request('products_id'));

        if (!$product) {
            return $this->apiResponse(null, 'Product Not Found', 404);
        }

        $cart = Cart::where('user_id', auth()->user()->id)->first();
        if (!$cart)
        {
            $validated = request()->validate([
                'quantity' => 'required',
                'products_id' => 'required',
            ]);
            if($validated){
            $cart = Cart::create([
                'user_id' => auth()->user()->id,
                'quantity' => request('quantity')
            ]);
            $cartitems = CartItems::create([
                    'cart_id' => $cart->id,
                    'products_id' => request('products_id'),
            ]);

            }

            return $this->apiResponse(  $cart->products(), 'Product Added To Cart Successfully', 200);
        }
        else
        {
            if(isset(CartItems::where('cart_id', $cart->id)->where('products_id', request('products_id'))->first()->id))
            {
            // add +1 to qunatity
            $cart->quantity +=1;
            $cart->save();
            //get cart items;
            return $this->apiResponse($cart, 'Product qunatity plused', 200);
            }
            else{
                CartItems::create([
                    'cart_id' => $cart->id,
                    'products_id' => request('products_id'),
                ]);
                return $this->apiResponse($cart, 'Product Added To Cart Successfully', 200);
            }
        }

    }


    public function remove_from_cart()
    {
        // remove sigle product from cart
        $cart = $this->cart->find_user_cart();
        $cartitems = CartItems::where('cart_id', $cart->id)->where('products_id', request('product_id'))->first();
        if(!$cartitems)
        {
            return $this->apiResponse(null, 'Product Not Found In Cart', 404);
        }
        else
        {
        $cartitems->delete();
        return $this->apiResponse([], 'Product Removed From Cart Successfully', 200);
        }
    }

    public function clear_cart()
    {
        $cart = Cart::where('user_id', auth()->user()->id)->first();
        if(!$cart)
        {
            return $this->apiResponse(null, 'Cart Not Found', 404);
        }
        else{
        $cart->delete();
        return $this->apiResponse(null, 'Cart Cleared Successfully', 200);
        }
    }

}
