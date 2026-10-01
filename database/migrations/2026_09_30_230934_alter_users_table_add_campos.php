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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('id_contacto')->nullable()->after('id');
            $table->string('rol')->default('operario')->after('password');
            $table->string('estado')->default('Activo')->after('rol');
            $table->foreign('id_contacto')->references('id')->on('contactos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
