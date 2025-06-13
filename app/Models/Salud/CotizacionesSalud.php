<?php

namespace App\Models\Salud;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CotizacionesSalud extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'cotizaciones_salud';
    protected $primaryKey = 'id_cotizacion';

    public static function nuevaCotizacion($tipoCotizacion, $cantidadAsegurados, $id_usuario)
    {
        $fecha = Carbon::now()->subHours(5)->toDateString();

        $nuevaCotizacion = new self();
        $nuevaCotizacion->num_asegurados = $cantidadAsegurados;
        $nuevaCotizacion->fecha_cotizacion = $fecha;
        $nuevaCotizacion->tipo_cotizacion = $tipoCotizacion;
        $nuevaCotizacion->id_usuario = $id_usuario;
        $nuevaCotizacion->save();

        return $nuevaCotizacion->id_cotizacion;
    }
}
