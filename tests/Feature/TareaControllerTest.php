<?php

namespace Tests\Feature;

use App\Models\Turno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TareaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_task_is_saved_with_pending_status(): void
    {
        $gerente = $this->createUserWithRole('Gerente', 'gerente');
        $operario = User::factory()->create(['name' => 'Operario de prueba']);

        $response = $this->actingAs($gerente)->post(route('tareas.asignar.store'), [
            'empleado' => '  oPeRaRiO de prueba  ',
            'nombre' => 'Control de secado',
            'descripcion' => 'Revisar humedad',
            'fecha_asignacion' => '2026-10-12',
        ]);

        $response->assertRedirect(route('tareas.asignar'));
        $response->assertSessionHas('success', 'La tarea se asignó correctamente.');
        $response->assertSessionHas('warning', 'El empleado no tiene un turno asignado para esa fecha. La tarea se guardó igualmente.');
        $this->assertDatabaseHas('tareas', [
            'nombre' => 'Control de secado',
            'id_operario' => $operario->id,
            'id_asignador' => $gerente->id,
            'fecha_asignacion' => '2026-10-12 00:00:00',
            'estado' => 'Pendiente',
            'descripcion' => 'Revisar humedad',
        ]);
    }

    public function test_task_assignment_rejects_an_unregistered_employee(): void
    {
        $gerente = $this->createUserWithRole('Gerente', 'gerente');

        $this->followingRedirects()
            ->actingAs($gerente)
            ->from(route('tareas.asignar'))
            ->post(route('tareas.asignar.store'), [
                'empleado' => 'Empleado inexistente',
                'nombre' => 'Control de secado',
                'descripcion' => 'Revisar humedad',
                'fecha_asignacion' => '2026-10-12',
            ])
            ->assertOk()
            ->assertSee('No hay un empleado registrado con ese nombre')
            ->assertSee('value="Empleado inexistente"', false);

        $this->assertDatabaseCount('tareas', 0);
    }

    public function test_task_assignment_rejects_duplicate_production_employee_names(): void
    {
        $gerente = $this->createUserWithRole('Gerente', 'gerente');
        User::factory()->create(['name' => 'Empleado repetido']);
        User::factory()->create(['name' => 'Empleado repetido']);

        $this->actingAs($gerente)
            ->from(route('tareas.asignar'))
            ->post(route('tareas.asignar.store'), [
                'empleado' => ' empleado repetido ',
                'nombre' => 'Control de secado',
                'descripcion' => 'Revisar humedad',
                'fecha_asignacion' => '2026-10-12',
            ])
            ->assertRedirect(route('tareas.asignar'))
            ->assertSessionHasErrors([
                'empleado' => 'Hay más de un empleado con ese nombre; diferenciá cuál querés asignar.',
            ]);

        $this->assertDatabaseCount('tareas', 0);
    }

    public function test_turno_is_saved_and_success_message_is_returned(): void
    {
        $gerente = $this->createUserWithRole('Gerente', 'gerente');
        $operario = User::factory()->create(['name' => 'Operario de prueba']);

        $this->actingAs($gerente)
            ->post(route('turnos.store'), [
                'empleado' => 'Operario de prueba',
                'fecha' => '2026-10-12',
                'turno' => 'mañana',
            ])
            ->assertRedirect(route('turnos.index'))
            ->assertSessionHas('success', 'El turno se registró correctamente.');

        $this->assertDatabaseHas('turnos', [
            'user_id' => $operario->id,
            'turno' => 'mañana',
        ]);
        $this->assertTrue(
            Turno::query()
                ->where('user_id', $operario->id)
                ->whereDate('fecha', '2026-10-12')
                ->exists()
        );
    }

    public function test_turno_cannot_be_assigned_to_a_non_production_user(): void
    {
        $gerente = $this->createUserWithRole('Gerente', 'gerente');
        $administracion = $this->createUserWithRole('Administración', 'administracion');

        $this->actingAs($gerente)
            ->from(route('turnos.index'))
            ->post(route('turnos.store'), [
                'empleado' => 'Administración',
                'fecha' => '2026-10-12',
                'turno' => 'mañana',
            ])
            ->assertRedirect(route('turnos.index'))
            ->assertSessionHasErrors('empleado');

        $this->assertDatabaseCount('turnos', 0);
    }

    public function test_turno_cannot_be_registered_twice_for_the_same_employee_and_date(): void
    {
        $gerente = $this->createUserWithRole('Gerente', 'gerente');
        $operario = User::factory()->create(['name' => 'Operario de prueba']);

        Turno::factory()->create([
            'user_id' => $operario->id,
            'fecha' => '2026-10-12',
            'turno' => 'mañana',
        ]);

        $this->actingAs($gerente)
            ->from(route('turnos.index'))
            ->post(route('turnos.store'), [
                'empleado' => 'Operario de prueba',
                'fecha' => '2026-10-12',
                'turno' => 'tarde',
            ])
            ->assertRedirect(route('turnos.index'))
            ->assertSessionHasErrors('fecha');

        $this->assertDatabaseCount('turnos', 1);
    }

    public function test_turno_assignment_rejects_duplicate_production_employee_names(): void
    {
        $gerente = $this->createUserWithRole('Gerente', 'gerente');
        User::factory()->create(['name' => 'Empleado repetido']);
        User::factory()->create(['name' => 'Empleado repetido']);

        $this->actingAs($gerente)
            ->from(route('turnos.index'))
            ->post(route('turnos.store'), [
                'empleado' => 'Empleado repetido',
                'fecha' => '2026-10-12',
                'turno' => 'mañana',
            ])
            ->assertRedirect(route('turnos.index'))
            ->assertSessionHasErrors([
                'empleado' => 'Hay más de un empleado con ese nombre; diferenciá cuál querés asignar.',
            ]);

        $this->assertDatabaseCount('turnos', 0);
    }

    public function test_only_administration_and_manager_roles_can_access_assignment_forms(): void
    {
        $operario = User::factory()->create(['name' => 'Operario visible']);

        $this->actingAs($operario)
            ->get(route('turnos.index'))
            ->assertForbidden();

        $this->createUserWithRole('Yerbatero oculto', 'yerbatero');
        $administracion = $this->createUserWithRole('Administración', 'administracion');

        $this->actingAs($administracion)
            ->get(route('turnos.index'))
            ->assertOk()
            ->assertSee('Turnos de producción')
            ->assertSee('Operario visible')
            ->assertDontSee('Yerbatero oculto');

        $this->get(route('tareas.asignar'))
            ->assertOk()
            ->assertSee('Nueva tarea')
            ->assertSee('Operario visible')
            ->assertDontSee('Yerbatero oculto');
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
