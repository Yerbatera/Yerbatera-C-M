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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha');
            $table->unsignedBigInteger('id_cliente');
            $table->unsignedBigInteger('id_lote');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio_kg', 10, 2);
            $table->decimal('total_venta', 12, 2);
            $table->string('punto_venta');
            $table->foreign('id_cliente')->references('id')->on('contactos');
            $table->foreign('id_lote')->references('id')->on('lotes');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
