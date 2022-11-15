<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class AdminAccess
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
        try{
            $tokendata = JWT::decode($token, new Key('secret', 'HS256'));
        }catch (\Exception $e) {
            return response()->json([
                "message" => "Invalid Token!"
            ]);
        }
        if($tokendata->username == "Shubhansh18g")
        {
            return $next($request);
        }
        else{
            return response()->json([
                "message" => "Unauthorized Access"
            ]);
        }
    }
}
