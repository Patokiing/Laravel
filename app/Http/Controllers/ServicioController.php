<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\Poliza;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServicioController extends Controller
{
    public function getTecnicos()
    {
        try {
            $tecnicos = User::where('rol', 'T')
                           ->select('id', 'name', 'email', 'telefono_contacto')
                           ->get();
            return response()->json($tecnicos);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener técnicos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getClientes()
    {
        try {
            $clientes = User::where('rol', 'C')
                           ->select('id', 'name', 'rfc', 'email', 'contacto')
                           ->get();
            return response()->json($clientes);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener clientes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        try {
            $servicios = Servicio::with([
                'cliente' => function($query) {
                    $query->where('rol', 'C')
                          ->select('id', 'name', 'rfc', 'email', 'contacto');
                },
                'tecnico' => function($query) {
                    $query->where('rol', 'T')
                          ->select('id', 'name', 'email', 'telefono_contacto');
                },
                'poliza',
                'factura'
            ])->get();

            return response()->json($servicios);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener servicios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'id_cliente' => 'required|exists:users,id',
                'id_tecnico' => 'required|exists:users,id',
                'fecha' => 'required|date',
                'horas' => 'required|numeric|min:0',
                'observaciones' => 'nullable|string'
            ]);

            // Verificar roles
            $cliente = User::where('id', $validated['id_cliente'])
                         ->where('rol', 'C')
                         ->first();

            $tecnico = User::where('id', $validated['id_tecnico'])
                          ->where('rol', 'T')
                          ->first();

            if (!$cliente || !$tecnico) {
                throw new \Exception('Cliente o técnico no válidos');
            }

            // Agregar póliza y factura al array validado si existen en la request
            if ($request->has('id_poliza')) {
                $validated['id_poliza'] = $request->id_poliza;
            }
            if ($request->has('id_factura')) {
                $validated['id_factura'] = $request->id_factura;
            }

            $servicio = Servicio::create($validated);

            DB::commit();

            return response()->json([
                'message' => 'Servicio creado exitosamente',
                'servicio' => $servicio->load(['cliente', 'tecnico', 'poliza', 'factura'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear el servicio',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function show($id)
    {
        $servicio = Servicio::with(['cliente', 'tecnico', 'poliza', 'factura'])->findOrFail($id);
        return response()->json($servicio);
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $servicio = Servicio::findOrFail($id);

            $validated = $request->validate([
                'id_cliente' => 'exists:users,id',
                'id_tecnico' => 'exists:users,id',
                'fecha' => 'date',
                'horas' => 'numeric|min:0',
                'observaciones' => 'nullable|string'
            ]);

            // Agregar póliza y factura si están presentes en la request
            if ($request->has('id_poliza')) {
                $validated['id_poliza'] = $request->id_poliza;
            }
            if ($request->has('id_factura')) {
                $validated['id_factura'] = $request->id_factura;
            }

            $servicio->update($validated);

            DB::commit();

            return response()->json([
                'message' => 'Servicio actualizado exitosamente',
                'servicio' => $servicio->load(['cliente', 'tecnico', 'poliza', 'factura'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar el servicio',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function destroy($id)
    {
        $servicio = Servicio::findOrFail($id);
        $servicio->delete();
        return response()->json(['message' => 'Servicio eliminado exitosamente']);
    }
}
