<?php

namespace App\Http\Controllers\ramos\autos\Equidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;
use App\Models\PeticionesAseguradoras;
use App\Models\CiudadesBolivar;
use DateTime;

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

    public function mainCiudadEquidad($areaCode)
    {
        /** getCiudadEquidad, no esta como funcion ya que no sera usada en ninguna parte del controlador **/
        $result = new \stdClass();
        $queryCiudad = CiudadesBolivar::where('Codigo', $areaCode)->first();

        if (!$queryCiudad) {
            $result->message = 'No se obtuvo la ciudad bolivar';
            $result->status = false;
            return $result;
        }

        $result->message = 'Ciudad obtenida';
        $result->status = true;
        $result->city = $queryCiudad->Nombre;

        $ciudadBolivar = $result;

        $result = new \stdClass();
        if ($ciudadBolivar->status == false) {
            return $ciudadBolivar;
        }

        $ciudadParts = explode('-', $ciudadBolivar->city);

        if ($ciudadParts[0] == "BOGOTA D.C.") {

            $ciudadParte = explode('-', $ciudadBolivar->city)[0];

            $ciudad = DB::table('ciudades')
                ->select('codigo')
                ->where('ciudad', 'like', '%' . $ciudadParte . '%')
                ->first();
        } else {

            $ciudadParts = explode('-', $ciudadBolivar->city);

            $ciudad = DB::table('ciudades')
                ->select('codigo')
                ->where('ciudad', 'like', '%' . $ciudadParts[0] . '%')
                ->where('departamento', 'like', '%' . $ciudadParts[1] . '%')
                ->first();
        }

        if (!$ciudad) {
            $result->message = 'No se obtuvo la ciudad bolivar';
            $result->status = false;
            return $result;
        }

        $result->message = 'Ciudad obtenida';
        $result->status = true;
        $result->city = $ciudad->codigo;

        $ciudad = $result;


        if ($ciudad->status == false) {
            return $ciudad;
        }

        $result->message = $ciudad->message;
        $result->status = $ciudad->status;
        $result->city = $ciudad->city;

        return $result;
    }

    public function definirValor($arrayDetalle)
    {
        $count = count($arrayDetalle);
        $count2 = $count - 1;

        //var_dump($count);
        //var_dump($count2);
        //var_dump($arrayDetalle[$count2]['valstring']);
        //return $count2;

        return $arrayDetalle[$count2]['valstring'];
    }

    public function definirPlan($codigo)
    {
        if ($codigo == '011720') {
            return 'AUTOPLUS FULL';
        }
        if ($codigo == '011723') {
            return 'AUTOPLUS LIGERO';
        };
        if ($codigo == '011721') {
            return 'AUTOPLUS BÁSICO';
        }
        if ($codigo == '011722') {
            return 'AUTOPLUS RCE';
        }

        return '';
    }

    public function defineCategory($id)
    {
        $category = "";
        switch ($id) {
            case '011720':
                $category = ["Premium"];
                break;
            case '011723':
                $category = ["Full"];
                break;
            case '011721':
                $category = ["Clasicas"];
                break;
            case '011722':
                $category = ["RCE"];
                break;
            default:
                $category = false;
                break;
        }

        return $category;
    }

    public function definirRCE($plan)
    {
        if ($plan == 'AUTOPLUS FULL') return 4000000000;
        if ($plan == 'AUTOPLUS BÁSICO') return 4000000000;
        if ($plan == 'AUTOPLUS RCE') return 1500000000;
        if ($plan == 'AUTOPLUS LIGERO') return 3000000000;
    }

    public function definirTotal($plan)
    {
        if ($plan == 'AUTOPLUS FULL') return 'Cubrimiento al 100%';
        if ($plan == 'AUTOPLUS BÁSICO') return 'Deducible: $6.000.000';
        if ($plan == 'AUTOPLUS RCE') return 'No aplica';
        if ($plan == 'AUTOPLUS LIGERO') return 'Cubrimiento al 100%';
    }

    public function definirParcial($plan)
    {
        if ($plan == 'AUTOPLUS FULL') return 'Deducible: $1.350.000';
        if ($plan == 'AUTOPLUS BÁSICO') return 'Deducible: $6.000.000';
        if ($plan == 'AUTOPLUS RCE') return 'No aplica';
        if ($plan == 'AUTOPLUS LIGERO') return 'Deducible: 1,5 SMMLV';
    }

    public function definirConductoresElegidos($plan)
    {
        if ($plan == 'AUTOPLUS FULL') return '12 servicios';
        if ($plan == 'AUTOPLUS BÁSICO') return '6 servicios';
        if ($plan == 'AUTOPLUS RCE') return 'No aplica';
        if ($plan == 'AUTOPLUS LIGERO') return '6 servicios';
    }

    public function definirServicioGrua($plan)
    {
        if ($plan == 'AUTOPLUS FULL') return 'Si aplica';
        if ($plan == 'AUTOPLUS BÁSICO') return 'Si aplica';
        if ($plan == 'AUTOPLUS RCE') return 'Si aplica';
        if ($plan == 'AUTOPLUS LIGERO') return 'Si aplica';
    }

    public function cotizar(Request $request)
    {
        // return $request->input('plan'); Obtener una sola clave 
        // return $request->all(); Obtener el JSON completo

        $token = self::getToken();

        $tipoDoc = $request->input('TipoIdentificacion');
        $numDoc = $request->input('NumeroIdentificacion');
        $fechaNacimiento = $request->input('FechaNacimiento');
        $Nombre = $request->input('Nombre');
        $Apellido = $request->input('Apellido');

        $codGenero = $request->input('Genero');

        if ($tipoDoc == 2) {
            $codGenero = '3';
        }
        $genero = 'N';
        $tipoPersona = 'N';
        if ($codGenero == '1') {
            $genero = 'M';
            $tipoPersona = '1';
        }
        if ($codGenero == '2') {
            $genero = 'F';
            $tipoPersona = '1';
        }
        if ($codGenero == '3') {
            $genero = 'PJ';
            $tipoPersona = '2';
            $nameParts = explode(" ", $request->input('razonSocial'), 2);
            $Nombre = $nameParts[0];
            $Apellido = $nameParts[1];
            $fechaNacimiento = "1999-01-01";
        }

        $modelo = substr($request->input('Modelo'), 2, 2);
        $fecha_nacimiento = new DateTime($fechaNacimiento);
        $fecha_sin_hora = $fecha_nacimiento->format('Y-m-d');
        $hoy = new DateTime();
        $edad = $hoy->diff($fecha_nacimiento);
        // $edad = $edad->y;

        $codigoCiudad = self::mainCiudadEquidad($request->input('CiudadBolivar'));

        require_once app_path('Libraries/nusoap/nusoap.php');

        $wsdl = 'https://servicios.laequidadseguros.coop/SrvPoliza?token=' . $token;
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

        $fechaInicio = trim(date('Y-m-d'));
        $fechaFinal = trim((new DateTime())->modify('+1 year')->format('Y-m-d'));

        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:srv="http://www.example.org/SrvPoliza/">
        <soapenv:Header />
        <soapenv:Body>
        <srv:crearCotizacion>
        <comp>1</comp>
        <sucur>7600103</sucur>
        <fecini>' . $fechaInicio . '</fecini>
        <fecter>' . $fechaFinal . '</fecter>
        <comision>0</comision>
        <vaseg>0</vaseg>
        <producto>
        <codpla>011720</codpla>
        <nemotec>AUTOPLUS FULL</nemotec>
        </producto>
        <producto>
        <codpla>011723</codpla>
        <nemotec>AUTOPLUS BASICO</nemotec>
        </producto>
        <producto>
        <codpla>011721</codpla>
        <nemotec>AUTOPLUS RCE</nemotec>
        </producto>
        <producto>
        <codpla>011722</codpla>
        <nemotec>AUTOPLUS LIGERO</nemotec>
        </producto>
        <parametroCotizacion>
        <usuario>SEGASI0301</usuario>
        <tipoObjeto>CERTIFICADO</tipoObjeto>
        </parametroCotizacion>
        <tercero>
        <vinculacion>1</vinculacion>
        <codVinculacion>1</codVinculacion>
        <codigo>' . $numDoc . '</codigo>
        <parentesco>1</parentesco>
        <nombre>' . strtoupper($Nombre) . ' ' . strtoupper($Apellido) . '</nombre>
        <sexo>' . $genero . '</sexo>
        <tipoPersona>' . $tipoPersona . '</tipoPersona>
        <fechaNacimiento>' . $fechaNacimiento . '</fechaNacimiento>
        </tercero>
        <tercero>
        <vinculacion>1</vinculacion>
        <codVinculacion>2</codVinculacion>
        <codigo>' . $numDoc . '</codigo>
        <parentesco>1</parentesco>
        <nombre>' . strtoupper($Nombre) . ' ' . strtoupper($Apellido) . '</nombre>
        <sexo>' . $genero . '</sexo>
        <tipoPersona>' . $tipoPersona . '</tipoPersona>
        <fechaNacimiento>' . $fechaNacimiento . '</fechaNacimiento>
        </tercero>
        <tercero>
        <vinculacion>1</vinculacion>
        <codVinculacion>3</codVinculacion>
        <codigo>900600470</codigo>
        <parentesco>1</parentesco>
        <nombre>SEGUROS GRUPOASISTENCIA SAS</nombre>
        <sexo>PJ</sexo>
        <tipoPersona>4</tipoPersona>
        <fechaNacimiento>2017-01-01</fechaNacimiento>
        </tercero>
        <tercero>
        <vinculacion>1</vinculacion>
        <codVinculacion>0</codVinculacion>
        <codigo>' . $numDoc . '</codigo>
        <parentesco>1</parentesco>
        <nombre>' . strtoupper($Nombre) . ' ' . strtoupper($Apellido) . '</nombre>
        <sexo>' . $genero . '</sexo>
        <tipoPersona>' . $tipoPersona . '</tipoPersona>
        <fechaNacimiento>' . $fechaNacimiento . '</fechaNacimiento>
        </tercero>
        <tercero>
        <vinculacion>1</vinculacion>
        <codVinculacion>0</codVinculacion>
        <codigo>' . $numDoc . '</codigo>
        <parentesco>1</parentesco>
        <nombre>' . strtoupper($Nombre) . ' ' . strtoupper($Apellido) . '</nombre>
        <sexo>' . $genero . '</sexo>
        <tipoPersona>' . $tipoPersona . '</tipoPersona>
        <fechaNacimiento>' . $fechaNacimiento . '</fechaNacimiento>
        </tercero>
        <detalle>
        <coddet>00000060</coddet>
        <valdate></valdate>
        <valnumber>1</valnumber>
        <valstring>' . $codigoCiudad->city . '</valstring>
        </detalle>
        <detalle>
        <coddet>01010005</coddet>
        <valdate></valdate>
        <valnumber>0</valnumber>
        <valstring>' . $request->input('CodigoFasecolda') . '</valstring>
        </detalle>
        <detalle>
        <coddet>01010053</coddet>
        <valdate></valdate>
        <valnumber>0</valnumber>
        <valstring>' . $modelo . '</valstring>
        </detalle>

        <detalle>
        <coddet>00000109</coddet>
        <valdate></valdate>
        <valnumber>1</valnumber>
        <valstring>1</valstring>
        </detalle>
        <detalle>
        <coddet>01010120</coddet>
        <valdate></valdate>
        <valnumber>1</valnumber>
        <valstring>' . $request->input('Placa') . '</valstring>
        </detalle>
        <detalle>
        <coddet>20010161</coddet>
        
        <valdate>' . $fecha_sin_hora . '</valdate>
        <valnumber>1</valnumber>
        <valstring>1</valstring>
        </detalle>
        <detalle>
        <coddet>GENERO01</coddet>
        <valdate></valdate>
        <valnumber>1</valnumber>
        <valstring>' . $genero . '</valstring>
        </detalle>
        <detalle>
        <coddet>00000120</coddet>
        <valdate></valdate>
        <valnumber>1</valnumber>
        <valstring>' . $edad->y . '</valstring>
        </detalle>
        <detalle>
        <coddet>DEDUC001</coddet>
        <valdate></valdate>
        <valnumber>1.0</valnumber>
        <valstring>0000</valstring>
        </detalle>
        <detalle>
        <coddet>DEDUC002</coddet>
        <valdate></valdate>
        <valnumber>1.0</valnumber>
        <valstring>P00095</valstring>
        </detalle>
        
        <detalle>
        <coddet>01010107</coddet>
        <valdate></valdate>
        <valnumber>1</valnumber>
        <valstring>SI</valstring>
        </detalle>
        <detalle>
        <coddet>CONINT01</coddet>
        <valdate></valdate>
        <valnumber>1</valnumber>
        <valstring>000001</valstring>
        </detalle>
        <detalle>
        <coddet>AUTOMAK</coddet>
        <valdate></valdate>
        <valnumber>0</valnumber>
        <valstring>04</valstring>
        </detalle>
        <detalle>
        <coddet>TMODVEH1</coddet>
        <valdate>2018-05-04</valdate>
        <valnumber>' . $request->input('CodigoFasecolda') . '</valnumber>
        <valstring>1</valstring>
        </detalle>
        <detalle>
        <coddet>VALASE01</coddet>
        <valdate>2018-05-04</valdate>
        <valnumber>0</valnumber>
        <valstring>0</valstring>
        </detalle>
        <detalle>
        <coddet>VALACC01</coddet>
        <valdate>2019-06-13</valdate>
        <valnumber>0</valnumber>
        <valstring>1</valstring>
        </detalle>
        <detalle>
        <coddet>BLINDA01</coddet>
        <valdate>2019-06-13</valdate>
        <valnumber>0</valnumber>
        <valstring>N</valstring>
        </detalle>
        <detalle>
        <coddet>VALACC03</coddet>
        <valdate>2019-06-13</valdate>
        <valnumber>0</valnumber>
        <valstring>0</valstring>
        </detalle>
        <detalle>
        <coddet>B1010181</coddet>
        <valdate>2019-06-13</valdate>
        <valnumber>1</valnumber>
        <valstring>00</valstring>
        </detalle>
        <detalle>
        <coddet>CONVE002</coddet>
        <valdate/>
        <valnumber>1</valnumber>
        <valstring>000001</valstring>
        </detalle>
        </srv:crearCotizacion>
        </soapenv:Body>
        </soapenv:Envelope>';

        $res = $client->send($xml);
        $resControl = json_encode($res, JSON_UNESCAPED_UNICODE);

        PeticionesAseguradoras::guardarPeticion('EquidadJ', strtoupper($request->input('Placa')), $request->input('cotizacion'), $xml, json_encode($res, JSON_UNESCAPED_UNICODE));

        //return $resControl; //PERFECTO HASTA AQUI!

        if (isset($res['faultcode'])) {
            $data = json_decode($resControl, true);
            if ($data !== null && isset($data['faultstring'])) {
                // Extraer el mensaje de error y almacenarlo en una variable
                $mensajeError = $data['faultstring'];

                // Imprimir o utilizar la variable $mensajeError según tus necesidades
                $errores = array();
                $mensaje = array();

                array_push($mensaje, $mensajeError);
                array_push($errores, array('Resultado' => false, 'Aseguradora' => 'Equidad', 'Mensajes' => $mensaje));

                // guardarAlerta($datos['cotizacion'], 'Equidad', $mensajeError, 0);

                return $errores;
            } else {
                // Manejar el caso en que el JSON no pudo ser decodificado correctamente
                var_dump("Error al decodificar el JSON.");
                die();
            }
        } elseif (isset($res['contextoRespuesta']['errores'])) {
            $esArray = false;
            $errores = array();
            $mensajes = array();
            $response = $res['contextoRespuesta']['errores'];

            if (is_array($response)) {
                // Verificamos si es un array asociativo de un único error
                if (isset($response['codError'])) {
                    // Caso de un único error
                    array_push($mensajes, $response['codError'] . ' - ' . $response['descError']);
                    array_push($errores, array('Resultado' => false, 'Aseguradora' => 'Equidad', 'Mensajes' => $mensajes));
                    return $errores;
                } else {
                    // Caso de múltiples errores
                    foreach ($response as $resp) {
                        if (is_array($resp) && isset($resp['codError'])) {
                            $mensajeEqui = $resp['codError'] . ' - ' . $resp['descError'];
                            array_push($mensajes, $mensajeEqui);
                        }
                    }
                }
            }

            if (empty($mensajes)) {
                // Si no se encontró ningún error, manejo de caso inesperado
                array_push($mensajes, "Error desconocido en la estructura de la respuesta.");
            }

            array_push($errores, array('Resultado' => false, 'Aseguradora' => 'Equidad', 'Mensajes' => $mensajes));
            return $errores;
        }

        $productos = array();
        $vari = "";

        foreach ($res['poliza'] as $key => $producto) {
            $plan = self::definirPlan($producto['codpla']);
            $producto = array(
                'entidad' => 'Equidad',
                'numero_cotizacion' => $producto['cotizacion'],
                'imagen' => 'equidad.png',
                'producto' => $plan,
                'precio' => number_format(substr((float)self::definirValor($producto['detalle']), 0, -2), 0, '.', '.'),
                'responsabilidad_civil' => number_format(self::definirRCE($plan), 0, '.', '.'),
                'cubrimiento' => self::definirTotal($plan),
                'deducible' => self::definirParcial($plan),
                'conductores_elegidos' => self::definirConductoresElegidos($plan),
                'servicio_grua' => self::definirServicioGrua($plan),
                'categoria' => self::defineCategory($producto['codpla'])
            );
            array_push($productos, $producto);
        }
        // echo json_encode($productos);
        // die();
        // guardarAlerta($datos['cotizacion'], 'Equidad', '', 1);

        return json_encode($productos, JSON_UNESCAPED_UNICODE);
    }
}
