<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class Singlesession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // $user = Auth::user();

        // if ($user && $user->session_token !== session('session_token')) {
        //     Auth::logout();
        //     return redirect()->route('login')->with('error', 'You have been logged out from other device.');
        // }

        // return $next($request);
    }
}
