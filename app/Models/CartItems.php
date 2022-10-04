<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItems extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'order_amount',
        'payment_mode',
        'status',
        'expected_arrival'
    ];
}
