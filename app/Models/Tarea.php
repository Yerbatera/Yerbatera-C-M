<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tarea extends Model
{
    protected $primaryKey = 'id_tarea';

    protected $fillable = [
        'nombre',
        'id_operario',
        'id_asignador',
        'fecha_asignacion',
        'estado',
        'relevancia',
        'es_recurrente',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_asignacion' => 'datetime',
            'es_recurrente' => 'boolean',
        ];
    }

    public function operario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_operario');
    }
}
