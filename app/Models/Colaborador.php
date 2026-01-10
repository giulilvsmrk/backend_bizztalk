<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RolColaborador;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Colaborador extends Pivot
{
    protected $table = 'colaborador';
    public $incrementing = false;
    protected $primaryKey = ['id_sucursal', 'id_usuario'];
    public $timestamps = true;
    const CREATED_AT = 'fecha_vinculo';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_sucursal',
        'id_usuario',
        'rol',
        'activo',
        'fecha_vinculo',
    ];

    protected $casts = [
        'rol' => RolColaborador::class,
        'activo' => 'boolean',
        'fecha_vinculo' => 'datetime',
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function scopeActivos(Builder $query): void
    {
        $query->where('activo', true);
    }

    public function scopeRol(Builder $query, RolColaborador $rol): void
    {
        $query->where('rol', $rol);
    }
}
