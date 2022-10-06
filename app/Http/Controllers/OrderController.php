<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartValidation;
use App\Models\CartItems;
use App\Models\Order;
use App\Models\Products;
use App\Models\User;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $token = $request->header('Authorization');
        $userdata = JWT::decode($token, new Key('secret', 'HS256'));
        $user = User::where('username', $userdata->username)->first();
        $bill = Order::where('user_id', $user->id)->get(['quantity', 'order_amount', 'payment_mode', 'expected_arrival']);
        return $bill;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $token = $request->header('Authorization');
        $userdata = JWT::decode($token, new Key('secret', 'HS256'));
        $user = User::where('username', $userdata->username)->first();
        $cartitems = CartItems::where('user_id', $user->id)->where('id', $request->cart_id)->first();
        if(empty($cartitems))
        {
            return response()->json([
                "message" => "please enter a valid cart_id"
            ]);
        }
        else{
            $products = Products::where('id', $cartitems->product_id)->first();
            if($products->quantity == 0)
            {
                return response()->json([
                    "message" => "this product is out of stock"
                ]);
            }
            else{
                if($cartitems->quantity > $products->quantity)
                {
                    return response()->json([
                        "message" => "Can't place this order because the quantity of ". $products->product_name." you ordered is not available",
                        "Quantity in stock" => $products->quantity,
                        "Suggestion" => "please update quantity in your cart to be within the range of quantity in stock"
                    ]);
                }
                else{
                    $data = ['cart_id' => $cartitems->id, 'user_id' => $user->id, 'quantity' => $cartitems->quantity, 'order_amount' => ($products->price)*($cartitems->quantity), 'expected_arrival' => Carbon::now()->addDays(7)];
                    $products->quantity -= $cartitems->quantity;
                    $products->save();
                    $order = Order::create($data);
                    return $order;
                }
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        if($order)
        {
            $data = ['delivered_at' => $request->delivered_at];
            $order->update($data);
            return $order;
        }
        return response()->json([
            "message" => "please mention a valid order id"
        ]);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $token = $request->header('Authorization');
        $userdata = JWT::decode($token, new Key('secret', 'HS256'));
        $user = User::where('username', $userdata->username)->first();
        $order = Order::where('user_id', $user->id)->where('delivered_at', null)->where('id', $id)->first();
        if($order)
        {
            $order->delete();
            return response()->json([
                "message" => "your order is cancelled"
            ]);
        }
        else{
            return response()->json([
                "message" => "this order can't be cancelled as it is either never placed or is already delivered"
            ]);
        }
    }
}
