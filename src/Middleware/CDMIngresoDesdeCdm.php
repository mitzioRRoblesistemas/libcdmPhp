<?php

namespace Cdmisiones\Libcmd\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Cdmisiones\Libcdm\ApiOperaciones;
use Illuminate\Support\Facades\Log;
use App\Http\Middleware\CDMAutoriza;

class CDMIngresoDesdeCdm
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $instanciaSDK = app(ApiOperaciones::class);
            if($request->query('idSesion')){
                return CDMAutoriza::handle($request, $next);
            }
            return $next($request);
        } catch (\Throwable $th) {
            return $next($request);
        }
    }
}
