<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CookieAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authed = false;

        if(Config::get("app.cookie_password") === null){
            Log::error("Cookie password not set");
            abort(500, "Cookie password not set");
        }

        //If the request has the cookie 'cookieauthentication' set, we will authenticate the user
        if(!$authed && $request->hasCookie('cookieauthentication') && $request->cookie('cookieauthentication') === Config::get('app.cookie_password')){
            $authed = true;
        }

        //If not, check if the user included the telescope pass in the url as a query parameter
        if(!$authed && $request->has('cookie_password') && $request->get('cookie_password') === Config::get('app.cookie_password')){
            $authed = true;
        }

        if($authed){
            return $next($request)
                ->withCookie(cookie()->forever('cookieauthentication', Config::get('app.cookie_password')));
        }else{
            abort(403, 'Unauthorized access');
        }
    }
}
