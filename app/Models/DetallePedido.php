<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DetallePedido extends Model
{
    use HasFactory;

    protected $table = 'detalle_pedido';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'id_pedido',
        'id_producto',
        'cantidad',
        'precio_unitario',
        'precio_original',
        'descuento_aplicado',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'precio_original' => 'decimal:2',
        'descuento_aplicado' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function resena(): HasOne
    {
        return $this->hasOne(ResenaProducto::class, 'id_detalle_pedido');
    }

    public function tieneDescuento(): bool
    {
        return $this->descuento_aplicado > 0 ||
                ($this->precio_original && $this->precio_unitario < $this->precio_original);
    }

    public function getPorcentajeAhorroAttribute(): float
    {
        if (!$this->precio_original || $this->precio_original <= 0) {
            return 0.0;
        }

        $ahorro = $this->precio_original - $this->precio_unitario;
        return round(($ahorro / $this->precio_original) * 100, 2);
    }
}
