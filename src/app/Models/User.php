<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany; 

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image_path'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(Item::class, 'seller_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likedItems()
    {
        return $this->belongsToMany(Item::class, 'likes', 'user_id', 'item_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function hasLiked(Item $item)
    {
        return $this->likes()->where('item_id', $item->id)->exists();
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

     public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function defaultShippingAddress()
    {
        return $this->addresses()->where('is_default', true)->first();
    }

    public function purchases()
    {
        return $this->hasMany(Order::class, 'buyer_id'); 
    }
}
