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
        'seller_last_read_at',
        'buyer_last_read_at',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'seller_last_read_at' => 'datetime',
        'buyer_last_read_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(Address::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
