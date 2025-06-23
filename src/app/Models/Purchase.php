<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_id',
        'purchased_at',
        'shipping_address_id',
        'payment_method',
        'status',
    ];

    protected $dates = [
        'purchased_at',
    ];

    public function item()
    {
        return $this->belongTo(Item::class);
    }

    public function shippingAddress()
    {
        return $this->belongTo(Address::class);
    }
}
