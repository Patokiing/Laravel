<?php

namespace App\Http\Controllers;

use App\Models\Factura;
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
            $facturas = Factura::with(['cliente' => function($query) {
                $query->where('rol', 'C')
                      ->select('id', 'name', 'rfc', 'email', 'contacto');
            }])->orderBy('created_at', 'desc')->get();

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
            $factura = Factura::with(['cliente', 'servicios'])
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
                'observaciones' => 'nullable|string|max:255'
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

            $factura = Factura::create($validated);

            DB::commit();

            return response()->json([
                'message' => 'Factura creada exitosamente',
                'factura' => $factura->load('cliente')
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

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $factura = Factura::findOrFail($id);

            $validated = $request->validate([
                'id_cliente' => 'sometimes|integer|exists:users,id',
                'fecha' => 'sometimes|date',
                'monto' => 'sometimes|numeric|min:0',
                'observaciones' => 'nullable|string|max:255'
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

            $factura->update($validated);

            DB::commit();

            return response()->json([
                'message' => 'Factura actualizada exitosamente',
                'factura' => $factura->load('cliente')
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
}
