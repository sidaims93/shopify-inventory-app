<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        
        $roles = [
            'Admin', 'SuperAdmin', 'Staff'
        ];
        foreach($roles as $role)
            Role::updateOrCreate(['name' => $role]);

        $permissionArr = config('permission.default_permissions');

        $permissions = array_merge($permissionArr, [
            'all-access', //For SuperAdmin   
        ]);

        foreach($permissions as $permission)
            Permission::updateOrCreate(['name' => $permission]);

        //Assign default Permissions
        $superadmin = Role::where('name', 'SuperAdmin')->first();
        $superadmin->givePermissionTo('all-access');

        $admin = Role::where('name', 'Admin')->first();
        $admin->givePermissionTo($permissionArr);
    }
}
