<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MetodoPago;
use App\Enums\ProveedorPago;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class BilleteraUsuario extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'billetera_usuario';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = null;
    const DELETED_AT = 'fecha_eliminacion';

    protected $fillable = [
        'id_usuario',
        'tipo',
        'proveedor',
        'token_externo',
        'marca_tarjeta',
        'ultimos_4_digitos',
        'fecha_expiracion',
        'es_predeterminado',
        'activo',
    ];

    protected $hidden = [
        'token_externo',
    ];

    protected $casts = [
        'tipo' => MetodoPago::class,
        'proveedor' => ProveedorPago::class,
        'es_predeterminado' => 'boolean',
        'activo' => 'boolean',
        'fecha_registro' => 'datetime',
        'fecha_eliminacion' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function transacciones(): HasMany
    {
        return $this->hasMany(TransaccionPago::class, 'id_metodo_guardado');
    }

    public function scopeActivas(Builder $query): void
    {
        $query->where('activo', true);
    }

    public function scopePredeterminada(Builder $query): void
    {
        $query->where('es_predeterminado', true);
    }

    public function scopePorTipo(Builder $query, MetodoPago $tipo): void
    {
        $query->where('tipo', $tipo);
    }
}
