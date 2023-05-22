<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Product;
use App\Models\CartProduct;
use App\Models\Cart;

class CartController extends Controller
{
    /**
     * @param Request $request
     * @return User
     */

    public function cart()
    {
        $cart = Cart::select()->where('user_id', Auth::user()->id)->get();
        return response($cart, 200);
    }

    public function mycart()
    {
        $cart = Cart::select('id')->where('user_id', Auth::user()->id)->first()->id;
        $cartProduct = CartProduct::where('cart_id', $cart)->get();
        return response($cartProduct, 200);
    }

    public function addtocart(Request $request)
    {
        $cartid = Cart::select()->where('user_id', Auth::user()->id)->first();
        $set = Product::select()->where('id', $request->product_id)->get();
        $newprice = $cartid->totalprice;

        foreach ($set as $tar) {
            $newprice += (($tar->price) * ($request->amount));
        }
        $prod = Product::select()->where('id', $request->product_id)->first();

        $newamount = ($prod->amount) - ($request->amount);

        if($newamount<0){
            return response('Не хватает продуктов', 400);
        }
        DB::update('update carts set totalprice = ? where id = ?', [$newprice, $cartid->id]);
//        DB::update('update products set amount = ? where id = ?', [$newamount, $prod->id]);

        $cartProduct = CartProduct::create([
            'product_id' => $request->product_id,
            'amount' => $request->amount,
            'cart_id' => $cartid->id
        ]);
        return response('Успешно добавлено', 200);

    }

    public function deletecart()
    {
        $cart = Cart::select('id')->where('user_id', Auth::user()->id)->first()->id;
        DB::update('update carts set totalprice = 0 where id = ?', [$cart]);

        $cartProduct = CartProduct::select('id')->where('cart_id', $cart)->get();

        foreach ($cartProduct as $target_result) {
            DB::delete('delete from cart_products where id = ?', [$target_result->id]);
        }
        return response('Success', 200);

    }
}
