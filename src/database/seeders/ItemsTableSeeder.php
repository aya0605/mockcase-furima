<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Condition;
use App\Models\Item;
use App\Models\Like;

class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $params = [
            [
                'name' => '腕時計',
                'price' => 15000,
                'brand' => 'Rolax',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'image_url' => 'public/img/mens_clock.jpg', 
                'seller_id' => 2,
                'condition_id' => 1, 
            ],
            [
                'name' => 'HDD',
                'price' => 5000,
                'brand' => '西芝',
                'description' => '高速で信頼性の高いハードディスク',
                'image_url' => 'public/img/hard_disk.jpg', 
                'seller_id' => 2, 
                'condition_id' => 2, 
            ],
            [
                'name' => '玉ねぎ3束',
                'price' => 300,
                'brand' => '',
                'description' => '新鮮な玉ねぎ3束のセット',
                'image_url' => 'public/img/onion.jpg', 
                'seller_id' => 2, 
                'condition_id' => 3, 
            ],
            [
                'name' => '革靴',
                'price' => 4000,
                'brand' => '',
                'description' => 'クラシックなデザインの革靴',
                'image_url' => 'public/img/leather_shoes.jpg', 
                'seller_id' => 2, 
                'condition_id' => 4, 
            ],
            [
                'name' => 'ノートPC',
                'price' => 45000,
                'brand' => '',
                'description' => '高性能なノートパソコン',
                'image_url' => 'public/img/laptop_PC.jpg', 
                'seller_id' => 2, 
                'condition_id' => 1, 
            ],
            [
                'name' => 'マイク',
                'price' => 8000,
                'brand' => '',
                'description' => '高音質のレコーディング用マイク',
                'image_url' => 'public/img/mic.jpg', 
                'seller_id' => 2, 
                'condition_id' => 2, 
            ],
            [
                'name' => 'ショルダーバッグ',
                'price' => 3500,
                'brand' => '',
                'description' => 'おしゃれなショルダーバッグ',
                'image_url' => 'public/img/shoulder_bag.jpg', 
                'seller_id' => 1, 
                'condition_id' => 3, 
            ],
            [
                'name' => 'タンブラー',
                'price' => 500,
                'brand' => '',
                'description' => '使いやすいタンブラー',
                'image_url' => 'public/img/tumbler.jpg',
                'seller_id' => 1, 
                'condition_id' => 4, 
            ],
            [
                'name' => 'コーヒーミル',
                'price' => 4000,
                'brand' => 'Starbacks',
                'description' => '手動のコーヒーミル',
                'image_url' => 'public/img/coffer_mill.jpg', 
                'seller_id' => 1, 
                'condition_id' => 1, 
            ],
            [
                'name' => 'メイクセット',
                'price' => 2500,
                'brand' => '',
                'description' => '便利なメイクアップセット',
                'image_url' => 'public/img/make_set.jpg', 
                'seller_id' => 1, 
                'condition_id' => 2, 
            ],
        ];

        Item::truncate(); 
        foreach ($params as $param) {
            Item::create($param);
        }

        Like::truncate();
        Like::create([
            'user_id' => 1,
            'item_id' => 1, 
        ]);
        Like::create([
            'user_id' => 2,
            'item_id' => 7, 
        ]);
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
