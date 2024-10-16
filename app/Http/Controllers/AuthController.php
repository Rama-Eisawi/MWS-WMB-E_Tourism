<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginFormRequest;
use App\Http\Requests\Auth\RegisterFormRequest;
use App\Models\Tourist;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use ApiResponses;
    public function register(RegisterFormRequest $request)
    {
        // Create the new user
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),  // Hash the password
            'fName' => $request->fName,
            'lName' => $request->lName,
            'description' => $request->description,
            'role' => 'tourist',  // Set the role (tourist, driver, guide, admin)
        ]);

        Tourist::create([
            'user_id' => $user->id,
        ]);
        $token = JWTAuth::fromUser($user);
        return $this->success(compact('user', 'token'), 'User created successfully', 201);
    }
    //-------------------------------------------------------------------------------------
    public function login(LoginFormRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $token = JWTAuth::attempt($credentials);
        // Attempt to authenticate the user with the provided credentials
        if (!$token) {
            return $this->failed('Password is wrong', 422);
        }

        // Retrieve the authenticated user
        $user = Auth::user();

        // Return a successful response with the user and token
        return  $this->success(compact('user', 'token'), 'User logged in successfully', 200);
    }

    public function logout()
    {
        try {
            // Invalidate the token, so it can no longer be used
            JWTAuth::invalidate(JWTAuth::getToken());

            return  $this->success(null, 'User logged out successfully', 200);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $exception) {
            // Something went wrong while attempting to invalidate the token
            return  $this->error('Logout error', 500, 'فشل تسجيل الخروج، الرجاء المحاولة لاحقا');
        }
    }
}
