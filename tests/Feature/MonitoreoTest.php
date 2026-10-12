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

    public function test_selected_assignment_day_filters_tasks_and_shows_matching_daily_counts(): void
    {
        $this->travelTo('2026-10-05 12:00:00');

        $operario = User::factory()->create(['name' => 'Operario de prueba']);
        $otroOperario = User::factory()->create(['name' => 'Otro operario']);

        $this->createTask($operario, 'Pendiente del lunes', '2026-10-05 08:00:00');
        $this->createTask($operario, 'Completada del lunes', '2026-10-05 09:00:00', 'Completada');
        $this->createTask($operario, 'Pendiente del martes', '2026-10-06 08:00:00');
        $this->createTask($otroOperario, 'Pendiente del lunes de otro operario', '2026-10-05 10:00:00');

        $this->actingAs($operario)
            ->get(route('monitoreo', [
                'estado' => 'pendiente',
                'operario' => $operario->id,
                'dia' => '2026-10-05',
                'semana' => '2026-10-05',
            ]))
            ->assertOk()
            ->assertSee('Pendiente del lunes')
            ->assertDontSee('Completada del lunes')
            ->assertDontSee('Pendiente del martes')
            ->assertDontSee('Pendiente del lunes de otro operario')
            ->assertDontSee('Dom')
            ->assertSee('estado=pendiente&amp;operario='.$operario->id, false)
            ->assertSee('dia=2026-10-05&amp;semana=2026-10-05', false)
            ->assertSeeInOrder(['Lun', '5', '1', 'Mar', '6', '1']);
    }

    public function test_invalid_day_query_is_rejected(): void
    {
        $operario = User::factory()->create();

        $this->actingAs($operario)
            ->get(route('monitoreo', ['dia' => '2026-13-40']))
            ->assertSessionHasErrors('dia');
    }

    public function test_employee_filter_lists_only_production_operarios_in_alphabetical_order(): void
    {
        $gerente = $this->createUserWithRole('Gerente', 'gerente');
        $this->createUserWithRole('Zulema Producción', 'operario');
        $this->createUserWithRole('Ana Producción', 'operario');
        $this->createUserWithRole('Yerbatero', 'yerbatero');
        $this->createUserWithRole('Administración', 'administracion');
        $this->createUserWithRole('Ventas', 'ventas');
        $this->createUserWithRole('Balanza', 'balanza');

        $this->actingAs($gerente)
            ->get(route('monitoreo'))
            ->assertOk()
            ->assertSee('Todos los empleados')
            ->assertSee('Empleado')
            ->assertSeeInOrder(['Ana Producción', 'Zulema Producción'])
            ->assertDontSee('Yerbatero')
            ->assertDontSee('Administración')
            ->assertDontSee('Ventas')
            ->assertDontSee('Balanza');
    }

    public function test_all_days_shows_tasks_from_any_date_and_week_navigation_keeps_filters(): void
    {
        $this->travelTo('2026-10-05 12:00:00');

        $operario = User::factory()->create();

        $this->createTask($operario, 'Tarea de septiembre', '2026-09-30 08:00:00');
        $this->createTask($operario, 'Tarea de octubre', '2026-10-07 08:00:00');

        $this->actingAs($operario)
            ->get(route('monitoreo', [
                'estado' => 'pendiente',
                'operario' => $operario->id,
                'semana' => '2026-10-05',
            ]))
            ->assertOk()
            ->assertSee('Tarea de septiembre')
            ->assertSee('Tarea de octubre')
            ->assertSee('>2</span>', false)
            ->assertSee('estado=pendiente&amp;operario='.$operario->id.'&amp;semana=2026-09-28', false)
            ->assertSee('estado=pendiente&amp;operario='.$operario->id.'&amp;semana=2026-10-12', false);
    }

    private function createTask(User $operario, string $nombre, string $fechaAsignacion, string $estado = 'Pendiente'): void
    {
        DB::table('tareas')->insert([
            'nombre' => $nombre,
            'id_operario' => $operario->id,
            'id_asignador' => $operario->id,
            'fecha_asignacion' => $fechaAsignacion,
            'estado' => $estado,
            'descripcion' => null,
            'created_at' => $fechaAsignacion,
            'updated_at' => $fechaAsignacion,
        ]);
    }

    private function createUserWithRole(string $name, string $role): User
    {
        $user = User::factory()->create(['name' => $name]);

        DB::table('users')
            ->where('id', $user->id)
            ->update(['rol' => $role]);

        return $user->refresh();
    }
}
