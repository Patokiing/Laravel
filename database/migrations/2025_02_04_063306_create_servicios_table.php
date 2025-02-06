<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_cliente')->constrained('users');
            $table->foreignId('id_tecnico')->constrained('users');
            $table->dateTime('fecha');
            $table->decimal('horas', 18, 2);
            $table->text('observaciones')->nullable();
            $table->foreignId('id_poliza')->nullable()->constrained('polizas');
            $table->foreignId('id_factura')->nullable()->constrained('facturas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};
