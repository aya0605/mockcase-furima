<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Purchase;
use Carbon\Carbon;

class PurchasesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Purchase::create([
            'item_id' => 1,      
            'user_id' => 2,
            'purchased_at' => Carbon::now(), 
            'shipping_address_id' => 2,
            'payment_method' => 'card',      
        ]);

        Purchase::create([
            'item_id' => 2,
            'user_id' => 1,
            'purchased_at' => Carbon::now(),
            'shipping_address_id' => 1, 
            'payment_method' => 'konbini',
        ]);

        Purchase::create([
            'item_id' => 3,
            'user_id' => 3,
            'purchased_at' => Carbon::now(),
            'shipping_address_id' => null, 
        ]);
    }
}
