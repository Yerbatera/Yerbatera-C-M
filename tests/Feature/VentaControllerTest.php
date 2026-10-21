<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VentaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_sales_routes(): void
    {
        $this->get('/ventas')
            ->assertRedirect(route('login'));

        $this->get('/ventas/create')
            ->assertRedirect(route('login'));

        $this->post('/ventas')
            ->assertRedirect(route('login'));
    }

    public function test_sales_page_renders_when_there_are_no_sales(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/ventas')
            ->assertOk()
            ->assertSee('Historial de ventas')
            ->assertSee('Todavía no hay ventas registradas.')
            ->assertDontSee('Registrar venta');
    }

    public function test_sales_page_shows_sales_with_their_customer_and_lot(): void
    {
        $contactoId = DB::table('contactos')->insertGetId([
            'nombre' => 'Cliente de prueba',
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

        $this->actingAs(User::factory()->create())
            ->get('/ventas')
            ->assertOk()
            ->assertSee('Cliente de prueba')
            ->assertSee('25,00')
            ->assertSee('1.200,00')
            ->assertSee('30.000,00');
    }
}
