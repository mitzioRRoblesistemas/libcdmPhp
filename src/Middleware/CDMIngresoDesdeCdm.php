<?php

namespace Cdmisiones\Libcmd\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Cdmisiones\Libcmd\ApiOperaciones;
use Illuminate\Support\Facades\Log;
use Cdmisiones\Libcmd\Middleware\CDMAutoriza;

class CDMIngresoDesdeCdm
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $instanciaSDK = app(ApiOperaciones::class);
            if($request->query('idSesion')){
                return app(CDMAutoriza::class)->handle($request, $next);
            }
            return $next($request);
        } catch (\Throwable $th) {
            return $next($request);
        }
    }
}
