<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeticionesAseguradoras extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'peticiones_aseguradoras';

    public static function guardarPeticion($aseguradora, $placa, $cotizacion, $request, $response)
    {
        $peticionModel = new self();
        $peticionModel->peticion    = self::pegarComillasRequest($aseguradora, $request);
        $peticionModel->respuesta   = $response;
        $peticionModel->cotizacion  = $cotizacion;
        $peticionModel->aseguradora = $aseguradora;
        $peticionModel->placa       = $placa;

        return $peticionModel->save();
    }

    protected static function pegarComillasRequest($aseguradora, $request)
    {
        return "'" . $request . "'";
    }
}
