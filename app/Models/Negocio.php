<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Negocio extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'negocio';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null;
    const DELETED_AT = 'fecha_eliminacion';

    protected $fillable = [
        'id_propietario',
        'nombre',
        'nit',
        'descripcion',
        'logotipo_url',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_creacion' => 'datetime',
        'fecha_eliminacion' => 'datetime',
    ];

    public function propietario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_propietario');
    }

    public function sucursales(): HasMany
    {
        return $this->hasMany(Sucursal::class, 'id_negocio');
    }

    public function categorias(): HasMany
    {
        return $this->hasMany(Categoria::class, 'id_negocio');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'id_negocio');
    }

    public function telefonosCentral(): HasMany
    {
        return $this->hasMany(ContactoTelefonico::class, 'id_negocio')
                    ->whereNull('id_sucursal');
    }
}
