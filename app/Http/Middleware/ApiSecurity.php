<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Redirector;
use Illuminate\Http\Request;
use Illuminate\Foundation\Applicaion;

class ApiSecurity
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
        $message='';
        // if($request->header('Accept')!= 'application/json'){
        //     $message='Accept';
        // }
        // if(!empty($message)){
        //     $message.=' headers not found.';
        //     return response()->json(['status' => false, 'code'=>503, 'error' => $message], 503);
        // }

        if (empty($message)) {
            if (!in_array(strtolower($request->method()), ['put', 'post'])) {
                return $next($request);
            }
            $input = $request->all();
            
            function walk($input)
            {
                array_walk($input, function (&$input) {
                    
                    if (!is_array($input)) {
                        if (is_string($input)) {
                            $input = strip_tags($input);
                        }
                        
                    } else {
                        walk($input);
                    }
                });
                
                return $input;
            }
            
            $input = walk($input);

            $request->merge($input);

            return $next($request);
        } else {
            return response()->json(['status' => false, 'code'=>503, 'error' => "Invalid requst"], 503);
        }

    }

}
