<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subcategoria;

class SubcategoriaController extends Controller {
    
     // Listar todas las subcategorías
     public function index() {
        $subcategorias = Subcategoria::with('categoria')->get();
        return response()->json($subcategorias);
    }
    

    // Crear una nueva subcategoría
    public function store(Request $request) {
        // Validar los datos
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
        ]);

        // Crear la subcategoría
        $subcategoria = Subcategoria::create($validated);
        return response()->json($subcategoria, 201);
    }

    // Mostrar una subcategoría específica
    public function show($id) {
        $subcategoria = Subcategoria::findOrFail($id);
        return $subcategoria;
    }

    // Actualizar una subcategoría
    public function update(Request $request, $id) {
        // Validar los datos
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
        ]);

        // Buscar y actualizar la subcategoría
        $subcategoria = Subcategoria::findOrFail($id);
        $subcategoria->update($validated);

        return response()->json($subcategoria, 200);
    }

    // Eliminar una subcategoría
    public function destroy($id) {
        $subcategoria = Subcategoria::findOrFail($id);
        $subcategoria->delete();

        return response()->json(null, 204);
    }
}