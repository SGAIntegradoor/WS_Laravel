<?php

namespace App\Models\Salud;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanesCotizacionesSalud extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'planes_cotizaciones_salud';

    public static function planesCotizacion($id_cotizacion, $id_asegurado, $planes, $tipoCotizacion)
    {

        foreach ($planes as $plan) {
            $planCotizacion = new self();
            $planCotizacion->id_cotizacion = $id_cotizacion;
            $planCotizacion->id_plan = $plan->plan_id;
            $planCotizacion->id_asegurado = $id_asegurado;
            $planCotizacion->nombre_plan = $plan->nombre;
            $planCotizacion->mensual_plan = $plan->mensual;
            $planCotizacion->trimestral_plan = $plan->trimestral;
            $planCotizacion->semestral_plan = $plan->semestral;
            $planCotizacion->anual_plan = $plan->anual;
            $planCotizacion->tipo_cotizacion = $tipoCotizacion;
            $planCotizacion->save();
        }

        return true;
    }
}
