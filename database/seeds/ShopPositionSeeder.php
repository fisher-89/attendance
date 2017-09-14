<?php

use Illuminate\Database\Seeder;

class ShopPositionSeeder extends Seeder
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
            ['name' => '店助', 'sort' => 1],
            ['name' => '导购', 'sort' => 1],
        ];
        DB::table('shop_position')->truncate();
        DB::table('shop_position')->insert($data);
    }
}
