<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'cart_id',
        'user_id',
        'quantity',
        'order_amount',
        'payment_mode',
        'delivered_at',
        'expected_arrival'
    ];
}
