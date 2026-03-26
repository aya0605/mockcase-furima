<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Address; // モデル名が Address であることを確認してください

class AddressesTableSeeder extends Seeder
{
    public function run()
    {
        // ユーザー1の住所
        Address::create([
            'user_id' => 1,
            'postal_code' => '108-0014', 
            'address' => '東京都港区芝5丁目29-20610',
            'building_name' => 'クロスオフィス三田',
            'is_default' => true,
        ]);

        // ユーザー2の住所 (購入者用)
        Address::create([
            'user_id' => 2,
            'postal_code' => '150-0043',
            'address' => '東京都渋谷区道玄坂',
            'building_name' => 'テックビル101',
            'is_default' => true,
        ]);
    }
}