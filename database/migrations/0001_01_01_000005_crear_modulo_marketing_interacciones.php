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
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->text('nombre');
            $table->text('codigo_cupon')->nullable()->unique();
            $table->text('descripcion')->nullable();
            $table->decimal('valor_descuento', 12, 2)->default(0);
            $table->foreignUuid('id_sucursal')->nullable()->constrained('sucursal', 'id')->cascadeOnDelete();
            $table->foreignUuid('id_producto')->nullable()->constrained('producto', 'id')->cascadeOnDelete();
            $table->timestampTz('fecha_inicio');
            $table->timestampTz('fecha_fin');
            $table->decimal('monto_minimo_compra', 12, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->jsonb('reglas_extra')->default('{}');
            $table->timestampTz('fecha_creacion')->default(DB::raw('now()'));
            $table->timestampTz('fecha_eliminacion')->nullable();
        });
        DB::statement("ALTER TABLE promocion ADD COLUMN tipo_beneficio tipo_descuento NOT NULL");
        DB::statement("ALTER TABLE promocion ADD COLUMN alcance alcance_promo NOT NULL");
        DB::statement("ALTER TABLE promocion ADD CONSTRAINT chk_alcance_valido CHECK (
            (alcance = 'global' AND id_sucursal IS NULL AND id_producto IS NULL) OR
            (alcance = 'sucursal' AND id_sucursal IS NOT NULL AND id_producto IS NULL) OR
            (alcance = 'producto' AND id_producto IS NOT NULL)
        )");
        DB::statement("ALTER TABLE promocion ADD CONSTRAINT chk_fechas_promo CHECK (fecha_fin > fecha_inicio)");
        DB::statement("ALTER TABLE promocion ADD CONSTRAINT chk_valor_descuento CHECK (valor_descuento >= 0)");
        DB::statement("ALTER TABLE promocion ADD CONSTRAINT chk_monto_minimo CHECK (monto_minimo_compra >= 0)");

        Schema::create('uso_promocion', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_promocion')->constrained('promocion', 'id');
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id');
            $table->foreignUuid('id_pedido')->nullable()->constrained('pedido', 'id');
            $table->decimal('monto_ahorrado', 12, 2);
            $table->timestampTz('fecha_uso')->default(DB::raw('now()'));
        });

        Schema::create('lista_deseos', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id')->cascadeOnDelete();
            $table->foreignUuid('id_producto')->constrained('producto', 'id')->cascadeOnDelete();
            $table->timestampTz('fecha_agregado')->default(DB::raw('now()'));
            $table->unique(['id_usuario', 'id_producto']);
        });

        Schema::create('item_guardado', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id')->cascadeOnDelete();
            $table->foreignUuid('id_producto')->constrained('producto', 'id')->cascadeOnDelete();
            $table->foreignUuid('id_sucursal_origen')->constrained('sucursal', 'id');
            $table->integer('cantidad');
            $table->text('observacion')->nullable();
            $table->timestampTz('fecha_guardado')->default(DB::raw('now()'));
        });
        DB::statement("ALTER TABLE item_guardado ADD CONSTRAINT chk_cantidad_guardada CHECK (cantidad > 0)");

        Schema::create('resena_producto', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id');
            $table->foreignUuid('id_producto')->constrained('producto', 'id');
            $table->foreignId('id_detalle_pedido')->constrained('detalle_pedido', 'id');
            $table->integer('puntuacion');
            $table->text('comentario')->nullable();
            $table->timestampTz('fecha_registro')->default(DB::raw('now()'));
            $table->unique(['id_usuario', 'id_detalle_pedido']);
        });
        DB::statement("ALTER TABLE resena_producto ADD CONSTRAINT chk_puntuacion_prod CHECK (puntuacion BETWEEN 1 AND 5)");

        Schema::create('resena_sucursal', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id');
            $table->foreignUuid('id_sucursal')->constrained('sucursal', 'id');
            $table->foreignUuid('id_pedido')->constrained('pedido', 'id');
            $table->integer('puntuacion');
        });
        DB::statement('ALTER TABLE resena_sucursal ADD COLUMN aspectos_positivos TEXT[]');
        DB::statement('ALTER TABLE resena_sucursal ADD COLUMN comentario TEXT');
        DB::statement("ALTER TABLE resena_sucursal ADD COLUMN fecha_registro TIMESTAMPTZ DEFAULT now()");
        DB::statement("ALTER TABLE resena_sucursal ADD CONSTRAINT resena_sucursal_unique UNIQUE (id_usuario, id_pedido)");
        DB::statement("ALTER TABLE resena_sucursal ADD CONSTRAINT chk_puntuacion_suc CHECK (puntuacion BETWEEN 1 AND 5)");

        Schema::create('coleccion', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->text('titulo');
            $table->text('descripcion')->nullable();
            $table->text('imagen_cover_url')->nullable();
            $table->foreignUuid('id_usuario_destino')->nullable()->constrained('usuario', 'id');
            $table->boolean('es_generada_por_ia')->default(false);
            $table->timestampTz('fecha_expiracion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestampTz('fecha_creacion')->default(DB::raw('now()'));
        });

        Schema::create('item_coleccion', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('id_coleccion')->constrained('coleccion', 'id')->cascadeOnDelete();
            $table->foreignUuid('id_producto')->constrained('producto', 'id')->cascadeOnDelete();
            $table->integer('orden_visual')->default(0);
            $table->decimal('score_relevancia', 5, 4)->nullable();
        });
        DB::statement("
            CREATE TABLE log_interaccion_ia (
                id UUID DEFAULT gen_random_uuid(),
                id_usuario UUID REFERENCES usuario(id),
                id_pedido UUID REFERENCES pedido(id),
                fecha_hora TIMESTAMPTZ DEFAULT now() NOT NULL,
                transcripcion_user TEXT,
                respuesta_ia TEXT,
                intencion TEXT,
                metadata_tecnica JSONB,
                PRIMARY KEY (fecha_hora, id)
            ) PARTITION BY RANGE (fecha_hora)
        ");
        DB::statement("CREATE TABLE log_ia_2025 PARTITION OF log_interaccion_ia FOR VALUES FROM ('2025-01-01') TO ('2026-01-01')");
        DB::statement("
            CREATE TABLE historial_vista (
                id BIGINT GENERATED ALWAYS AS IDENTITY,
                id_usuario UUID NOT NULL REFERENCES usuario(id) ON DELETE CASCADE,
                id_producto UUID NOT NULL REFERENCES producto(id) ON DELETE CASCADE,
                ubicacion_viewer POINT,
                fecha_vista TIMESTAMPTZ DEFAULT now() NOT NULL,
                PRIMARY KEY (fecha_vista, id)
            ) PARTITION BY RANGE (fecha_vista)
        ");
        DB::statement("CREATE TABLE historial_vista_2025 PARTITION OF historial_vista FOR VALUES FROM ('2025-01-01') TO ('2026-01-01')");
        DB::statement("CREATE INDEX idx_item_guardado_usuario ON item_guardado(id_usuario)");
        DB::statement("CREATE INDEX idx_lista_deseos_usuario ON lista_deseos(id_usuario)");
        DB::statement("CREATE INDEX idx_historial_usuario_fecha ON historial_vista(id_usuario, fecha_vista DESC)");
        DB::statement("CREATE INDEX idx_coleccion_usuario ON coleccion(id_usuario_destino) WHERE activo = true");
        DB::statement("CREATE INDEX idx_resena_producto_usuario ON resena_producto(id_usuario)");
        DB::statement("CREATE INDEX idx_promocion_activa ON promocion(activo, fecha_inicio, fecha_fin)");
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS item_coleccion');
        DB::statement('DROP TABLE IF EXISTS coleccion');
        DB::statement('DROP TABLE IF EXISTS historial_vista CASCADE');
        DB::statement('DROP TABLE IF EXISTS log_interaccion_ia CASCADE');
        Schema::dropIfExists('resena_sucursal');
        Schema::dropIfExists('resena_producto');
        Schema::dropIfExists('item_guardado');
        Schema::dropIfExists('lista_deseos');
        Schema::dropIfExists('uso_promocion');
        Schema::dropIfExists('promocion');
    }
};
