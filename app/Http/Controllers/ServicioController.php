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

    public function getPolizasByCliente($clienteId)
    {
        try {
            // Obtener todas las pólizas del cliente
            $polizas = Poliza::where('id_cliente', $clienteId)->get();

            // Calcular horas consumidas para cada póliza
            foreach ($polizas as $poliza) {
                $horasConsumidas = Servicio::where('id_poliza', $poliza->id)->sum('horas');
                $poliza->horas_consumidas = $horasConsumidas;
                $poliza->horas_disponibles = $poliza->total_horas - $horasConsumidas;
            }

            return response()->json($polizas);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener pólizas del cliente',
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
                'observaciones' => 'nullable|string',
                'id_poliza' => 'nullable|exists:polizas,id'
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

            // Verificar póliza si existe
            if (!empty($validated['id_poliza'])) {
                $poliza = Poliza::findOrFail($validated['id_poliza']);

                // Calcular horas consumidas
                $horasConsumidas = Servicio::where('id_poliza', $poliza->id)->sum('horas');
                $horasDisponibles = $poliza->total_horas - $horasConsumidas;

                if ($horasDisponibles < $validated['horas']) {
                    throw new \Exception("No hay suficientes horas disponibles. Disponibles: {$horasDisponibles}");
                }
            }

            $servicio = Servicio::create($validated);

            DB::commit();

            return response()->json([
                'message' => 'Servicio creado exitosamente',
                'servicio' => $servicio->load(['cliente', 'tecnico', 'poliza'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
                'error' => true
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

    public function getServiciosConPolizaByCliente($clienteId)
    {
        try {
            $servicios = Servicio::with(['poliza'])
                ->where('id_cliente', $clienteId)
                ->whereNotNull('id_poliza')
                ->whereNull('id_factura')  // Solo servicios no facturados
                ->get()
                ->map(function ($servicio) {
                    return [
                        'id' => $servicio->id,
                        'id_poliza' => $servicio->id_poliza,
                        'observaciones' => $servicio->observaciones,
                        'horas' => $servicio->horas,
                        'fecha' => $servicio->fecha,
                        'poliza' => [
                            'id' => $servicio->poliza->id,
                            'precio' => $servicio->poliza->precio,
                            'total_horas' => $servicio->poliza->total_horas,
                        ]
                    ];
                });

            return response()->json($servicios);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener servicios del cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getServicioByFactura($facturaId)
    {
        try {
            $servicio = Servicio::where('id_factura', $facturaId)
                ->with('poliza')
                ->first();

            if (!$servicio) {
                return response()->json(null);
            }

            return response()->json($servicio);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener el servicio',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateFacturaId(Request $request, $id)
    {
        try {
            // Validar que el id_factura existe
            $validated = $request->validate([
                'id_factura' => 'required|exists:facturas,id'
            ]);

            $servicio = Servicio::findOrFail($id);

            // Verificar que el servicio no tenga ya una factura asignada
            if ($servicio->id_factura && $servicio->id_factura != $validated['id_factura']) {
                throw new \Exception('El servicio ya tiene una factura asignada');
            }

            // Actualizar solo el campo id_factura
            $servicio->id_factura = $validated['id_factura'];
            $servicio->save();

            return response()->json([
                'message' => 'Id de factura actualizado exitosamente',
                'servicio' => $servicio->load(['factura'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar id de factura',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
