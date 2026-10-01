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
        Schema::create('tareas', function (Blueprint $table) {
            $table->id('id_tarea');
            $table->string('nombre');
            $table->unsignedBigInteger('id_operario');
            $table->unsignedBigInteger('id_asignador');
            $table->dateTime('fecha_asignacion');
            $table->string('estado')->default('Pendiente');
            $table->text('descripcion')->nullable();
            $table->foreign('id_operario')->references('id')->on('users');
            $table->foreign('id_asignador')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};
