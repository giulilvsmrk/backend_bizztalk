<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioRol extends Pivot
{

    protected $table = 'usuario_rol';
    public $incrementing = false;
    public $timestamps = true;

    const CREATED_AT = 'fecha_asignacion';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_usuario',
        'id_rol',
        'fecha_asignacion',
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }
}
