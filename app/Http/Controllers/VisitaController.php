<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visita;
use Illuminate\Support\Facades\Http;

class VisitaController extends Controller
{
    public function index(Request $request)
    {
        $limite = 10;  // Número de registros por página
        $busqueda = $request->input('busqueda');
        $fecha = $request->input('fecha');

        // Consulta con filtro por búsqueda y fecha
        $query = Visita::whereNull('hora_salida')->orWhere('hora_salida', '');
        if ($fecha) {
            $query->whereDate('fecha', $fecha);
        }
        if ($busqueda) {
            $query->where(function($q) use ($busqueda) {
                $q->where('nombre', 'LIKE', "%{$busqueda}%")
                  ->orWhere('dni', 'LIKE', "%{$busqueda}%")
                  ->orWhere('smotivo', 'LIKE', "%{$busqueda}%")
                  ->orWhere('lugar', 'LIKE', "%{$busqueda}%");
            });
        }

        $visitas = $query->paginate($limite);
        return view('visitas', compact('visitas', 'busqueda', 'fecha'));
    }

    public function buscarDNI(Request $request)
    {
        $dni = $request->input('dni');
        $token = 'apis-token-10779.deFjdQHVSuenRlLS27jpqtmQ0SJV4hfj';
        $response = Http::withHeaders([
            'Authorization' => "Bearer $token"
        ])->get("https://api.apis.net.pe/v2/reniec/dni?numero={$dni}");

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'No se encontró el DNI'], 404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'dni' => 'required|max:8',
            'nombre' => 'required|string|max:255',
            'tipopersona' => 'required',
            'lugar' => 'required',
            'smotivo' => 'required',
        ]);

        Visita::create($request->all());
        return redirect()->route('visitas.index')->with('success', 'Visita registrada correctamente');
    }
}
