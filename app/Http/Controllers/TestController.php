<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;

class TestController extends Controller
{
    public function index()
    {
        $usuarios = Usuario::where('usu_documento', 1143875526)->first();
        //$usuarios = Usuario::all();
        //dd($usuarios->usu_nombre);
        return view('welcome', compact('usuarios'));
        // return $usuarios->toArray();
    }
}
