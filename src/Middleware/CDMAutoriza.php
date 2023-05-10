<?php

namespace Cdmisiones\Libcmd\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Cdmisiones\Libcdm\ApiOperaciones;
use Illuminate\Support\Facades\Log;



class CDMAutoriza
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            Log::info('En autoriza!!!!');
            
            $instanciaSDK = app(ApiOperaciones::class);
            $session = $request->query('idSesion') ?? $request->cookie('cdm-idSesion');
            $rta = $instanciaSDK->api_autoriza($request->cookie('cdm-keyLogin'), $session, $request->originUrl ?? '/');
            if ($rta['msg'] === 'redireccionado') {
                return redirect($rta['data']['redirect']);
            }
            $request->cdm['status'] = $rta['status'];
            $request->cdm['msg'] = $rta['msg'];
            $request->cdm['data']['autoriza'] = $rta;

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
