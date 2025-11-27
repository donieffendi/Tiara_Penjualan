<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckDivisi
{
    public function handle(Request $request, Closure $next)
    {
        $divisi = array_slice(func_get_args(), 2);
    
        foreach ($divisi as $div) { 
            $user = \Auth::user()->divisi;
            if( $user == $div){
                return $next($request);
            }
        }
    
        return redirect('/');
    }
}
