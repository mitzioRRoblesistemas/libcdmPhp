<?php

namespace Cdmisiones\Libcmd\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;
use App\Http\Middleware\CDMAutoriza;
use Cdmisiones\Libcmd\ApiOperaciones;



class CDMValidacionOTP
{
        public function handle($request, Closure $next, $rutaError, $ventanaVida)
            {            
                $instanciaSDK = app(ApiOperaciones::class);
                $solicitud = $request->cookie('cdm-solicitud-otp');
                if ($ventanaVida == 'false') {
                    
                    $solicitud = $request->query('solicitud');
                }
                if ($rutaError == '') {
                    throw new \Exception("Falta declarar la ruta para el manejo de errores");
                }
                try {
                    if ($solicitud) {
                        $rta = $instanciaSDK->api_getSolicitud($request->cookie('cdm-token'), $solicitud);
                        
                        if ($rta['status'] == 200) {
                            if ($rta['data']['resultadoProceso'] !== "correcto") {
                                Cookie::queue('cdm-solicitud-otp', '', -1);
                                return  redirect($rutaError.'?solicitud='.$request->query('solicitud'));
                            } else {
                                $data = collect($rta['data'])->except(['origen', 'metodo', 'origenUri'])->merge(['solicitud' => $solicitud])->toArray();
                                $validacionOTP = ['status' => $rta['status'], 'msg' => $rta['msg'], 'data' => $data];
                                $request->cdm['status'] = $rta['status'];
                                $request->cdm['msg'] = $rta['msg'];
                                $request->cdm['data']['validacionOTP'] = $validacionOTP;
                                return $next($request);
                            }
                        }
                        Cookie::queue('cdm-solicitud-otp', '', -1);
                        // return redirect($rutaError);
                    }
                    $request->originUrl = $request->fullUrl();
                    

                    $rta = $instanciaSDK->api_validaOTP($request->cookie('cdm-token'), $request->fullUrl());
                    if ($rta['msg'] == "redireccionado") {
                        return redirect($rta['data']['redirect']);
                    }
                    if ($rta['inStatus'] == 922) {
                        return redirect($rutaError)->with('solicitud', $request->query('solicitud'));
                    }
                    return CDMAutoriza::handle($request, $next);
                    
                } catch (\Exception $exception) {
                    return redirect($rutaError)->with('solicitud', $request->query('solicitud'));
                }
            
        }
    
}
