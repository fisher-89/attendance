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
        $this->call(ShopPositionSeeder::class);
        $this->call(TransferTagSeeder::class);
        $this->call(LeaveTypeSeeder::class);
    }
}
