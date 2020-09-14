<?php

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 新增权限
        $now = \Carbon\Carbon::now()->toDateTimeString();
        $permissions = [
            ['id' => '1', 'parent_id' => '0', 'name' => 'admin_manage', 'desc' => '后台管理', 'guard_name' => 'api', 'created_at' => $now],
            ['id' => '2', 'parent_id' => '1', 'name' => 'user_manage', 'desc' => '用户管理', 'guard_name' => 'api', 'created_at' => $now],
            ['id' => '3', 'parent_id' => '2', 'name' => 'view_user', 'desc' => '查看用户', 'guard_name' => 'api', 'created_at' => $now],
            ['id' => '4', 'parent_id' => '2', 'name' => 'add_user', 'desc' => '新增用户', 'guard_name' => 'api', 'created_at' => $now],
            ['id' => '5', 'parent_id' => '2', 'name' => 'edit_user', 'desc' => '修改用户', 'guard_name' => 'api', 'created_at' => $now],
            ['id' => '6', 'parent_id' => '2', 'name' => 'delete_user', 'desc' => '删除用户', 'guard_name' => 'api', 'created_at' => $now],
            ['id' => '7', 'parent_id' => '1', 'name' => 'role_manage', 'desc' => '角色管理', 'guard_name' => 'api', 'created_at' => $now],
            ['id' => '8', 'parent_id' => '7', 'name' => 'view_role', 'desc' => '查看角色', 'guard_name' => 'api', 'created_at' => $now],
            ['id' => '9', 'parent_id' => '7', 'name' => 'add_role', 'desc' => '新增角色', 'guard_name' => 'api', 'created_at' => $now],
            ['id' => '10', 'parent_id' => '7', 'name' => 'edit_role', 'desc' => '修改角色', 'guard_name' => 'api', 'created_at' => $now],
            ['id' => '11', 'parent_id' => '7', 'name' => 'delete_role', 'desc' => '删除角色', 'guard_name' => 'api', 'created_at' => $now],
            ['id' => '12', 'parent_id' => '1', 'name' => 'permission_manage', 'desc' => '权限管理', 'guard_name' => 'api', 'created_at' => $now],
            ['id' => '13', 'parent_id' => '12', 'name' => 'view_permission', 'desc' => '查看权限', 'guard_name' => 'api', 'created_at' => $now],
            ['id' => '14', 'parent_id' => '12', 'name' => 'add_permission', 'desc' => '新增权限', 'guard_name' => 'api', 'created_at' => $now],
            ['id' => '15', 'parent_id' => '12', 'name' => 'edit_permission', 'desc' => '修改权限', 'guard_name' => 'api', 'created_at' => $now],
            ['id' => '16', 'parent_id' => '12', 'name' => 'delete_permission', 'desc' => '删除名称', 'guard_name' => 'api', 'created_at' => $now],
            ['id' => '17', 'parent_id' => '1', 'name' => 'operator_log_manage', 'desc' => '日志管理', 'guard_name' => 'api', 'created_at' => $now],
            ['id' => '18', 'parent_id' => '17', 'name' => 'view_operator_log', 'desc' => '查看日志', 'guard_name' => 'api', 'created_at' => $now],
            ['id' => '19', 'parent_id' => '17', 'name' => 'delete_operator_log', 'desc' => '删除日志', 'guard_name' => 'api', 'created_at' => $now],
            ['id' => '20', 'parent_id' => '1', 'name' => 'common_manage', 'desc' => '公共权限', 'guard_name' => 'api', 'created_at' => $now],
            ['id' => '21', 'parent_id' => '20', 'name' => 'profile_user', 'desc' => '用户详情', 'guard_name' => 'api', 'created_at' => $now],
        ];
        Permission::insert($permissions);
        // 新增角色
        $role = [
            'id' => 1,
            'name' => 'super_admin',
            'desc' => '超级管理员',
        ];
        $adminRole = Role::create($role);

        $role = [
            'id' => 2,
            'name' => 'normal_user',
            'desc' => '普通用户',
        ];
        $normalRole = Role::create($role);
        // 清除缓存
        \Illuminate\Support\Facades\Artisan::call('permission:cache-reset');
        // 角色绑定权限
        $normalRole->givePermissionTo([17,18,19,20,21,]);
        // 用户绑定角色
        $admin = \App\Models\User::find(1);
        $admin->assignRole($adminRole);
    }
}