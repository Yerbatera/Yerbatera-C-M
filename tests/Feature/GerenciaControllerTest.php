<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GerenciaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_gerente_dashboard_links_to_both_read_only_histories(): void
    {
        $gerente = User::factory()->create(['rol' => 'gerente']);

        $this->actingAs($gerente)
            ->get('/gerente')
            ->assertOk()
            ->assertSee(route('gerencia.ventas'))
            ->assertSee(route('gerencia.procesos'));
    }

    public function test_gerente_can_view_sales_history_without_write_controls(): void
    {
        $gerente = User::factory()->create(['rol' => 'gerente']);
        $contactoId = DB::table('contactos')->insertGetId([
            'nombre' => 'Cliente gerencial',
            'telefono' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $loteId = DB::table('lotes')->insertGetId([
            'estado' => 'Disponible',
            'peso' => 150,
            'tipo' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('ventas')->insert([
            'fecha' => '2026-10-14 12:00:00',
            'id_cliente' => $contactoId,
            'id_lote' => $loteId,
            'cantidad' => 25,
            'precio_kg' => 1200,
            'total_venta' => 30000,
            'punto_venta' => 'Casa Central',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($gerente)
            ->get(route('gerencia.ventas'))
            ->assertOk()
            ->assertSee('Historial de ventas')
            ->assertSee('Cliente gerencial')
            ->assertSee('30.000,00')
            ->assertSee('Casa Central')
            ->assertDontSee('Registrar venta')
            ->assertDontSee('Confirmar Venta');
    }

    public function test_gerente_can_view_productive_control_records_without_write_controls(): void
    {
        $gerente = User::factory()->create(['rol' => 'gerente']);
        $operario = User::factory()->create(['rol' => 'operario']);
        $loteId = DB::table('lotes')->insertGetId([
            'estado' => 'Disponible',
            'peso' => 150,
            'tipo' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('procesos')->insert([
            'tipo_proceso' => 'Sapecado',
            'id_encargado' => $operario->id,
            'fecha' => '2026-10-14',
            'temperatura' => 180,
            'vuelta_torax' => 3,
            'id_lote' => $loteId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($gerente)
            ->get(route('gerencia.procesos'))
            ->assertOk()
            ->assertSee('Registros de control productivo')
            ->assertSee('Sapecado')
            ->assertSee($operario->name)
            ->assertSee('180')
            ->assertSee('3')
            ->assertDontSee('<form', false)
            ->assertDontSee('Guardar');
    }

    public function test_gerente_can_access_the_complete_sales_history_across_pages(): void
    {
        $gerente = User::factory()->create(['rol' => 'gerente']);
        $contactoId = DB::table('contactos')->insertGetId([
            'nombre' => 'Cliente paginado',
            'telefono' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $loteId = DB::table('lotes')->insertGetId([
            'estado' => 'Disponible',
            'peso' => 150,
            'tipo' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (range(1, 16) as $day) {
            DB::table('ventas')->insert([
                'fecha' => now()->subDays($day)->toDateTimeString(),
                'id_cliente' => $contactoId,
                'id_lote' => $loteId,
                'cantidad' => 1,
                'precio_kg' => 1,
                'total_venta' => $day,
                'punto_venta' => 'Casa Central',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->actingAs($gerente)
            ->get(route('gerencia.ventas', ['page' => 2]))
            ->assertOk()
            ->assertSee('Cliente paginado')
            ->assertSee('$1,00');
    }

    public static function rolesWithoutGerenciaAccess(): array
    {
        return [
            'administracion' => ['administracion'],
            'operario' => ['operario'],
            'yerbatero' => ['yerbatero'],
        ];
    }

    #[DataProvider('rolesWithoutGerenciaAccess')]
    public function test_non_gerente_roles_are_forbidden_from_both_histories(string $rol): void
    {
        $user = User::factory()->create(['rol' => $rol]);

        $this->actingAs($user)
            ->get(route('gerencia.ventas'))
            ->assertForbidden();

        $this->get(route('gerencia.procesos'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_from_both_histories(): void
    {
        $this->get(route('gerencia.ventas'))
            ->assertRedirect(route('login'));

        $this->get(route('gerencia.procesos'))
            ->assertRedirect(route('login'));
    }

    public function test_gerencia_history_routes_reject_write_methods(): void
    {
        $gerente = User::factory()->create(['rol' => 'gerente']);

        $this->actingAs($gerente)
            ->post(route('gerencia.ventas'))
            ->assertStatus(405);

        $this->put(route('gerencia.ventas'))
            ->assertStatus(405);

        $this->patch(route('gerencia.ventas'))
            ->assertStatus(405);

        $this->delete(route('gerencia.ventas'))
            ->assertStatus(405);

        $this->post(route('gerencia.procesos'))
            ->assertStatus(405);

        $this->put(route('gerencia.procesos'))
            ->assertStatus(405);

        $this->patch(route('gerencia.procesos'))
            ->assertStatus(405);

        $this->delete(route('gerencia.procesos'))
            ->assertStatus(405);

        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('procesos', 0);
    }
}
