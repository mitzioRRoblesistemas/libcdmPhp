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
            
            Log::info('CDMVerificaSesionToken');
            $instanciaSDK = app(ApiOperaciones::class);
            Log::info('token:', [$request->cookie('cdm-token')]);
            if($request->cookie('cdm-token')){
                Log::info('NO TIENE QUE ENTRAR !');

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
            

        // Llamar al middleware B dentro del resultado
        return app(CDMAutoriza::class)->handle($request, $next);

        // return $next($request);;
            


        } catch (\Throwable $th) {
            Log::error($th->getMessage());
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
