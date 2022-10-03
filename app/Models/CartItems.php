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
        'Order_amount',
        'Payment_mode',
        'Status',
        'Expected_arrival'
    ];
}
