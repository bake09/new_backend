<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Todo;

use App\Models\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // TEAM
        Team::create(['name' => 'Ökke']);

        // ROLE and PERMISSIONS

        // clear Roles and Permissions CACHE
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create roles and assign created permissions
        $superadminRole = Role::create(['name' => 'superadmin', 'guard_name' => 'api']);
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $userRole = Role::create(['name' => 'user', 'guard_name' => 'api']);

        // create permissions
        $userPermissionCreate   =   Permission::create(['name' => 'create_todo', 'guard_name' => 'api']);
        $userPermissionRead     =   Permission::create(['name' => 'read_todo', 'guard_name' => 'api']);
        $userPermissionUpdate   =   Permission::create(['name' => 'update_todo', 'guard_name' => 'api']);
        $adminPermissionDelete  =   Permission::create(['name' => 'delete_todo', 'guard_name' => 'api']);

        $superadminRole->syncPermissions($userPermissionCreate, $userPermissionRead, $userPermissionUpdate, $adminPermissionDelete);
        $adminRole->syncPermissions($userPermissionCreate, $userPermissionRead, $userPermissionUpdate);
        $userRole->syncPermissions($userPermissionRead, $userPermissionCreate);

        // USERS
        $user1 = User::factory()->create([
            'name' => 'User 1',
            'team_id' => 1,
            'email' => 'test@test.com',
            'avatar' => 'storage/avatars/avatar_1.png',
        ]);
        $user2 = User::factory()->create([
            'name' => 'User 2',
            'team_id' => 1,
            'email' => 'test2@test.com',
            'avatar' => 'storage/avatars/avatar_2.jpg',
        ]);
        $user3 = User::factory()->create([
            'name' => 'User 3',
            'team_id' => 1,
            'email' => 'test3@test.com',
            'avatar' => 'storage/avatars/avatar_3.jpg',
        ]);
        $user1->assignRole('superadmin');
        $user2->assignRole('admin');
        $user3->assignRole('user');

        // TODO's
        Todo::factory()->create(['content' => 'Todo 1', 'user_id' => '1']);
        Todo::factory()->create(['content' => 'Todo 2', 'user_id' => '2']);
        Todo::factory()->create(['content' => 'Todo 3', 'user_id' => '3']);
    }
}
