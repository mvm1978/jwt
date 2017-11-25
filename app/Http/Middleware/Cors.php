<?php

namespace App\Http\Middleware;

use Closure;

class Cors {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('Access-Control-Allow-Origin', env('API_ACCESS_CONTROL_ALLOW_ORIGIN'));
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE');

        return $response;
    }
}