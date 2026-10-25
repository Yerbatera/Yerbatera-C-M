<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Balanza extends Model
{
    protected $table = 'registro_balanzas';

    protected $fillable = [
        'fecha_hora',
        'id_proveedor',
        'id_operario',
        'id_materia',
        'nro_ticket',
        'remito',
        'peso_bruto',
        'peso_tara',
        'descuento',
        'peso_neto',
        'importe_total',
        'concepto_descuento',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
            'peso_bruto' => 'decimal:2',
            'peso_tara' => 'decimal:2',
            'descuento' => 'decimal:2',
            'peso_neto' => 'decimal:2',
            'importe_total' => 'decimal:2',
        ];
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Contacto::class, 'id_proveedor');
    }

    public function operario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_operario');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'id_materia');
    }
}
