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


    public function register()
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 422,
                "success" => false,
                "message" => "Validasi gagal",
                "errors" => $validator->messages()
            ], 422);
        }

        $user = User::create([
            'email' => request('email'),
            'password' => Hash::make(request('password')),
            'name' => request('name'),
        ]);

        if (!$user) {
            return response()->json([
                "status" => 422,
                "success" => false,
                "message" => "Pendaftaran yang dilakukan gagal",
                "data" => null,
            ], 422);
        }

        return response()->json([
            "status" => 200,
            "success" => true,
            "message" => "User  berhasil melakukan pendaftaran",
            "data" => $user,
        ], 200);
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
            return response()->json(['error' => 'Unauthorized', 'massage' => 'Invalid credentials'], 401);
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

        if (auth()->user()) {
            return response()->json([
                "status" => 200,
                "success" => true,
                "message" => "Data User",
                "data" => auth()->user()
            ], 200);
        }

        return response()->json([
            "status" => 404,
            "success" => false,
            "message" => "User Tidak",
        ]);
    }

    public function updateName()
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $user = auth()->user();
        $user->name = request('name');
        $user->save();

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Name updated successfully',
            'data' => $user,
        ], 200);
    }

    public function updateEmail()
    {
        $user = auth()->user();

        $validator = Validator::make(request()->all(), [
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->messages()
            ], 422);
        }

        // Cek apakah password yang dimasukkan cocok dengan password yang ada di database
        if (!Hash::check(request('password'), $user->password)) {
            return response()->json([
                'status' => 422,
                'success' => false,
                'message' => 'Password yang dimasukkan salah',
            ], 422);
        }

        // Jika password cocok, lanjutkan dengan update email
        $user->email = request('email');
        $user->save();

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Email berhasil diperbarui',
            'data' => $user,
        ], 200);
    }

    public function updatePassword()
    {
        // Validate the request
        $validatedData = request()->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = auth()->user();

        // Check if the current password is correct
        if (!Hash::check($validatedData['current_password'], $user->password)) {
            return response()->json([
                'status' => 401,
                'success' => false,
                'message' => 'Password lama yang dimasukkan salah',
            ], 401);
        }

        // Update the password
        $user->update(['password' => Hash::make($validatedData['new_password'])]);

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Password updated successfully',
        ], 200);
    }


    public function updateAvatar()
    {
        $validator = Validator::make(request()->all(), [
            'avatar' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $user = auth()->user();
        $user->avatar = request('avatar');
        $user->save();

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Name updated successfully',
            'data' => $user,
        ], 200);
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
            'success' => true,
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        try {

            $user = auth()->user();


            if (!$user) {
                return response()->json([
                    "status" => 401,
                    "success" => false,
                    "message" => "User Belum Login",
                ], 401);
            }

            $newToken = auth()->refresh();
            return $this->respondWithToken($newToken);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'status' => 401,
                'success' => false,
                'message' => 'Refresh token has expired. Please log in again.'
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'status' => 401,
                'success' => false,
                'message' => 'Token is invalid. Please log in again.'
            ], 401);
        }
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
            'status' => 200,
            'success' => true,
            'message' => "User Berhasil Login",
            'user' => auth()->user(),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
