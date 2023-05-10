<?php

namespace Cdmisiones\Libcmd\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Cdmisiones\Libcmd\ApiOperaciones;

class CDMGlobal
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->cdm = [];
        if ($request->hasCookie('cdm-idColeccion')) {
            $request->cdm['idColeccion'] = $request->cookie('cdm-idColeccion');
        }
        return $next($request);
    }
}
