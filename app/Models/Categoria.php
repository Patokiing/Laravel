<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'id_usuario']; // Asegúrate de definir los campos correctos

    // Relación con subcategorías
    public function subcategorias()
    {
        return $this->hasMany(Subcategoria::class);
    }
}