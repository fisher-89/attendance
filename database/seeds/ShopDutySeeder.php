<?php

use Illuminate\Database\Seeder;

class ShopDutySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['name' => '店长', 'sort' => 1],
            ['name' => '店助', 'sort' => 2],
            ['name' => '导购', 'sort' => 3],
        ];
        DB::table('shop_duty')->truncate();
        DB::table('shop_duty')->insert($data);
    }
}
