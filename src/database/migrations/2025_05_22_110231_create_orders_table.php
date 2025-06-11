<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade'); 
            $table->integer('total_amount');
            $table->timestamp('order_date');
            
            $table->string('shipping_postal_code', 8); 
            $table->string('shipping_prefecture'); 
            $table->string('shipping_city');      
            $table->string('shipping_street_address'); 
            $table->string('shipping_building_name')->nullable(); 

            $table->string('payment_method');
            $table->string('status'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
