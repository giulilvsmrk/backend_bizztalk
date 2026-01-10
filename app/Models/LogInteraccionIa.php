<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LogInteraccionIa extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'log_interaccion_ia';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = true;

    const CREATED_AT = 'fecha_hora';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_usuario',
        'id_pedido',
        'transcripcion_user',
        'respuesta_ia',
        'intencion',
        'metadata_tecnica',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'metadata_tecnica' => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function registrarMetricas(int $tokensIn, int $tokensOut, int $latencia): void
    {
        $meta = $this->metadata_tecnica ?? [];
        $meta['metricas'] = [
            'tokens_input' => $tokensIn,
            'tokens_output' => $tokensOut,
            'latencia_ms' => $latencia
        ];
        $this->metadata_tecnica = $meta;
        $this->save();
    }
}
