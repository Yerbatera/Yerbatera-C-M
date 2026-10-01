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
        Schema::create('procesos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_proceso');
            $table->unsignedBigInteger('id_encargado');
            $table->string('fecha');
            $table->decimal('temperatura', 5, 2)->nullable();
            $table->integer('vuelta_torax')->nullable();
            $table->unsignedBigInteger('id_lote');
            $table->foreign('id_encargado')->references('id')->on('users');
            $table->foreign('id_lote')->references('id')->on('lotes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procesos');
    }
};
