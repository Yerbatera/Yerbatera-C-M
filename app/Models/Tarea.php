<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tarea extends Model
{
    protected $primaryKey = 'id_tarea';

    public function operario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_operario');
    }
}
