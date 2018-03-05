<?php

namespace App\Http\Middleware;

use Closure;

class RestrictDocumentAccess
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
        $trackingId = $request->trackingId;
        $doc = \App\Document::where("trackingId", $trackingId)->first();
        if ($doc && $doc->classification != "open" && !\Auth::user())
            return redirect()->route("login");
        return $next($request);
    }
}
