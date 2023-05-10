<?php

namespace Cdmisiones\Libcmd\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Cookie\CookieJar;
use Illuminate\Support\Facades\Cookie;
use Cdmisiones\Libcmd\ApiOperaciones;

class CDMCerrarSesion
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $cookieJar= new CookieJar();
            $instanciaSDK = app(ApiOperaciones::class);
            $rta = $instanciaSDK->api_cerrarSesion($request->cookie('cdm-token'));
            
            $request->cdm['status'] = $rta['status'];
            $request->cdm['msg'] = $rta['msg'];
            $request->cdm['data']= array_merge($request->cdm['data'] ?? [],$rta );

            if ($rta['status'] == 200) {
                Cookie::queue('cdm-token', '', -1);
                Cookie::queue('cdm-idSesion', '', -1);
                Cookie::queue('cdm-idColeccion', '', -1);
            }
            
            return $next($request);
        } catch (\Throwable $th) {
            $request->cdm['status'] = 500;
            $request->cdm['msg'] = $th->getMessage();
            $request->cdm['data']['autoriza'] = [
                'status' => 500,
                'msg' => $th->getMessage(),
                'data' => [],
            ];

            return $next($request);
        }
    }
}
