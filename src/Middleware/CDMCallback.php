<?php

namespace Cdmisiones\Libcmd\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Cdmisiones\Libcmd\ApiOperaciones;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Cookie;

class CDMCallback
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            Log::error('ENTRE EN CDM CALLBACK');
            Log::error('EL TIPO: ', [$request->query('tipo')]);
            $instanciaSDK = app(ApiOperaciones::class);
            if ($request->query('tipo') === 'login') {
                
                $token = null;
                Cookie::queue('cdm-idSesion', $request->query('idSesion'), 525600);
                Cookie::queue('cdm-keyLogin', $request->query('keyLogin'), 525600);
                Cookie::queue('cdm-token', '', -1);
                $rta = $instanciaSDK->api_getToken($request->query('codigoAutorizacion'));
                
                if ($rta['status'] == 200) {
                    Cookie::queue('cdm-token', $rta['data']['token'], 525600);
                    $token = $rta['data']['token'];
                    $rta = $instanciaSDK->api_getPerfil($rta['data']['token']);
                    if ($rta['status'] == 200) {
                        $request->cdm = [
                            'idColeccion' => $rta['data']['id'],
                            'data' => array_merge($request->cdm['data'] ?? [], [
                                'getPerfil' => $rta,
                            ]),
                        ];
                        
                        Cookie::queue('cdm-idColeccion', $rta['data']['id'], 525600);
                    }
                }
                $request->cdm = [
                    'status' => $rta['status'],
                    'msg' => $rta['msg'],
                    'token' => $token,
                    'data' => array_merge($request->cdm['data'] ?? [], [
                        'origenUri' => $request->query('origenUri'),
                    ]),
                ];
                
                return $next($request);
            }
            if ($request->query('tipo') === 'solicitud') {
                if ($request->query('metodo') === 'facetec') {
                    Cookie::queue('cdm-solicitud-facetec', $request->query('solicitud'), 525600);
                }
                if ($request->query('metodo') === 'otp') {
                    Cookie::queue('cdm-solicitud-otp', $request->query('solicitud'), 525600);
                }
                return redirect($request->query('origenUri').'?solicitud='.$request->query('solicitud'));
            }
            
            

            $request->cdm = ['status' => 400,
            'msg' => 'tipo de callback no reconocido',
            'data' => array_merge($request->cdm['data'] ?? [], [
                'getPerfil' => [
                    'status' => 500,
                    'msg' => 'Error callback no reconocido',
                    'data' => [],
                ],
                'origenUri' => $request->query('origenUri'),
            ]),
            ];
            return $next($request);
        } catch (Exception $error) {
            $request->cdm['status'] = 500;
            $request->cdm['msg'] = $error->getMessage();
            $request->cdm['data'] = array_merge($request->cdm['data'] ?? [], ['getPerfil' => ['status' => 500, 'msg' => $error->getMessage(), 'data' => []], 'origenUri' => $request->query('origenUri')]);
            return $next($request);
        }  
    }
}
