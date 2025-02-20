<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poliza extends Model
{
    use HasFactory;

    protected $table = 'polizas';

    protected $fillable = [
        'total_horas',
        'fecha_inicio',
        'fecha_fin',
        'precio',
        'id_cliente',
        'observaciones'
    ];

    /**
     * Obtiene el cliente asociado a la póliza
     */
    public function cliente()
    {
        return $this->belongsTo(User::class, 'id_cliente');
    }
}
