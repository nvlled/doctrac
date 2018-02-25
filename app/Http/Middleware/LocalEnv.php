<?php

namespace App\Http\Middleware;

use Closure;

class LocalEnv
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
        if (!isLocal()) {
            throw new \Exception("app environment is not local");
        }
        return $next($request);
    }
}
