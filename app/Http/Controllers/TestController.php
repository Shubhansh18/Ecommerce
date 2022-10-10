<?php

namespace App\Http\Controllers;

use App\Models\CartItems;
use App\Models\Products;
use App\Models\User;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        $user = User::find(3);
        $cartitems = CartItems::where('user_id', $user->id)->pluck('quantity', 'product_id')->toArray();
        $prid = array_keys($cartitems);
        $products = Products::wherein('id', $prid)->pluck('quantity', 'id')->toArray();
        foreach($cartitems as $key => $val){
            if($val < $products[$key])
            {
               echo "check";
            } else {
               echo "choke";
            }
        }
    }
}
