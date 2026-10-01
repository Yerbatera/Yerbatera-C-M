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
        Schema::create('lotexticket', function (Blueprint $table) {
            $table->unsignedBigInteger('id_lote');
            $table->unsignedBigInteger('id_ticket');
            $table->decimal('pesoxkg', 10, 2);
            $table->foreign('id_lote')->references('id')->on('lotes');
            $table->foreign('id_ticket')->references('id')->on('registro_balanzas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotexticket');
    }
};
