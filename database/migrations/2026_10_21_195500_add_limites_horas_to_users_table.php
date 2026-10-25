<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'horas_max_dia')) {
                    $table->decimal('horas_max_dia', 4, 1)->default(8.0)->after('estado');
                }
                if (! Schema::hasColumn('users', 'horas_max_semana')) {
                    $table->decimal('horas_max_semana', 5, 1)->default(48.0)->after('horas_max_dia');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $columnas = array_filter(
                    ['horas_max_dia', 'horas_max_semana'],
                    fn (string $col): bool => Schema::hasColumn('users', $col)
                );
                if ($columnas !== []) {
                    $table->dropColumn($columnas);
                }
            });
        }
    }
};
