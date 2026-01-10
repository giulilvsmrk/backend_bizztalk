<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ListaDeseos extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'lista_deseos';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = true;

    const CREATED_AT = 'fecha_agregado';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_usuario',
        'id_producto',
    ];

    protected $casts = [
        'fecha_agregado' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function scopeTieneProducto(Builder $query, string $usuarioId, string $productoId): void
    {
        $query->where('id_usuario', $usuarioId)
                ->where('id_producto', $productoId);
    }

    public function scopeDeUsuario(Builder $query, string $usuarioId): void
    {
        $query->where('id_usuario', $usuarioId)
                ->orderByDesc('fecha_agregado');
    }

    public function scopeProductosDisponibles(Builder $query): void
    {
        $query->whereHas('producto', function ($q) {
            $q->where('activo', true);
        });
    }

    public static function toggle(string $usuarioId, string $productoId): bool
    {
        $item = self::where('id_usuario', $usuarioId)
                    ->where('id_producto', $productoId)
                    ->first();

        if ($item) {
            $item->delete();
            return false;
        }

        self::create([
            'id_usuario' => $usuarioId,
            'id_producto' => $productoId
        ]);

        return true;
    }
}
