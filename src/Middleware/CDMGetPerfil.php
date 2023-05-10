<?php

namespace Cdmisiones\Libcmd\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Cdmisiones\Libcdm\ApiOperaciones;
use Illuminate\Support\Facades\Log;
use App\Http\Middleware\CDMAutoriza;


class CDMGetPerfil
{
    
    public function handle(Request $request, Closure $next): Response
    {
        try {
            Log::info('en get perfil');
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
