<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promocion', function (Blueprint $table) {
            $table->uuid('id_promocion')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->text('nombre');
            $table->text('codigo_cupon')->nullable()->unique();
            $table->text('descripcion')->nullable();
            $table->decimal('valor_descuento', 12, 2)->default(0);
            $table->foreignUuid('id_sucursal')->nullable()->constrained('sucursal', 'id_sucursal')->cascadeOnDelete();
            $table->foreignUuid('id_producto')->nullable()->constrained('producto', 'id_producto')->cascadeOnDelete();
            $table->timestampTz('fecha_inicio');
            $table->timestampTz('fecha_fin');
            $table->decimal('monto_minimo_compra', 12, 2)->default(0);
            $table->boolean('activo')->default(true);
        });
        DB::statement("ALTER TABLE promocion ADD COLUMN tipo_beneficio tipo_descuento NOT NULL");
        DB::statement("ALTER TABLE promocion ADD COLUMN alcance alcance_promo NOT NULL");
        DB::statement("
            ALTER TABLE promocion ADD CONSTRAINT chk_alcance_valido CHECK (
                (alcance = 'global' AND id_sucursal IS NULL AND id_producto IS NULL) OR
                (alcance = 'sucursal' AND id_sucursal IS NOT NULL AND id_producto IS NULL) OR
                (alcance = 'producto' AND id_producto IS NOT NULL)
            )
        ");
        DB::statement("CREATE INDEX idx_promocion_activa ON promocion(activo, fecha_inicio, fecha_fin)");

        Schema::create('uso_promocion', function (Blueprint $table) {
            $table->uuid('id_uso')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_promocion')->constrained('promocion', 'id_promocion');
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id_usuario');
            $table->foreignUuid('id_orden_compra')->nullable()->constrained('orden_compra', 'id_orden_compra');
            $table->foreignUuid('id_pedido')->nullable()->constrained('pedido', 'id_pedido');
            $table->decimal('monto_ahorrado', 12, 2);
            $table->timestampTz('fecha_uso')->default(DB::raw('now()'));
        });

        Schema::create('lista_deseos', function (Blueprint $table) {
            $table->uuid('id_deseo')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id_usuario')->cascadeOnDelete();
            $table->foreignUuid('id_producto')->constrained('producto', 'id_producto')->cascadeOnDelete();
            $table->timestampTz('fecha_agregado')->default(DB::raw('now()'));
            $table->unique(['id_usuario', 'id_producto']);
        });
        DB::statement("CREATE INDEX idx_lista_deseos_usuario ON lista_deseos(id_usuario)");

        Schema::create('item_guardado', function (Blueprint $table) {
            $table->id('id_guardado');
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id_usuario')->cascadeOnDelete();
            $table->foreignUuid('id_producto')->constrained('producto', 'id_producto')->cascadeOnDelete();
            $table->foreignUuid('id_sucursal_origen')->constrained('sucursal', 'id_sucursal');
            $table->integer('cantidad');
            $table->text('observacion')->nullable();
            $table->timestampTz('fecha_guardado')->default(DB::raw('now()'));
        });
        DB::statement("CREATE INDEX idx_item_guardado_usuario ON item_guardado(id_usuario)");

        Schema::create('resena_producto', function (Blueprint $table) {
            $table->uuid('id_resena_prod')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id_usuario');
            $table->foreignUuid('id_producto')->constrained('producto', 'id_producto');
            $table->foreignId('id_detalle_pedido')->constrained('detalle_pedido', 'id_detalle');
            $table->integer('puntuacion');
            $table->text('comentario')->nullable();
            $table->timestampTz('fecha_registro')->default(DB::raw('now()'));
            $table->unique(['id_usuario', 'id_detalle_pedido']);
        });

        Schema::create('resena_sucursal', function (Blueprint $table) {
            $table->uuid('id_resena_suc')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id_usuario');
            $table->foreignUuid('id_sucursal')->constrained('sucursal', 'id_sucursal');
            $table->foreignUuid('id_pedido')->constrained('pedido', 'id_pedido');
            $table->integer('puntuacion');
            $table->text('aspectos_positivos')->nullable();
            $table->text('comentario')->nullable();
            $table->timestampTz('fecha_registro')->default(DB::raw('now()'));
            $table->unique(['id_usuario', 'id_pedido']);
        });

        Schema::create('log_interaccion_ia', function (Blueprint $table) {
            $table->uuid('id_log')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_usuario')->nullable()->constrained('usuario', 'id_usuario');
            $table->foreignUuid('id_pedido')->nullable()->constrained('pedido', 'id_pedido');
            $table->timestampTz('fecha_hora')->default(DB::raw('now()'));
            $table->text('transcripcion_user')->nullable();
            $table->text('respuesta_ia')->nullable();
            $table->text('intencion')->nullable();
            $table->jsonb('metadata_tecnica')->nullable();
        });

        Schema::create('historial_vista', function (Blueprint $table) {
            $table->id('id_vista');
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id_usuario')->cascadeOnDelete();
            $table->foreignUuid('id_producto')->constrained('producto', 'id_producto')->cascadeOnDelete();
            $table->timestampTz('fecha_vista')->default(DB::raw('now()'));
        });
        DB::statement('ALTER TABLE historial_vista ADD COLUMN ubicacion_viewer point');
        DB::statement("CREATE INDEX idx_historial_usuario_fecha ON historial_vista(id_usuario, fecha_vista DESC)");

        Schema::create('coleccion', function (Blueprint $table) {
            $table->uuid('id_coleccion')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->text('titulo');
            $table->text('descripcion')->nullable();
            $table->text('imagen_cover_url')->nullable();
            $table->foreignUuid('id_usuario_destino')->nullable()->constrained('usuario', 'id_usuario');
            $table->boolean('es_generada_por_ia')->default(false);
            $table->timestampTz('fecha_expiracion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestampTz('fecha_creacion')->default(DB::raw('now()'));
        });
        DB::statement("CREATE INDEX idx_coleccion_usuario ON coleccion(id_usuario_destino) WHERE activo = true");

        Schema::create('item_coleccion', function (Blueprint $table) {
            $table->id('id_item_col');
            $table->foreignUuid('id_coleccion')->constrained('coleccion', 'id_coleccion')->cascadeOnDelete();
            $table->foreignUuid('id_producto')->constrained('producto', 'id_producto')->cascadeOnDelete();
            $table->integer('orden_visual')->default(0);
            $table->decimal('score_relevancia', 5, 4)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_coleccion');
        Schema::dropIfExists('coleccion');
        Schema::dropIfExists('historial_vista');
        Schema::dropIfExists('log_interaccion_ia');
        Schema::dropIfExists('resena_sucursal');
        Schema::dropIfExists('resena_producto');
        Schema::dropIfExists('item_guardado');
        Schema::dropIfExists('lista_deseos');
        Schema::dropIfExists('uso_promocion');
        Schema::dropIfExists('promocion');
    }
};
