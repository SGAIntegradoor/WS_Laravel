<?php

namespace App\Http\Controllers\ramos\salud\Bolivar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Salud\CotizacionesSalud;
use App\Models\Salud\TomadoresCotizacionesSalud;
use App\Models\Salud\AseguradosCotizacionesSalud;
use App\Models\Salud\PlanesCotizacionesSalud;

class BolivarController extends Controller
{
    /**
     * Muestra mensaje de bienvenida al módulo de Salud de Bolívar.
     *
     * @return void
     */
    public function index()
    {
        echo "Bienvenido al módulo de Salud de Bolívar";
    }

    /**
     * Realiza el proceso de cotización de salud para Bolívar.
     * 
     * - Crea la cotización principal.
     * - Guarda el tomador y los asegurados.
     * - Consulta y aplica descuentos a los planes de cada asegurado.
     * - Guarda los planes asociados a cada asegurado.
     * - Retorna la estructura final con los asegurados y sus planes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cotizar(Request $request)
    {
        // Extraer datos principales del request
        $tipoCotizacion = (int) $request->input('tipoCotizacion');
        $id_usuario = (int) $request->input('id_usuario');
        $asegurados = $request->input('asegurados');
        $tomador = $request->input('tomador');
        $aseguradosConPlanes = [];

        // Calcular si la mayoría de asegurados son de género masculino (1)
        $totalAsegurados = count($asegurados);
        $genero1Count = 0;
        foreach ($asegurados as $asegurado) {
            if ((int)$asegurado['genero'] === 1) {
                $genero1Count++;
            }
        }
        $mayoriaGenero1 = $genero1Count > ($totalAsegurados / 2);

        // Crear una nueva cotización en la tabla cotizaciones_salud
        $id_nueva_cotizacion = CotizacionesSalud::nuevaCotizacion(
            $tipoCotizacion,
            $totalAsegurados,
            $id_usuario
        );

        // Guardar la información del tomador en la tabla correspondiente
        TomadoresCotizacionesSalud::tomadorCotizacion(
            $tomador['numeroDocumento'],
            $tomador['tipoDocumento'],
            ($tomador['nombre'] . ' ' . $tomador['apellido']),
            $id_nueva_cotizacion
        );

        // Guardar los asegurados en la tabla correspondiente y obtener sus IDs
        $id_asegurados_insertados = AseguradosCotizacionesSalud::aseguradosCotizacion(
            $id_nueva_cotizacion,
            $asegurados
        );

        $i = 0;
        // Procesar cada asegurado para consultar y asignar planes
        foreach ($asegurados as $asegurado) {
            $edad = (int) $asegurado['edad'];
            $genero = (int) $asegurado['genero'];
            $ciudad = $asegurado['ciudad'];

            // Seleccionar la tabla de precios según la ciudad
            if ($ciudad == '08001') { // Barranquilla
                $tablaConsulta = 'precios_planes_bolivar_barranquilla_salud';
            } else {
                $tablaConsulta = 'precios_planes_salud';
            }

            // Consultar los planes disponibles para el asegurado
            $planes = DB::table($tablaConsulta . ' as p')
                ->join('planes_salud as ps', 'ps.id_plan', '=', 'p.id_plan')
                ->join('aseguradoras_salud as ass', 'ass.id_aseguradora', '=', 'ps.id_aseguradora')
                ->select(
                    'p.id_plan as plan_id',
                    'ps.nombre',
                    DB::raw('IFNULL(p.mensual, 0) as mensual'),
                    DB::raw('IFNULL(p.trimestral, 0) as trimestral'),
                    DB::raw('IFNULL(p.semestral, 0) as semestral'),
                    DB::raw('IFNULL(p.anual, 0) as anual'),
                    'p.id_tipo_cotizacion as tipo_cotizacion_id'
                )
                ->whereRaw('? BETWEEN p.edad_minima AND p.edad_maxima', [$edad])
                ->where('p.id_genero', $genero)
                ->get();

            // Aplicar descuentos según el tipo de cotización y mayoría de género
            foreach ($planes as $plan) {
                $descuento = 1.0;
                if ($tipoCotizacion == 2 && $mayoriaGenero1) {
                    $descuento *= 0.85; // 15% descuento
                } else if ($tipoCotizacion == 2) {
                    $descuento *= 0.9; // 10% descuento
                }
                if (is_numeric($plan->mensual)) {
                    $plan->mensual = round($plan->mensual * $descuento, 2);
                }
                if (is_numeric($plan->trimestral)) {
                    $plan->trimestral = round($plan->trimestral * $descuento, 2);
                }
                if (is_numeric($plan->semestral)) {
                    $plan->semestral = round($plan->semestral * $descuento, 2);
                }
                if (is_numeric($plan->anual)) {
                    $plan->anual = round($plan->anual * $descuento, 2);
                }
            }

            // Convertir todos los campos de planes (excepto tipo_cotizacion_id) a texto
            foreach ($planes as $plan) {
                $plan->plan_id = (string) $plan->plan_id;
                $plan->nombre = (string) $plan->nombre;
                $plan->mensual = number_format((float)$plan->mensual, 2, '.', '');
                $plan->trimestral = number_format((float)$plan->trimestral, 2, '.', '');
                $plan->semestral = number_format((float)$plan->semestral, 2, '.', '');
                $plan->anual = number_format((float)$plan->anual, 2, '.', '');
                // $plan->tipo_cotizacion_id se deja como está
            }

            // Agregar los planes al asegurado
            $asegurado['planes'] = $planes;
            $aseguradosConPlanes[] = $asegurado;

            // Guardar los planes en la tabla planes_cotizaciones_salud
            PlanesCotizacionesSalud::planesCotizacion(
                $id_nueva_cotizacion,
                $id_asegurados_insertados[$i],
                $planes->toArray(),
                $tipoCotizacion
            );
            $i++;
        }

        // Retornar la respuesta con los asegurados y sus planes
        return response()->json([
            'asegurados' => $aseguradosConPlanes
        ]);
    }
}
