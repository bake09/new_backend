<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Exports\UsersExport;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Maatwebsite\Excel\Facades\Excel;
use App\Events\RolePermissionsUpdated;

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
        // return $user;
        
        $roleId = $request->input('role');

        // Alle Rollen entfernen
        $user->roles()->detach();
        // $user->syncRoles([]);

        // Neue Rolle zuweisen (per ID)
        $user->roles()->attach($roleId);

        // Optional: User neu laden mit Rollen
        // $user->load('roles');

        
        broadcast(new RolePermissionsUpdated($role))->toOthers();

        return response()->json([
            'message' => 'Role assigned successfully',
            'user' => new UserResource($user)
        ]);
    }

    public function export() 
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }
}
