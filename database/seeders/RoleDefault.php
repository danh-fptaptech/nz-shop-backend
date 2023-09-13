<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleDefault extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleTB = [
            [
                'name' => 'Admin',
                'guard_name ' => 'sanctum'
            ],
            [
                'name' => 'User',
                'guard_name ' => 'sanctum'
            ],
            [
                'name' => 'Deliver',
                'guard_name ' => 'sanctum'
            ],
            [
                'name' => 'Manager',
                'guard_name ' => 'sanctum'
            ],
        ];
        Role::insert($roleTB);
        $permissionsTB = [
            [
                'name'=> 'Create Role',
                'guard_name'=> 'sanctum',
            ],[
                'name'=> 'Edit Role',
                'guard_name'=> 'sanctum',
            ],[
                'name'=> 'Delete Role',
                'guard_name'=> 'sanctum',
            ],[
                'name'=> 'Set Role User',
                'guard_name'=> 'sanctum',
            ],[
                'name'=> 'Set Permission Role',
                'guard_name'=> 'sanctum',
            ],[
                'name'=> 'Create Coupon',
                'guard_name'=> 'sanctum',
            ],
        ];
        Permission::insert($permissionsTB);
        DB::table('role_has_permissions')->insert([
            [
                'permission_id ' => '1',
                'role_id ' => '1',
            ],[
                'permission_id ' => '2',
                'role_id ' => '1',
            ],[
                'permission_id ' => '3',
                'role_id ' => '1',
            ],[
                'permission_id ' => '4',
                'role_id ' => '1',
            ],[
                'permission_id ' => '5',
                'role_id ' => '1',
            ],
        ]);
        DB::table('model_has_roles')->insert([
            [
                'role_id' => '1',
                'model_type' => 'App\Models\User',
                'model_id' => '1',
            ]
        ]);
    }
}
