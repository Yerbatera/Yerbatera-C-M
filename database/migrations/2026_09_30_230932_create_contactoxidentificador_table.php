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
        Schema::create('contactoxidentificador', function (Blueprint $table) {
            $table->unsignedBigInteger('id_identificador');
            $table->unsignedBigInteger('id_contacto');
            $table->string('valor');
            $table->foreign('id_identificador')->references('id')->on('identificadores')->onDelete('cascade');
            $table->foreign('id_contacto')->references('id')->on('contactos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contactoxidentificador');
    }
};
