<?php

use App\Http\Controllers\SubscriptionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::resource('/categories',CategoryController::class);

Route::resource('/products',ProductController::class);


Route::name('carts.')->group(function(){
    Route::get('/carts', [CartController::class, 'cart'])->middleware('auth:sanctum');
    Route::get('/carts/products', [CartController::class, 'mycart'])->middleware('auth:sanctum');
    Route::post('/carts/add', [CartController::class, 'addtocart'])->middleware('auth:sanctum');
    Route::get('/carts/remove', [CartController::class, 'deletecart'])->middleware('auth:sanctum');
});

Route::name('subscription.')->group(function (){
    Route::get('subscriptions',[SubscriptionController::class, 'subscriptions'])->middleware('auth:sanctum');
    Route::get('subscriptions/{id}',[SubscriptionController::class, 'subscrProducts'])->middleware('auth:sanctum');
    Route::post('makeSubscription', [SubscriptionController::class, 'makeSubscription'])->middleware('auth:sanctum');
});
