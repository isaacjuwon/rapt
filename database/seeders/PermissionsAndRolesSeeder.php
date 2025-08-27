<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionsAndRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for User CRUD
        $permissions = [
            'create user',
            'read user',
            'update user',
            'delete user',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Assign all permissions to super_admin
        $superAdminRole->givePermissionTo(Permission::all());

        // Assign specific permissions to admin
        $adminRole->givePermissionTo([
            'create user',
            'read user',
            'update user',
        ]);

        // Assign specific permissions to user
        $userRole->givePermissionTo([
            'read user',
        ]);
    }
}
