<?php

namespace Cdmisiones\Libcmd\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Cdmisiones\Libcmd\ApiOperaciones;
use Illuminate\Support\Facades\Log;



class GetPerfil
{
    
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $instanciaSDK = app(ApiOperaciones::class);
            $rta = $instanciaSDK->api_getPerfil($request->cookie("cdm-token"));
            $request->cdm['status'] = $rta['status'];
            $request->cdm['msg'] = $rta['msg'];
            $request->cdm['data'] = array_merge($request->cdm['data'] ?? [],['getPerfil'=>$rta]);
           return $next($request);        
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
                                "getPerfil" => [
                                    "status" => 500,
                                    "msg" => 'Error inseperado',
                                    "data" => [],
                                ],
                            ]),
                        ],
                    ],
                ];
        }
        return $next($request);
    }
}
