<?php

use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['name' => '事假', 'sort' => '1'],
            ['name' => '病假', 'sort' => '2'],
            ['name' => '年假', 'sort' => '3'],
            ['name' => '调休', 'sort' => '4'],
            ['name' => '婚假', 'sort' => '5'],
            ['name' => '产假', 'sort' => '6'],
            ['name' => '陪产假', 'sort' => '7'],
        ];
        DB::table('leave_type')->truncate();
        DB::table('leave_type')->insert($data);
    }
}
