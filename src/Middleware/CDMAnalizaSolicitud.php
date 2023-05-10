<?php

namespace Cdmisiones\Libcmd\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Cdmisiones\Libcmd\ApiOperaciones;

class CDMAnalizaSolicitud
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $instanciaSDK = app(ApiOperaciones::class);
            $rta = $instanciaSDK->api_getSolicitud($request->cookie("cdm-token"), $request->query("solicitud"));

            $data = collect($rta['data'])->except(["origen", "metodo", "origenUri"])->toArray();
            $analizaSolicitud = [
                "status" => $rta['status'],
                "msg" => $rta['msg'],
                "data" => $data,
            ];
            $request->cdm =
                 [
                    "status" => $rta['status'],
                    "msg" => $rta['msg'],
                    "data" => [
                        "analizaSolicitud" => $analizaSolicitud,
                    ],
                ];
            
        } catch (\Throwable $th) {
            $request->cdm = 
                [
                    "status" => 500,
                    "msg" => 'Error inseperado',
                    "data" => [
                        "analizaSolicitud" => [
                            "status" => 500,
                            "msg" => 'Error inseperado',
                            "data" => [],
                        ],
                    ],
                ];
        }
        return $next($request);
    }
}
