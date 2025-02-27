<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $table = 'servicios';
    protected $fillable = [
        'id_cliente',
        'id_tecnico',
        'fecha',
        'horas',
        'observaciones',
        'id_poliza',
        'id_factura'
    ];

    public function cliente()
    {
        return $this->belongsTo(User::class, 'id_cliente');
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'id_tecnico');
    }

    public function poliza()
    {
        return $this->belongsTo(Poliza::class, 'id_poliza');
    }

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'id_factura');
    }
}
