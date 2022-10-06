<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserUpdateValidation;
use App\Http\Requests\UserValidation;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


class UserController extends Controller
{
    /**
     * Regenerate JWT if lost.
     */
    public function getJWT(Request $request)
    {
        $username = $request->username;
        $password = $request->password;

        $user = User::where('username',$username)->where('password',$password)->first();
        if(empty($user))
        {
            return response()->json([
                "message" => "invalid credentials"
            ]);
        }
        $data = [
            'name'=>$user->name,
            'email'=>$user->email,
            'username'=>$user->username,
            'mobile'=>$user->mobile,
            'address'=>$user->address
        ]; 
        $userdata = JWT::encode($data,'secret','HS256');
        return $userdata;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return User::all();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
       //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserValidation $request)
    {
        $data = [
            'name'=>$request->name,
            'email'=>$request->email,
            'username'=>$request->username,
            'mobile'=>$request->mobile,
            'address'=>$request->address
        ];
        $userdata = JWT::encode($data,'secret','HS256');
        $password = $data['username'];
        $data['password']= $password;
        User::create($data);
        return response()->json([
            "message" => "Registration Successful. Pease keep the mentioned Auth_key safe with you.",
            "Auth_key" => $userdata
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        return $user;
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
    public function update(UserUpdateValidation $request, $id)
    {
        $userdata = User::find($id);
        if(empty($userdata))
        {
            return response()->json([
                "message" => "User not found"
            ]);
        }
        else{
            if($userdata->username == "Shubhansh18g")
            {
                return response()->json([
                    "message" => "Cannot Update this data"
                ]);
            }
            $data = $request->all();
            if($request->is_vendor == 0)
            {
                $vendor = Vendor::where('user_id', $id)->first();
                if($vendor == null)
                {
                    $userdata->update($data);
                    return response()->json([
                        "message" => "User Updated Successfully",
                        $userdata
                    ]);
                }
                $vendor->update(['status' => 'pending']);
                $userdata->update($data);
                return response()->json([
                    "message" => "User Updated Successfully",
                    $userdata
                ]);
            }
            else{
                $vendor = Vendor::where('user_id', $id)->first();
                if($vendor == null)
                {
                    return response()->json([
                        "message" => "This user has not requested for vendor access",
                        $userdata
                    ]);
                }
                $vendor->update(['status' => 'approved']);
                $userdata->update($data);
                return response()->json([
                    "message" => "User Updated Successfully",
                    $userdata
                ]);
            } 
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $token = $request->header('Authorization');
        $userdata = JWT::decode($token, new Key('secret', 'HS256'));
        if($userdata->username == "Shubhansh18g")
        {
            return response()->json([
                "message" => "Admin cannot be deleted"
            ]);
        }
        $user = User::where('username', $userdata->username);
        $user->delete();
            return response()->json([
                "message" => "User Deleted"
            ]);
    }

    public function changePass(Request $request)
    {
        $username = $request->username;
        $oldpassword = $request->old_password;
        $newpassword = $request->new_password;

        $user = User::where('username', $username)->where('password', $oldpassword)->first();
        if(empty($user))
        {
            return response()->json([
                "message"=> "Invalid credentials"
            ]);
        }
        else{
            $user->update([$user->password = $newpassword]);
            return response()->json([
                "message" => "Password changed successfully"
            ]);
        }
    }
    
    public function makevendor(Request $request)
    {
        $token = $request->header('Authorization');
        $userdata = JWT::decode($token, new Key('secret', 'HS256'));
        $user = User::where('username', $userdata->username)->first();
        $rq = Vendor::where('user_id', $user->id)->first();
        if($user->is_vendor){
            return response()->json([
                "message" => "You are already a vendor"
            ]);
        }
        else{
            if(empty($rq))
            {
                $vrequest = Vendor::create(
                    [
                        'user_id' => $user->id
                    ]);
                return response()->json([
                    "message" => "Your request is submitted and will be approved shortly",
                    $vrequest
                ]);
            }
            return response()->json([
                "message" => "You have already made a request"
            ]);
        }
    }
}