<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Agregar columna session_id para rastrear conversaciones persistentes
     */
    public function up(): void
    {
        Schema::table('chat', function (Blueprint $table) {
            $table->string('session_id')
                  ->nullable()
                  ->after('id_negocio')
                  ->comment('ID de sesión para agrupar mensajes de una conversación');

            $table->index('session_id', 'idx_chat_session_id');
            $table->index(['session_id', 'fecha_creacion'], 'idx_chat_session_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat', function (Blueprint $table) {
            $table->dropIndex('idx_chat_session_fecha');
            $table->dropIndex('idx_chat_session_id');
            $table->dropColumn('session_id');
        });
    }
};
