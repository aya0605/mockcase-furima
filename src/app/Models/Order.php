<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'item_id',
        'total_amount',
        'order_date',
        'shipping_postal_code',
        'shipping_prefecture',
        'shipping_city',
        'shipping_street_address',
        'shipping_building_name',
        'payment_method',
        'status',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
