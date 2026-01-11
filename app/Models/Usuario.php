<?php

declare(strict_types=1);

namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Point;

class Usuario extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids, SoftDeletes, HasSpatial , notifiable;

    protected $table = 'usuario';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';
    const DELETED_AT = 'fecha_eliminacion';

    protected $fillable = [
        'nombres',
        'apellidos',
        'correo',
        'password_hash',
        'telefono',
        'ubicacion_actual',
        'activo',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password_hash' => 'hashed',
        'activo' => 'boolean',
        'ubicacion_actual' => Point::class,
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
        'fecha_eliminacion' => 'datetime',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'usuario_rol', 'id_usuario', 'id_rol')
                    ->withPivot('fecha_asignacion')
                    ->using(UsuarioRol::class);
    }

    public function direcciones(): HasMany
    {
        return $this->hasMany(DireccionUsuario::class, 'id_usuario');
    }

    public function billeteras(): HasMany
    {
        return $this->hasMany(BilleteraUsuario::class, 'id_usuario');
    }

    public function negociosPropios(): HasMany
    {
        return $this->hasMany(Negocio::class, 'id_propietario');
    }

    public function sucursalesTrabajo(): BelongsToMany
    {
        return $this->belongsToMany(Sucursal::class, 'colaborador', 'id_usuario', 'id_sucursal')
                    ->withPivot(['rol', 'fecha_vinculo', 'activo'])
                    ->using(Colaborador::class);
    }

    public function fichasColaborador(): HasMany
    {
        return $this->hasMany(Colaborador::class, 'id_usuario');
    }

    public function carrito(): HasOne
    {
        return $this->hasOne(Carrito::class, 'id_usuario');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'id_usuario');
    }

    public function listaDeseos(): HasMany
    {
        return $this->hasMany(ListaDeseos::class, 'id_usuario');
    }

    public function itemsGuardados(): HasMany
    {
        return $this->hasMany(ItemGuardado::class, 'id_usuario');
    }

    public function colecciones(): HasMany
    {
        return $this->hasMany(Coleccion::class, 'id_usuario_destino');
    }

    public function resenasProductos(): HasMany
    {
        return $this->hasMany(ResenaProducto::class, 'id_usuario');
    }

    public function resenasSucursales(): HasMany
    {
        return $this->hasMany(ResenaSucursal::class, 'id_usuario');
    }

    public function logsIA(): HasMany
    {
        return $this->hasMany(LogInteraccionIa::class, 'id_usuario');
    }

    public function historialVistas(): HasMany
    {
        return $this->hasMany(HistorialVista::class, 'id_usuario');
    }
}
