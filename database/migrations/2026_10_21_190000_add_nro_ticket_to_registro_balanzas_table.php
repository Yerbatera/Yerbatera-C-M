<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('registro_balanzas', 'nro_ticket')) {
            Schema::table('registro_balanzas', function (Blueprint $table) {
                $table->string('nro_ticket')->nullable()->after('id_materia');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('registro_balanzas', 'nro_ticket')) {
            Schema::table('registro_balanzas', function (Blueprint $table) {
                $table->dropColumn('nro_ticket');
            });
        }
    }
};
