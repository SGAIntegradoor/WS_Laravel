<?php

namespace App\Models\Salud;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AseguradosCotizacionesSalud extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'asegurados_cotizaciones_salud';
    protected $primaryKey = 'id_asegurado';

    public static function aseguradosCotizacion($id_cotizacion, $asegurados)
    {
        foreach ($asegurados as $asegurado) {
            $cedula = $asegurado['numeroDocumento'];
            $tipo_documento = $asegurado['tipoDocumento'];
            $nombre = $asegurado['nombre'] . ' ' . $asegurado['apellido'];
            $nacimiento = $asegurado['fechaNacimiento']['anio'] . '-' .
                str_pad($asegurado['fechaNacimiento']['mes'], 2, '0', STR_PAD_LEFT) . '-' .
                str_pad($asegurado['fechaNacimiento']['dia'], 2, '0', STR_PAD_LEFT);
            $edad = $asegurado['edad'];
            $genero = $asegurado['genero'];

            $aseguradoCotizacion = new self();
            $aseguradoCotizacion->id_cotizacion = $id_cotizacion;
            $aseguradoCotizacion->cedula_asegurado = $cedula;
            $aseguradoCotizacion->tipo_documento_asegurado = $tipo_documento;
            $aseguradoCotizacion->nom_asegurado = $nombre;
            $aseguradoCotizacion->fch_nac_asegurado = $nacimiento;
            $aseguradoCotizacion->edad_asegurado = $edad;
            $aseguradoCotizacion->genero_asegurado = $genero;
            $aseguradoCotizacion->ciudad = $asegurado['ciudad'];
            $aseguradoCotizacion->save();

            $ids_asegurados_insertados[] = $aseguradoCotizacion->id_asegurado;
        }
        return $ids_asegurados_insertados;
    }
}
