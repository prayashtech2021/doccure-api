<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Redirector;
use Illuminate\Http\Request;
use Illuminate\Foundation\Applicaion;

class CheckAuthHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    { 
        if(!in_array($request->headers->get('accept'), ['application/json', 'Application/Json'])){
            return response()->json(['status' => false, 'code'=>503, 'error' => "Accept header not available"], 503);
        }
        if($request->bearerToken()){
            return $next($request);
        } 
        return response()->json(['status' => false, 'code'=>503, 'error' => "Auth token not available"], 503);
        
    }
}
