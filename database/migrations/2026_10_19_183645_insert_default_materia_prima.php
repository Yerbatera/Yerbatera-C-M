<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    DB::table('materia_primas')->insertOrIgnore([
        'id'         => 1,
        'nombre'     => 'Hoja Verde',
        'tipo'       => 'Hoja Verde',
        'precio'     => 0.00,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
};
