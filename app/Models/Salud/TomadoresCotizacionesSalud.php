<?php

namespace App\Models\Salud;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TomadoresCotizacionesSalud extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'tomadores_cotizaciones_salud';
    protected $primaryKey = 'id_cotizacion';

    public static function tomadorCotizacion($documento, $tipoDocuemtno, $nombre, $id_cotizacion)
    {
        $tomadorCotizacion = new self();
        $tomadorCotizacion->id_tomador = $documento;
        $tomadorCotizacion->tipo_documento = $tipoDocuemtno;
        $tomadorCotizacion->nombre_tomador = $nombre;
        $tomadorCotizacion->id_cotizacion = $id_cotizacion;

        return $tomadorCotizacion->save();
    }
}
