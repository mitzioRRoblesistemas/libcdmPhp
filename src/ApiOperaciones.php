<?php

namespace App;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use Illuminate\Support\Facades\Log;




class ApiOperaciones{
    private $errores;
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $UrlServerApi;
    private $UrlServerAuth;
    private $apiKey;

    public function __construct($opciones)
    {
        $this->errores = [];
        $this->clientId = $opciones['clientId'] ?? $this->errores[] = 'Falta clientId';
        $this->clientSecret = $opciones['clientSecret'] ?? $this->errores[] = 'Falta clientSecret';
        $this->redirectUri = $opciones['redirectUri'] ?? $this->errores[] = 'Falta redirectUri';
        $this->UrlServerApi = $opciones['UrlServerApi'] ?? 'https://api.cdmisiones.net.ar';
        $this->UrlServerAuth = $opciones['UrlServerAuth'] ?? 'https://auth.cdmisiones.net.ar';
        $this->apiKey = $opciones['apiKey'] ?? null;
        if (!empty($this->errores)) {
            // Concatenar todos los elementos del array $this->errores en una cadena separada por comas
            $mensaje_error = implode(', ', $this->errores);
            throw new \Exception($mensaje_error);
        }
    }

    public function api_autoriza($keyLogin, $idSesion, $origenUri = '/')
    {
        $rta = [
            'status' => 400,
            'data' => ['redirect' => null],
            'msg' => 'Error Inesperado'
        ];

        $client = new Client(['base_uri' => $this->UrlServerAuth]);

        try {
            $response = $client->post('/auth/autoriza', [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                    'clientId' => $this->clientId,
                    'clientSecret' => $this->clientSecret,
                    'redirectUri' => $this->redirectUri,
                    'origenUri' => $origenUri,
                    'idSesion' => $idSesion,
                    'keyLogin' => $keyLogin,
                ],
                'allow_redirects' => [
                    'max' => 0
                ]
            ]);
            if ($response->getStatusCode() == [200]) {
                
                $rta['status'] = 200;
                $rta['msg'] = 'ok';
            }else{
                $rta['status'] = $response->getStatusCode();
                if ($response->getStatusCode() == 302) {
                    $rta['status'] = 200;
                    $rta['msg'] = 'redireccionado';
                    $rta['data']['redirect'] = $response->getHeader('Location')[0];
                } else {
                    $rta['msg'] = json_decode($response->getBody(), true)['msgStatus'];
                }
            }
        } catch (\Exception $e) {

            return $rta;
        }
    
        return $rta;
    
    }



    public function api_getToken($codigoAutorizacion)
    {
        $rta = [
            'status' => 400,
            'data' => ['token' => null],
            'msg' => 'Error Inesperado'
        ];

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                ])->asForm()->post($this->UrlServerAuth . '/auth/token', [
                    'codigo' => $codigoAutorizacion
                ]);
                if ($response->successful()) {
                    $rta['status'] = 200;
                    $rta['msg'] = 'ok';
                    $rta['data']['token'] = $response->json()['data'][0]['token'];
                }else {
                    $rta['msg'] = $response->json()['msgStatus'];
                }
        } catch (\Exception $e) {
            return $rta;
        }
        return $rta;

    }

    public function api_getPerfil($token)
    {
        $rta = [
            'status' => 400,
            'data' => [],
            'msg' => 'Error Inesperado'
        ];
        try {
            
            if ($token) {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'token' => $token
                    ])->get($this->UrlServerApi . '/api/coleccion/getPerfilColeccion');
                    if ($response->successful()) {
                        $rta['status'] = 200;
                        $rta['msg'] = 'ok';
                        $rta['data'] = $response->json()['data'][0];
                    }else {
                        $rta['msg'] = $response->json()['msgStatus'];
                    }
            }
            
        } catch (\Exception $e) {
            return $rta;
        }
        return $rta;
    }

    public function api_verificaSesionToken($token)
    {
        $rta = [
            'status' => 400,
            'data' => [],
            'msg' => 'Error Inesperado'
        ];
        try {
            
            if ($token) {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'token' => $token
                    ])->get($this->UrlServerAuth . '/auth/verificaSesion');
                    if ($response->successful()) {
                        $rta['status'] = 200;
                        $rta['msg'] = 'ok';
                        $rta['data'] = $response->json()['data'][0];
                    }else {
                        $rta['msg'] = $response->json()['msgStatus'];
                    }
            }
            
        } catch (\Exception $e) {
            return $rta;
        }
        return $rta;
    }

    public function api_getSolicitud($token, $solicitud)
    {
        $rta = [
            'status' => 400,
            'data' => [],
            'msg' => 'Error Inesperado'
        ];
        try {
            if ($token) {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'token' => $token
                    ])->get($this->UrlServerAuth . '/solicitud/' . $solicitud);
                    if ($response->successful()) {
                        $rta['status'] = 200;
                        $rta['msg'] = 'ok';
                        $rta['data'] = $response->json()['data'][0];
                    }else {
                        $rta['msg'] = $response->json()['msgStatus'];
                    }
            }
            
        } catch (\Exception $e) {
            return $rta;
        }
        return $rta;
    }

    public function api_cerrarSesion($token)
    {
        $rta = [
            'status' => 400,
            'data' => [],
            'msg' => 'Error Inesperado'
        ];
        try {
            if ($token) {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'token' => $token
                    ])->get($this->UrlServerApi . '/api/coleccion/cerrarSesionColeccion');
                    if ($response->successful()) {
                        $rta['status'] = 200;
                        $rta['msg'] = $response->json()['msgStatus'];
                    }else {
                        $rta['msg'] = $response->json()['msgStatus'];
                    }
            }
            
        } catch (\Exception $e) {
            return $rta;
        }
        return $rta;
    }

    
    public function api_validaOTP($token, $origenUri = '/')
    {
        Log::error('En valida OTP');
        $rta = [
            'status' => 400,
            'data' => ['redirect' => null],
            'msg' => 'Error Inesperado'
        ];
        try {
            $client = new Client(['base_uri' => $this->UrlServerAuth]);
            $response = $client->request('GET', '/otp/valida', [
            'headers' => [
                'Accept' => 'application/json',
                'token' => $token,
                'origenuri' => $origenUri,
                'apikey' => $this->apiKey['otp']
            ],
            'allow_redirects' => [
                'max' => 0,
            ],
            ]);
            Log::error('respuestaaaa');
            Log::error($response->getStatusCode());
            if ($response->getStatusCode() == 200) {
                $rta['status'] = 200;
                Log::error($response->getBody());
                $rta['msg'] = $response->getBody()->getContents()['msgStatus'];
            } else{
                $rta['status'] = $response->getStatusCode();
                if ($rta['status'] == 302) {
                    $rta['status'] = 200;
                    $rta['msg'] = 'redireccionado';
                    $rta['data']['redirect'] = $response->getHeader('Location')[0];
                } else {
                    $rta['msg'] = json_decode($response->getBody()->getContents(), true)['msgStatus'];
                }
            }
    } catch (\Exception $e) {
        Log::error('error', [$e]);
        return $rta;
    }


        return $rta;
    }

    public function api_validaFaceTec($token, $origenUri = '/')
    {
        $rta = [
            'status' => 400,
            'data' => ['redirect' => null],
            'msg' => 'Error Inesperado'
        ];

        try {
            
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'token' => $token,
                'origenuri' => $origenUri,
                'apikey' => $this->apiKey['facetec'],
                ])->withOptions(['max_redirects' => 0])->get($this->UrlServerAuth . '/facetec/valida');
                if ($response->successful()) {
                    $rta['status'] = 200;
                    $rta['msg'] = $response->json()['msgStatus'];
                } else if ($response->clientError()) {
                    $rta['status'] = $response->status();
                if ($rta['status'] == 302) {
                    $rta['status'] = 200;
                    $rta['msg'] = 'redireccionado';
                    $rta['data']['redirect'] = $response->header('Location');
                } else {
                    $rta['msg'] = $response->json()['msgStatus'];
                }
            }
            
        } catch (\Exception $e) {
            return $rta;
        }
        return $rta;
    }

}