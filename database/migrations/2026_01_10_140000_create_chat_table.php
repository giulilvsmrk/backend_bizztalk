<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ejecutar la migración.
     * 
     * Crea la tabla 'chat' para almacenar el historial de conversaciones
     * entre clientes y el asistente IA.
     */
    public function up(): void
    {
        Schema::create('chat', function (Blueprint $table) {
            // Identificador único
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));

            // Relación con el negocio/empresa
            $table->foreignUuid('id_negocio')
                  ->constrained('negocio', 'id')
                  ->cascadeOnDelete()
                  ->comment('ID del negocio al que pertenece este chat');

            // Mensaje del usuario/cliente
            $table->text('mensaje_usuario')
                  ->comment('Mensaje enviado por el cliente');

            // Respuesta del asistente IA
            $table->text('respuesta_bot')
                  ->comment('Respuesta generada por el asistente IA (Ollama)');

            // Datos adicionales
            $table->text('metadata')->nullable()
                  ->comment('Información adicional en JSON (contexto, tokens, etc)');

            // Contraseña blanda (soft delete)
            $table->boolean('activo')->default(true)
                  ->comment('Indica si el registro está activo');

            // Timestamps
            $table->timestampTz('fecha_creacion')
                  ->default(DB::raw('now()'))
                  ->comment('Fecha de creación del registro');

            $table->timestampTz('fecha_actualizacion')
                  ->nullable()
                  ->comment('Fecha de última actualización');

            $table->timestampTz('fecha_eliminacion')
                  ->nullable()
                  ->comment('Fecha de eliminación lógica (soft delete)');

            // Índices para optimizar búsquedas
            $table->index('id_negocio', 'idx_chat_negocio');
            $table->index('fecha_creacion', 'idx_chat_fecha');
            $table->index(['id_negocio', 'fecha_creacion'], 'idx_chat_negocio_fecha');
        });
    }

    /**
     * Revertir la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat');
    }
};
