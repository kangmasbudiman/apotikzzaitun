<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Dashboard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()->can('isGudang')) {
            return redirect()->route('stuff.index');
        } else if ($request->user()->can('isKasir')) {
            return redirect()->route('transaction.index');
        }

        return $next($request);
    }
}
