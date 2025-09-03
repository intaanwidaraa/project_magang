<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat Roles
        $adminRole = Role::create(['name' => 'Administrator']);
        $staffRole = Role::create(['name' => 'Staf Gudang']);

        // Buat User Administrator
        $admin = User::factory()->create([
            'name' => 'Admin gudang',
            'email' => 'admin@gudang.com',
        ]);
        $admin->assignRole($adminRole);

        // Buat User Staf Gudang
        $staff = User::factory()->create([
            'name' => 'Staf Gudang',
            'email' => 'staff@gudang.com',
        ]);
        $staff->assignRole($staffRole);
    }
}
