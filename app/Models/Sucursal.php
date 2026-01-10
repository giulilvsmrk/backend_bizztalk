<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Point;

class Sucursal extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasSpatial;

    protected $table = 'sucursal';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null;
    const DELETED_AT = 'fecha_eliminacion';

    protected $fillable = [
        'id_negocio',
        'nombre_sucursal',
        'ubicacion_gps',
        'direccion_texto',
        'qr_estatico_url',
        'imagen_portada_url',
        'activo',
    ];

    protected $casts = [
        'ubicacion_gps' => Point::class,
        'activo' => 'boolean',
        'fecha_creacion' => 'datetime',
        'fecha_eliminacion' => 'datetime',
    ];

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class, 'id_negocio');
    }

    public function zonasCobertura(): HasMany
    {
        return $this->hasMany(ZonaCobertura::class, 'id_sucursal');
    }

    public function configuracionesEntrega(): HasMany
    {
        return $this->hasMany(ConfigEntrega::class, 'id_sucursal');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'id_sucursal');
    }

    public function telefonos(): HasMany
    {
        return $this->hasMany(ContactoTelefonico::class, 'id_sucursal');
    }

    public function colaboradores(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'colaborador', 'id_sucursal', 'id_usuario')
                    ->withPivot(['rol', 'fecha_vinculo', 'activo'])
                    ->using(Colaborador::class);
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'id_sucursal');
    }

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'inventario', 'id_sucursal', 'id_producto')
                    ->withPivot(['cantidad', 'precio_local', 'activo', 'ultima_actualizacion'])
                    ->using(Inventario::class);
    }

    public function itemsInventario(): HasMany
    {
        return $this->hasMany(Inventario::class, 'id_sucursal');
    }

    public function galeria(): HasMany
    {
        return $this->hasMany(GaleriaSucursal::class, 'id_sucursal');
    }

    public function promociones(): HasMany
    {
        return $this->hasMany(Promocion::class, 'id_sucursal');
    }

    public function resenas(): HasMany
    {
        return $this->hasMany(ResenaSucursal::class, 'id_sucursal');
    }
}
