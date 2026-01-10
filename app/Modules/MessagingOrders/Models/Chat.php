<?php

declare(strict_types=1);

namespace App\Modules\MessagingOrders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Negocio;

/**
 * Chat Model
 * Representa un mensaje en la conversación de chat IA
 */
class Chat extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'chat';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'id_negocio',
        'mensaje_usuario',
        'respuesta_bot',
        'metadata',
        'activo',
    ];

    protected $casts = [
        'metadata' => 'array',
        'activo' => 'boolean',
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
        'fecha_eliminacion' => 'datetime',
    ];

    /**
     * Un chat pertenece a un negocio
     */
    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class, 'id_negocio', 'id');
    }

    /**
     * Scope: Filtrar chats activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true)->whereNull('fecha_eliminacion');
    }

    /**
     * Scope: Filtrar por negocio
     */
    public function scopeDelNegocio($query, string $idNegocio)
    {
        return $query->where('id_negocio', $idNegocio);
    }

    /**
     * Scope: Chats recientes
     */
    public function scopeRecientes($query)
    {
        return $query->where('fecha_creacion', '>=', now()->subDay());
    }
}
