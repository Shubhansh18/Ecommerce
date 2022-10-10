<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartUpdateValidation;
use App\Http\Requests\CartValidation;
use App\Models\CartItems;
use App\Models\Products;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class CartController extends Controller
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
        $cartitems = CartItems::where('user_id', $user->id)->get();
        if($cartitems->isEmpty())
        {
            return response()->json([
                "message" => "No items in your cart"
            ]);
        }
        return $cartitems;
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
    public function store(CartValidation $request)
    {
        $token = $request->header('Authorization');
        $userdata = JWT::decode($token, new Key('secret', 'HS256'));
        $user = User::where('username', $userdata->username)->first();
        $data = $request->only('product_id', 'quantity');
        $products = Products::where('id', $data['product_id'])->first();
        $data['user_id']=$user->id;
        if($products->quantity_available == 0)
        {
            return response()->json([
                "message" => "this product is out of stock"
            ]);
        }
        if($products->quantity_available < $data['quantity'])
        {
            return response()->json([
                "message" => "Mentioned quantity of ". $products->product_name." is not in stock.",
                "Quantity in stock" => $products->quantity_available,
                "Suggestion" => "Please try reducing the quantity!"
            ]);
        }
        else{
            $cartitems = CartItems::where('product_id', $data['product_id'])->where('user_id', $data['user_id'])->first();
            $data['amount']= ($products->price)*($data['quantity']);
            if(empty($cartitems))
            {
                $cart = CartItems::create($data);
                return $cart;
            }
            $cartitems->quantity += $data['quantity'];
            if($cartitems->quantity > $products->quantity_available)
            {
                return Response()->json([
                    "message" => "Mentioned quantity of ". $products->product_name." is not in stock.",
                    "Quantity in stock" => $products->quantity_available,
                    "Suggestion" => "Please try reducing the quantity!"
                ]);
            }
            $cartitems->amount = ($products->price) * $cartitems->quantity;
            $cartitems->save();
            return $cartitems;
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
        $token = $request->header('Authorization');
        $userdata = JWT::decode($token, new Key('secret', 'HS256'));
        $user = User::where('username', $userdata->username)->first();
        $cartitems = CartItems::where('user_id', $user->id)->where('id', $id)->first();
        return $cartitems;
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
    public function update(CartUpdateValidation $request, $id)
    {
        $token = $request->header('Authorization');
        $userdata = JWT::decode($token, new Key('secret', 'HS256'));
        $user = User::where('username', $userdata->username)->first();
        $cartitems = CartItems::where('user_id', $user->id)->where('product_id', $id)->first();
        $product = Products::where('id', $id)->first();
        if(empty($product))
        {
            return response()->json([
                "message" => "product_id is not valid"
            ]);
        }
        else{
            if(empty($cartitems))
            {
                return response()->json([
                    "message" => $product->product_name. " is\are not added in your cart"
                ]);
            }
            else{
                $data = ['quantity' => $request->quantity];
                if($data['quantity'] == 0)
                {
                    $cartitems->delete();
                    return response()->json([
                        "message" => "Product is deleted from your cart"
                    ]);
                }
                if($data['quantity'] > $product->quantity_available)
                {
                    return Response()->json([
                        "message" => "Mentioned quantity of ". $product->product_name." is not in stock.",
                        "Quantity in stock" => $product->quantity_available,
                        "Suggestion" => "Please try reducing the quantity!"
                    ]);
                }
                $cartitems->cart_amount = ($product->price) * $data['quantity'];
                $cartitems->save();
                $cartitems->update($data);
                return $cartitems;
            }
        }
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
        $cartitem = CartItems::where('user_id', $user->id)->where('product_id', $id)->first();
        if($cartitem)
        {
            $cartitem->delete();
        }
        return response()->json([
            "message" => "the mentioned product_id does not exist in your cart"
        ]);
    }
}
