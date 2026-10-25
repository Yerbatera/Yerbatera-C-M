<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('turnos') && ! Schema::hasColumn('turnos', 'franja_horaria')) {
            Schema::table('turnos', function (Blueprint $table) {
                $table->string('franja_horaria')->nullable()->after('turno');
            });
        }

        if (Schema::hasTable('tareas')) {
            Schema::table('tareas', function (Blueprint $table) {
                if (! Schema::hasColumn('tareas', 'relevancia')) {
                    $table->string('relevancia')->default('Media')->after('estado');
                }
                if (! Schema::hasColumn('tareas', 'es_recurrente')) {
                    $table->boolean('es_recurrente')->default(false)->after('relevancia');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('turnos') && Schema::hasColumn('turnos', 'franja_horaria')) {
            Schema::table('turnos', function (Blueprint $table) {
                $table->dropColumn('franja_horaria');
            });
        }

        if (Schema::hasTable('tareas')) {
            Schema::table('tareas', function (Blueprint $table) {
                if (Schema::hasColumn('tareas', 'es_recurrente')) {
                    $table->dropColumn('es_recurrente');
                }
                if (Schema::hasColumn('tareas', 'relevancia')) {
                    $table->dropColumn('relevancia');
                }
            });
        }
    }
};
