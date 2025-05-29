<?php

namespace App\Http\Controllers\Equidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;

class EquidadController extends Controller
{
    public function index()
    {
        //$usuarios = Usuario::where('usu_documento', 1143875526)->first();
        $usuarios = Usuario::all();
        return view('test', compact('usuarios'));
    }

    public function getToken()
    {
        require_once app_path('Libraries/nusoap/nusoap.php');

        $wsdl = 'https://servicios.laequidadseguros.coop/TokenService';
        $client = new \nusoap_client($wsdl, true);

        $client->response_timeout = 600;
        $client->soap_defencoding = false;

        $client->setUseCURL(true);
        $client->useHTTPPersistentConnection();
        $client->setCurlOption(CURLOPT_SSL_VERIFYHOST, 2);
        $client->setCurlOption(CURLOPT_SSL_VERIFYPEER, 0);

        $err = $client->getError();
        if ($err) {
            echo 'Error en Constructor' . $err;
        }

        /* XML para obtener token */
        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tok="http://servicios.laequidadseguros.coop/TokenService/">
                <soapenv:Header/>
                <soapenv:Body>
                <tok:credenciales>
                    <usuario>SEGASIS0301</usuario>
            <clave>Gasistencia2022*</clave>
                </tok:credenciales>
                </soapenv:Body>
            </soapenv:Envelope>';
        $res = $client->send($xml);
        $token = $res['token'];

        return $token;
    }

    public function cotizar(Request $request) {
        return $request->input('plan');
    }
}
