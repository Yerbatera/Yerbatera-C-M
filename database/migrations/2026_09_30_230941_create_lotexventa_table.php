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
        Schema::create('lotexventa', function (Blueprint $table) {
            $table->unsignedBigInteger('id_lote');
            $table->unsignedBigInteger('id_venta');
            $table->decimal('pesoxkg', 10, 2);
            $table->foreign('id_lote')->references('id')->on('lotes');
            $table->foreign('id_venta')->references('id')->on('ventas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotexventa');
    }
};
