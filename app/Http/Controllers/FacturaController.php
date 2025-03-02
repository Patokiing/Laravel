<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FacturaController extends Controller
{
    public function getClientes()
    {
        try {
            $clientes = User::where('rol', 'C')
                ->select('id', 'name', 'rfc', 'email', 'contacto', 'telefono_contacto')
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
            $facturas = Factura::with(['cliente' => function ($query) {
                $query->where('rol', 'C')
                    ->select('id', 'name', 'rfc', 'email', 'contacto');
            }, 'servicios', 'servicios.poliza'])->orderBy('created_at', 'desc')->get();

            return response()->json($facturas);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener facturas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        // Asegurar que $id sea un entero
        $id = intval($id);

        try {
            $factura = Factura::with(['cliente', 'servicios', 'servicios.poliza'])
                ->findOrFail($id);
            return response()->json($factura);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Factura no encontrada'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'id_cliente' => 'required|integer|exists:users,id',
                'fecha' => 'required|date',
                'monto' => 'required|numeric|min:0',
                'observaciones' => 'nullable|string|max:255',
                'id_servicio' => 'required|integer|exists:servicios,id' // Agregado validación para id_servicio
            ]);

            // Verificar que el cliente tenga rol 'C'
            $cliente = User::where('id', $validated['id_cliente'])
                ->where('rol', 'C')
                ->first();

            if (!$cliente) {
                throw ValidationException::withMessages([
                    'id_cliente' => ['El usuario seleccionado no es un cliente válido']
                ]);
            }

            // Crear la factura
            $factura = Factura::create([
                'id_cliente' => $validated['id_cliente'],
                'fecha' => $validated['fecha'],
                'monto' => $validated['monto'],
                'observaciones' => $validated['observaciones'] ?? null
            ]);

            // Actualizar el servicio con el id de la factura creada
            $servicio = Servicio::findOrFail($validated['id_servicio']);

            // Verificar que el servicio no esté facturado
            if ($servicio->id_factura) {
                throw new \Exception('El servicio ya está facturado');
            }

            // Verificar que el servicio pertenezca al cliente
            if ($servicio->id_cliente !== $validated['id_cliente']) {
                throw new \Exception('El servicio no pertenece al cliente seleccionado');
            }

            $servicio->update(['id_factura' => $factura->id]);

            DB::commit();

            return response()->json([
                'message' => 'Factura creada exitosamente',
                'factura' => $factura->load(['cliente', 'servicios'])
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear la factura',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id)
{
    try {
        DB::beginTransaction();

        // Encontrar la factura
        $factura = Factura::findOrFail($id);

        // Buscar el servicio vinculado
        $servicio = Servicio::where('id_factura', $factura->id)->first();

        // Si existe un servicio vinculado, desvincularlo
        if ($servicio) {
            $servicio->update(['id_factura' => null]);
        }

        // Eliminar la factura
        $factura->delete();

        DB::commit();

        return response()->json([
            'message' => 'Factura eliminada exitosamente'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Error al eliminar la factura',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $factura = Factura::findOrFail($id);

            $validated = $request->validate([
                'id_cliente' => 'sometimes|integer|exists:users,id',
                'fecha' => 'sometimes|date',
                'monto' => 'sometimes|numeric|min:0',
                'observaciones' => 'nullable|string|max:255',
                'id_servicio' => 'required|integer|exists:servicios,id' // Agregado validación para id_servicio
            ]);

            if (isset($validated['id_cliente'])) {
                $cliente = User::where('id', $validated['id_cliente'])
                    ->where('rol', 'C')
                    ->first();

                if (!$cliente) {
                    throw ValidationException::withMessages([
                        'id_cliente' => ['El usuario seleccionado no es un cliente válido']
                    ]);
                }
            }

            // Actualizar la factura
            $factura->update($validated);

            // Buscar y actualizar el servicio
            $servicio = Servicio::findOrFail($validated['id_servicio']);

            // Verificar que el servicio no esté facturado por otra factura
            if ($servicio->id_factura && $servicio->id_factura !== $id) {
                throw new \Exception('El servicio ya está asignado a otra factura');
            }

            // Verificar que el servicio pertenezca al cliente
            if ($servicio->id_cliente !== $validated['id_cliente']) {
                throw new \Exception('El servicio no pertenece al cliente seleccionado');
            }

            // Actualizar el servicio con el id de la factura
            $servicio->update(['id_factura' => $id]);

            DB::commit();

            return response()->json([
                'message' => 'Factura actualizada exitosamente',
                'factura' => $factura->load(['cliente', 'servicios'])
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar la factura',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getFacturaParaPDF($id)
    {
        try {
            // Obtener la factura
            $factura = Factura::select(
                'id',
                'fecha',
                'monto',
                'observaciones',
                'id_cliente'
            )->findOrFail($id);

            // Obtener el cliente vinculado
            $cliente = User::where('id', $factura->id_cliente)
                ->where('rol', 'C')
                ->select('id', 'name', 'rfc', 'email', 'contacto', 'telefono_contacto')
                ->firstOrFail();

            // Obtener el servicio vinculado
            $servicio = Servicio::where('id_factura', $factura->id)
                ->select(
                    'id',
                    'fecha',
                    'horas',
                    'observaciones',
                    'id_factura',
                    'id_poliza',
                    'id_cliente'
                )
                ->firstOrFail();

            return response()->json([
                'factura' => [
                    'id' => $factura->id,
                    'fecha' => $factura->fecha,
                    'monto' => $factura->monto,
                    'observaciones' => $factura->observaciones,
                    'cliente' => [
                        'id' => $cliente->id,
                        'nombre' => $cliente->name,
                        'rfc' => $cliente->rfc,
                        'email' => $cliente->email,
                        'contacto' => $cliente->contacto,
                        'telefono' => $cliente->telefono_contacto
                    ],
                    'servicio' => [
                        'id' => $servicio->id,
                        'fecha' => $servicio->fecha,
                        'horas' => $servicio->horas,
                        'observaciones' => $servicio->observaciones,
                        'id_poliza' => $servicio->id_poliza
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los datos de la factura',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

