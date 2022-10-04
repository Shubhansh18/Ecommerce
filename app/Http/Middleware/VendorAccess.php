<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class VendorAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    { 
        $token = $request->header('Authorization');
        if($token==null)
        {
            return response()->json([
                "message" => "Please enter your Auth_key"
            ]);
        }
        $userdata = JWT::decode($token, new Key('secret', 'HS256'));
            if(empty($userdata))
            {
                return response()->json([
                    "message" => "Invalid token"
                ]);
            }
            $user = User::where('username', $userdata->username)->first();
                if(!empty($user))
                {
                    if($user->is_vendor == true){
                        return $next($request);
                    }
                    return response()->json([
                        "message" => "You are not a vendor, please make a request to get vendor access"
                    ]);
                }
                return response()->json([
                    "message" => "Invalid Token"
                ]);
    }
}
