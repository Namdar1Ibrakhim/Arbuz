<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Cart;

class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return User
     */
    public function login(Request $request){
        try{
            $validateUser = Validator::make($request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'

                ]);
            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            $formFields = $request->only(['email', 'password']);

            if(!Auth::attempt($formFields)){
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            return response()->json([
                'status' => true,
                'message' => 'Success login',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);


        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }

    }

    public function register(Request $request){
        try{
            $validateUser = Validator::make($request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|email',
                    'password' => 'required'
                ]);
            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            $user = User::create([
                'name' => $request->name,
                'email'=> $request->email,
                'password' => $request->password
            ]);
            $cart = new Cart;
            $cart -> user_id = $user->id;
            $cart->save();

            return response()->json([
                'status' => true,
                'message' => 'Success registration',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 401);
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
