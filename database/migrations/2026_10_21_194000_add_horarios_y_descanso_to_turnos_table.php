<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('turnos')) {
            Schema::table('turnos', function (Blueprint $table) {
                if (! Schema::hasColumn('turnos', 'horario_entrada')) {
                    $table->string('horario_entrada')->nullable()->after('franja_horaria');
                }
                if (! Schema::hasColumn('turnos', 'horario_salida')) {
                    $table->string('horario_salida')->nullable()->after('horario_entrada');
                }
                if (! Schema::hasColumn('turnos', 'descanso')) {
                    $table->string('descanso')->nullable()->after('horario_salida');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('turnos')) {
            Schema::table('turnos', function (Blueprint $table) {
                $columnas = array_filter(
                    ['horario_entrada', 'horario_salida', 'descanso'],
                    fn (string $col): bool => Schema::hasColumn('turnos', $col)
                );
                if ($columnas !== []) {
                    $table->dropColumn($columnas);
                }
            });
        }
    }
};
