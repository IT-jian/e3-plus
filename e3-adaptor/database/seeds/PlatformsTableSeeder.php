<?php

use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlatformsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $platforms = [
            [
                'code' => 'taobao',
                'name' => '淘宝',
            ],
            [
                'code' => 'jingdong',
                'name' => '京东',
            ],
        ];

        Platform::insert($platforms);
    }
}
