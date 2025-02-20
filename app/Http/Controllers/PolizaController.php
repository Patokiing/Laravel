<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poliza;

class PolizaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $polizas = Poliza::with('cliente')->get();
        return response()->json($polizas);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $poliza = new Poliza();
        $poliza->total_horas = $request->total_horas;
        $poliza->fecha_inicio = $request->fecha_inicio;
        $poliza->fecha_fin = $request->fecha_fin;
        $poliza->precio = $request->precio;
        $poliza->id_cliente = $request->id_cliente;
        $poliza->observaciones = $request->observaciones;
        $poliza->save();

        return response()->json(['message' => 'Póliza creada exitosamente', 'poliza' => $poliza]);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $poliza = Poliza::with('cliente')->findOrFail($id);
        return response()->json($poliza);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $poliza = Poliza::findOrFail($id);
        $poliza->total_horas = $request->total_horas;
        $poliza->fecha_inicio = $request->fecha_inicio;
        $poliza->fecha_fin = $request->fecha_fin;
        $poliza->precio = $request->precio;
        $poliza->id_cliente = $request->id_cliente;
        $poliza->observaciones = $request->observaciones;
        $poliza->save();

        return response()->json(['message' => 'Póliza actualizada exitosamente', 'poliza' => $poliza]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $poliza = Poliza::findOrFail($id);
        $poliza->delete();

        return response()->json(['message' => 'Póliza eliminada exitosamente']);
    }
}
