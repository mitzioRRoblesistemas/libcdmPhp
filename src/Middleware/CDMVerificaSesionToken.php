<?php

namespace Cdmisiones\Libcmd\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Cdmisiones\Libcmd\ApiOperaciones;
use Illuminate\Support\Facades\Log;
use Cdmisiones\Libcmd\Middleware\CDMAutoriza;

class CDMVerificaSesionToken
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $request->originUrl = $request->fullUrl();
            $instanciaSDK = app(ApiOperaciones::class);
            if($request->cookie('cdm-token')){
                $rta = $instanciaSDK->api_verificaSesionToken($request->cookie("cdm-token"));
                if($rta['status'] != 200){
                    return app(CDMAutoriza::class)->handle($request, $next);
                }
                $request->cdm['status'] = $rta['status'];
                $request->cdm['msg'] = $rta['msg'];
                $request->cdm['idColeccion'] = $rta['data']['idColeccion'];
                $request->cdm['data'] = array_merge($request->cdm['data'] ?? [],$rta);
                return $next($request);
            }
            return app(CDMAutoriza::class)->handle($request, $next);
        } catch (\Throwable $th) {
            $request->cdm = 
                [
                    "status" => 500,
                    "msg" => 'Error inseperado',
                    "data" => [
                        "analizaSolicitud" => [
                            "status" => 500,
                            "msg" => 'Error inseperado',
                            "data" => array_merge($request->cdm['data'] ?? [],[
                                "verificaSesionToken" => [
                                    "status" => 500,
                                    "msg" => 'Error inseperado',
                                    "data" => [],
                                ],
                            ]),
                        ],
                    ],
                ];
                return $next($request);
        }
    }
}
