<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class TelescopeAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authed = false;

        //If the request has the cookie 'telescope_auth' set, we will authenticate the user
        if(!$authed && $request->hasCookie('telescope_auth') && $request->cookie('telescope_auth') === Config::get('telescope.telescope_pass')){
            $authed = true;
        }

        //If not, check if the user included the telescope pass in the url as a query parameter
        if(!$authed && $request->has('telescope_pass') && $request->get('telescope_pass') == Config::get('telescope.telescope_pass')){
            $authed = true;
        }

        if($authed){
            return $next($request)
                ->withCookie(cookie()->forever('telescope_auth', Config::get('telescope.telescope_pass')));
        }else{
            abort(403, 'Unauthorized access');
        }
    }
}
