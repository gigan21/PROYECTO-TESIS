<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TiendaController extends Controller
{
    public function index()
    {
        // Por ahora solo devolvemos la vista
        // Después aquí cargaremos los ítems, monedas del jugador, etc.
        return view('estudiante.tienda');
    }
}