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
use Illuminate\Support\Arr;

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
        $bill = Order::where('user_id', $user->id)->get(['order_amount', 'payment_mode', 'expected_arrival']);
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
        $cartitems = CartItems::where('user_id', $user->id)->pluck('quantity','product_id')->toArray();
        ksort($cartitems);
        if($cartitems==null)
        {
            return response()->json([
                "message" => "please add items in your cart to order"
            ]);
        }
        else{
            $prid = array_keys($cartitems);
            $products = Products::wherein('id', $prid)->pluck('quantity_available', 'id')->toArray();
            // dd($cartitems, $products);
            $qty = array_map(function ($a, $b) {
                if($a == $b)
                    return "0"; 
                if($a < $b){
                    return ($b-$a);
                }
            },$cartitems, $products);
            $arr = array_combine($prid, $qty);
            asort($arr);
            $id = array_keys($arr);
            $qt = array_values($arr);

            for ($i = 0; $i < count($arr); $i++) {
                if($qt[$i] == null)
                {
                    $product = Products::where(['id' => $id[$i]])->first();
                    return response()->json([
                        "Order Status" => "Failed| Your order is not placed, please update your cart to order",
                        "Message" => "The quantity of ".$product->product_name. " in your cart is more than its quantity available"
                    ]);
                }
                else
                {
                    Products::where(['id' => $id[$i]])->update(['quantity_available' => $qt[$i]]);
                }
            }
            $cartamount = CartItems::where('user_id', $user->id)->pluck('amount')->toArray();
            $price = array_sum($cartamount);
            $data = ['user_id' => $user->id, 'order_amount' => $price, 'expected_arrival' => Carbon::now()->addDays(7)];
            $order = Order::create($data);
            return response()->json([
                "Order Status" => "Success| Your order is successfully placed",
                "Message" => "The order will be delivered to your doorstep by ".$data['expected_arrival']
            ]);
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
        };
        if(Order::find($id) == null)
        {
            return response()->json([
                "message" => "The mentioned order_id is invalid"
            ]);
        }
        else{
            return response()->json([
                "message" => "this order can't be cancelled as it is already delivered"
            ]);
        }
    }
}
