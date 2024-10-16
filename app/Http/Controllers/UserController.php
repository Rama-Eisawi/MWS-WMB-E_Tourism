<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterFormRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Driver;
use App\Models\Guide;
use App\Models\Tourist;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return $this->success($users, 'The list of Users', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'fName' => $validated['fName'],
            'lName' => $validated['lName'],
            'role' => $validated['role'],
            'description' => $validated['description'],
        ]);
        if ($user->role === 'tourist')
            $userInfo = Tourist::create([
                'user_id' => $user->id,
            ]);
        $userInfo = null;
        if ($user->role === 'driver')
            $userInfo  = Driver::create([
                'user_id' => $user->id,
                'plate_number' => $request->plate_number,
            ]);
        if ($user->role === 'guide')
            $userInfo = Guide::create([
                'user_id' => $user->id,
                'address' => $request->address,
                'mobile' => $request->mobile,
            ]);
        return $this->success(compact('user', 'userInfo'), 'The user created successfully !', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return $this->success($user, 'The User info', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validated();

        $user->update($validated);
        return $this->success($user, 'The User updated successfully', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return $this->success(null, 'User deleted successfully', 200);
    }
}
