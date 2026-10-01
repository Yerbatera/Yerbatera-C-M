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
        Schema::create('registro_balanzas', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha_hora');
            $table->unsignedBigInteger('id_proveedor');
            $table->unsignedBigInteger('id_operario');
            $table->unsignedBigInteger('id_materia');
            $table->string('remito')->nullable();
            $table->decimal('peso_bruto', 10, 2);
            $table->decimal('peso_tara', 10, 2);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('peso_neto', 10, 2);
            $table->decimal('importe_total', 12, 2);
            $table->string('concepto_descuento')->nullable();
            $table->foreign('id_proveedor')->references('id')->on('contactos');
            $table->foreign('id_operario')->references('id')->on('users');
            $table->foreign('id_materia')->references('id')->on('materia_primas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_balanzas');
    }
};
