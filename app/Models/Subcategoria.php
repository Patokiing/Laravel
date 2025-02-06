<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subcategoria extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'categoria_id']; // Asegúrate de que 'categoria_id' sea fillable

    // Relación con la categoría
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}