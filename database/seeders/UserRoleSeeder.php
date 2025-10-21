<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        
        $adminRole   = Role::firstOrCreate(['name' => 'Administrator']);
        $staffRole   = Role::firstOrCreate(['name' => 'Staf Gudang']);
        $managerRole = Role::firstOrCreate(['name' => 'Manajer Gudang']);

        
        $admin = User::factory()->create([
            'name' => 'Admin gudang',
            'email' => 'admin@gudang.com',
        ]);
        $admin->assignRole($adminRole);

        
        $staff = User::factory()->create([
            'name' => 'Staf Gudang',
            'email' => 'staff@gudang.com',
        ]);
        $staff->assignRole($staffRole);

        
        $manager = User::factory()->create([
            'name' => 'Manajer Gudang',
            'email' => 'manager@gudang.com',
        ]);
        $manager->assignRole($managerRole);

    }
}
