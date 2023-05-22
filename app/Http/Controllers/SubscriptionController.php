<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartProduct;

use App\Models\Product;
use App\Models\Subscription;
use App\Models\SubscriptionProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function subscriptions(){
        $order = new Subscription;
        $id = Auth::user()->id;
        return response()->json($order->find($id), 200);
    }
    public function subscrProducts($id){
        return response()->json(SubscriptionProduct::select()->where('subscription_id',$id)->get());
    }

    public function makeSubscription(Request $request){
        $cart = Cart::select()->where('user_id',Auth::user()->id)->first();
        $cartProduct = CartProduct::select()->where('cart_id', $cart->id)->get();

        //Order for my cart

        foreach ($cartProduct as $targetprod) {
            $prod = Product::select()->where('id',$targetprod->product_id)->first();
            if(($targetprod->amount) * ($request->period) > ($prod->amount)){
                return response('Продукт #'. $prod->id . 'не хватит на указанный срок', 400);
            }
        }

        foreach ($cartProduct as $targetprod) {
            $prod = Product::select()->where('id',$targetprod->product_id)->first();
            $newamount = ($prod->amount) - (($targetprod->amount) * ($request->period));
            DB::update('update products set amount = ? where id = ?', [$newamount, $prod->id]);

        }

        $subscr = new Subscription;
        $subscr->user_id = Auth::user()->id;
        $subscr->totalPrice = $cart -> totalprice;
        $subscr->address = $request->address;
        $subscr->phone = $request->phone;
        $subscr->dayOfWeek = $request->dayOfWeek;
        $subscr->period = $request->period;
        $subscr -> save();


        foreach ($cartProduct as $target_result) {
            $orderproduct = new SubscriptionProduct;
            $orderproduct->subscription_id = $subscr->id;
            $orderproduct->product_id = $target_result->product_id;
            $orderproduct->amount = $target_result->amount;
            $orderproduct -> save();

        }
        DB::update('update carts set totalprice = 0 where id = ?',[$cart->id]);
        $cartProduct = CartProduct::select('id')->where('cart_id', $cart->id)->get();

        foreach ($cartProduct as $target_result) {
            DB::delete('delete from cart_products where id = ?',[$target_result->id]);
        }

        return response('Успешно сделан заказ', 200);

    }
}
