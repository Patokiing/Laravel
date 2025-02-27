<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\Poliza;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    public function index()
    {
        $servicios = Servicio::with(['cliente', 'tecnico', 'poliza', 'factura'])->get();
        return response()->json($servicios);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_cliente' => 'required|exists:users,id',
            'id_tecnico' => 'required|exists:users,id',
            'fecha' => 'required|date',
            'horas' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string',
            'id_poliza' => 'nullable|exists:polizas,id',
            'id_factura' => 'nullable|exists:facturas,id'
        ]);

        // Verificar horas disponibles si hay póliza
        if ($request->id_poliza) {
            $poliza = Poliza::findOrFail($request->id_poliza);
            $horasDisponibles = $poliza->total_horas - $poliza->horas_consumidas;

            if ($request->horas > $horasDisponibles) {
                return response()->json([
                    'message' => 'Horas solicitadas exceden las disponibles en la póliza'
                ], 400);
            }

            // Actualizar horas consumidas
            $poliza->horas_consumidas += $request->horas;
            $poliza->save();
        }

        $servicio = Servicio::create($request->all());
        return response()->json([
            'message' => 'Servicio creado exitosamente',
            'servicio' => $servicio
        ], 201);
    }

    public function show($id)
    {
        $servicio = Servicio::with(['cliente', 'tecnico', 'poliza', 'factura'])->findOrFail($id);
        return response()->json($servicio);
    }

    public function update(Request $request, $id)
    {
        $servicio = Servicio::findOrFail($id);

        $request->validate([
            'id_cliente' => 'exists:users,id',
            'id_tecnico' => 'exists:users,id',
            'fecha' => 'date',
            'horas' => 'numeric|min:0',
            'observaciones' => 'nullable|string',
            'id_poliza' => 'nullable|exists:polizas,id',
            'id_factura' => 'nullable|exists:facturas,id'
        ]);

        $servicio->update($request->all());
        return response()->json([
            'message' => 'Servicio actualizado exitosamente',
            'servicio' => $servicio
        ]);
    }

    public function destroy($id)
    {
        $servicio = Servicio::findOrFail($id);
        $servicio->delete();
        return response()->json(['message' => 'Servicio eliminado exitosamente']);
    }
}
