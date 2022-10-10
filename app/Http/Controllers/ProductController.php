<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductValidation;
use App\Models\Products;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $allproducts = Products::where(function ($query) use ($request) {
            if($request->has('catagory')){
                $query->where('catagory', $request->catagory);
            }
            if($request->has('product_name')){
                $query->where('product_name', $request->product_name);
            }
            if($request->has('quantity')){
                $query->where('quantity', '>=', $request->quantity);
            }
            if($request->has('price')){
                $query->where('price', '<=', $request->price);
            }
        })->get();
        return $allproducts;
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
    public function store(ProductValidation $request)
    {
        $token = $request->header('Authorization');
        $userdata = JWT::decode($token, new Key('secret', 'HS256'));
        $user = User::where('username', $userdata->username)->first();
        $data = $request->except('user_id');
        $data['user_id'] = $user->id;
        $productexist = Products::where('user_id', $user->id)
                                ->where('product_name', $data['product_name'])
                                ->where('catagory',$data['catagory'])->first();
        if($productexist)
        {
            $productexist->quantity += $data['quantity']; 
            $productexist->save();
            return $productexist;
        }
        else{
            $product = Products::create($data);
            return $product;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Products::find($id);
        if(empty($product))
        {
            return response()->json([
                "message" => "Invalid Product id mentioned in url"
            ]);
        }
        return($product);
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
        $token = $request->header('Authorization');
        $userdata = JWT::decode($token, new Key('secret', 'HS256'));
        $user = User::where('username', $userdata->username)->first();
        $product = Products::find($id);
        if(empty($product))
        {
            return response()->json([
                "message" => "Invalid Product id mentioned in url"
            ]);
        }
        if($product->user_id == $user->id)
        {
            $data = $request->except('product_name');
            $product->update($data);
            return $product;
        }
        return response()->json([
            "message" => "You cannot edit this product as it does not belong to you"
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
        $product = Products::find($id);
        if(empty($product))
        {
            return response()->json([
                "message" => "Invalid Product id mentioned in url"
            ]);
        }
        if($product->user_id == $user->id)
        {
            $product->delete();
            return "Product deleted";
        }
        return response()->json([
            "message" => "You cannot delete this product as it does not belong to you"
        ]);
    }
}
