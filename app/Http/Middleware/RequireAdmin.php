<?php

namespace App\Http\Middleware;

use Closure;

class RequireAdmin
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
        if (optional(\Auth::user())->admin) {
            return $next($request);
        }
        if ($request->ajax())
            return response()->json([
                "errors"=>["operation requires administrative privileges"],
            ]);
        return redirect("/login");
    }
}
