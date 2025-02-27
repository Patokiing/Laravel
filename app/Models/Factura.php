<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;

    protected $table = 'facturas';

    protected $fillable = [
        'id_cliente',
        'fecha',
        'monto',
        'observaciones'
    ];

    // Agregar casts para asegurar tipos de datos
    protected $casts = [
        'id' => 'integer',
        'id_cliente' => 'integer',
        'fecha' => 'datetime',
        'monto' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function cliente()
    {
        return $this->belongsTo(User::class, 'id_cliente')
                    ->where('rol', 'C'); // Solo usuarios con rol Cliente
    }

    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'id_factura', 'id');
    }
}
