<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('UsersTableSeeder'); // 用户
        $this->call('PermissionsSeeder'); // 角色权限
        $this->call('PlatformsTableSeeder'); //  平台列表
    }
}
