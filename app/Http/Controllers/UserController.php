<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // return UserResource::collection(User::all()->with(['roles', 'permissions']));
        return UserResource::collection(
            User::with(['team', 'roles', 'permissions'])->get()
        );
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Request $request, User $user)
    {
        return new UserResource($user);
    }

    public function update(Request $request, User $user)
    {
        dd($request);
    }

    public function destroy(User $user)
    {
        //
    }

    public function assignRole(Request $request, User $user)
    {
        $role = $request->input('role');
        return $role;
        if ($user->hasRole($role)) {
            return response()->json(['message' => 'User already has this role'], 400);
        }

        $user->assignRole($role);
        return response()->json(['message' => 'Role assigned successfully']);
    }
}
