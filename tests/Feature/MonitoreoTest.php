<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MonitoreoTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_sees_global_task_monitoring(): void
    {
        $operario = User::factory()->create(['name' => 'Operario de prueba']);

        DB::table('tareas')->insert([
            'nombre' => 'Control de secado',
            'id_operario' => $operario->id,
            'id_asignador' => $operario->id,
            'fecha_asignacion' => now(),
            'estado' => 'Pendiente',
            'descripcion' => 'Revisar humedad',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($operario)
            ->get(route('monitoreo'))
            ->assertOk()
            ->assertSee('Monitoreo Global de Actividades')
            ->assertSee('Operario de prueba')
            ->assertSee('Control de secado')
            ->assertSee('Revisar humedad');
    }
}
