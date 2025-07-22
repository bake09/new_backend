<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Events\RolePermissionsUpdated;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::with('roles')->get();

        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        // Validierung der Eingabedaten
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Rolle erstellen
        $role = Role::create(['name' => $request->name]);

        // Berechtigungen zuweisen, falls vorhanden
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'message' => 'Role created successfully!',
            'role' => $role->load('permissions'),
        ], 201);
    }

    public function show(string $id)
    {
        $role = Role::with('permissions')->find($id);

        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        return response()->json(['role' => $role]);
    }

    public function update(Request $request, string $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        // Validierung der Eingabedaten
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Rolle aktualisieren
        if ($request->has('name')) {
            $role->name = $request->name;
            $role->save();
        }

        // Berechtigungen synchronisieren, falls vorhanden
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'message' => 'Role updated successfully!',
            'role' => $role->load('permissions'),
        ]);
    }

    public function destroy(string $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        // Rolle löschen
        $role->delete();

        return response()->json(['message' => 'Role deleted successfully!']);
    }

    public function assignPermissions(Request $request, Role $role)
    {
        $role->givePermissionTo($request->permission);

        // broadcast(new RolePermissionsUpdated($role));
        broadcast(new RolePermissionsUpdated($role))->toOthers();

        return response()->json([
            'message' => 'Permissions assigned successfully!',
            'role' => $role->load('permissions'),
        ]);
    }

    public function removePermission(Role $role, Permission $permission)
    {
        // Berechtigung entfernen
        $role->revokePermissionTo($permission);
        
        broadcast(new RolePermissionsUpdated($role));
        // broadcast(new RolePermissionsUpdated($role))->toOthers();

        return response()->json([
            'message' => 'Permission removed successfully!',
            'role' => $role->load('permissions'),
        ]);
    }

    public function updateRole(Role $role, Request $request)
    {
        $permissionNames = collect($request->permissions)->pluck('name');

        // Validierung der Eingabedaten
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles,name,' . $role->id,
            // 'permissions' => 'nullable|array',
            // 'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // // Rolle aktualisieren
        $role->name = $request->name;
        $role->save();

        // Berechtigungen synchronisieren, falls vorhanden
        if ($request->has('permissions')) {
            $role->syncPermissions($permissionNames);
        }
        broadcast(new RolePermissionsUpdated($role))->toOthers();

        return response()->json([
            'message' => 'Role updated successfully!',
            'role' => $role->load('permissions'),
        ]);
    }

    public function addPermission(Request $request)
    {
        // Validierung der Eingabedaten
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Berechtigung erstellen
        $permission = Permission::create(['name' => $request->name]);

        return response()->json([
            'message' => 'Permission created successfully!',
            'permission' => $permission,
        ], 201);
    }   
}