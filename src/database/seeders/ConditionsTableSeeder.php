<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConditionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('conditions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // カラム名を 'condition' から 'name' に修正
        DB::table('conditions')->insert([
            [
                'id' => 1,
                'name' => '良好',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => '目立った傷や汚れなし',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'やや傷や汚れあり',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => '状態が悪い',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}