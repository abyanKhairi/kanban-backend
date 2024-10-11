<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }


    public function register(){
        $validator = Validator::make(request()->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $user = User::create([
            'email'=> request('email'),
            'password'=> Hash::make(request('password')),
            'name'=> request('name'),
        ]);

        if (!$user) {
            return response()->json([
                "status" => 422,
                "success" => false,
                "message"=> "Pendaftaran Yang Dilakukan Gagal",
                "data" => $user,
            ],422);
        }


        return response()->json([
            "status" => 200,
            "success"=> true,
            "message"=> "User Berhasil Melakukan Pendaftaran",
            "data" => $user,
        ],200);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {

        if(auth()->user()) {
        return response()->json([
            "status" => 200,
            "success"=> true,
            "message"=> "Data User",
            "data" => auth()->user()
        ], 200);
    }

        return response()->json([
            "status"=> 404,
            "success"=> false,
            "message"=> "User Tidak",
        ]);

}

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json([
            'status' => 200,
            'success'=> true,
            'message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'status'=> 200,
            'success'=> true,
            'message'=> "User Berhasil Login",
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}