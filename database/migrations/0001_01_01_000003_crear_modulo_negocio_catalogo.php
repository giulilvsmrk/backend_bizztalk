<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('negocio', function (Blueprint $table) {
            $table->uuid('id_negocio')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_propietario')->constrained('usuario', 'id_usuario');
            $table->text('nombre');
            $table->text('descripcion')->nullable();
            $table->text('logotipo_url')->nullable();
            $table->timestampTz('fecha_registro')->default(DB::raw('now()'));
            $table->boolean('activo')->default(true);
        });

        Schema::create('sucursal', function (Blueprint $table) {
            $table->uuid('id_sucursal')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_negocio')->constrained('negocio', 'id_negocio')->cascadeOnDelete();
            $table->text('nombre_sucursal');
            $table->text('telefonos')->nullable();
            $table->text('direccion_texto');
            $table->text('imagen_qr_estatico_url')->nullable();
            $table->text('imagen_portada_url')->nullable();
            $table->boolean('activo')->default(true);
        });
        DB::statement('ALTER TABLE sucursal ADD COLUMN ubicacion_gps point NOT NULL');
        DB::statement('ALTER TABLE sucursal ADD COLUMN zona_reparto polygon');
        DB::statement('CREATE INDEX idx_sucursal_geo ON sucursal USING GIST (ubicacion_gps)');

        Schema::create('config_entrega', function (Blueprint $table) {
            $table->uuid('id_config')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_sucursal')->constrained('sucursal', 'id_sucursal')->cascadeOnDelete();
            $table->decimal('costo_base', 12, 2)->default(0);
            $table->integer('tiempo_min_aprox')->nullable();
            $table->boolean('activo')->default(true);
        });
        DB::statement("ALTER TABLE config_entrega ADD COLUMN tipo tipo_entrega NOT NULL");
        DB::statement("ALTER TABLE config_entrega ADD CONSTRAINT config_entrega_unique UNIQUE (id_sucursal, tipo)");
        DB::statement("CREATE INDEX idx_config_entrega_sucursal ON config_entrega(id_sucursal) WHERE activo = true");

        Schema::create('horario', function (Blueprint $table) {
            $table->uuid('id_horario')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_sucursal')->constrained('sucursal', 'id_sucursal')->cascadeOnDelete();
            $table->integer('dia_semana');
            $table->time('hora_apertura');
            $table->time('hora_cierre');
            $table->boolean('es_feriado')->default(false);
            $table->unique(['id_sucursal', 'dia_semana']);
        });
        DB::statement("CREATE INDEX idx_horario_sucursal ON horario(id_sucursal)");

        Schema::create('colaborador', function (Blueprint $table) {
            $table->foreignUuid('id_sucursal')->constrained('sucursal', 'id_sucursal');
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id_usuario');
            $table->timestampTz('fecha_vinculo')->default(DB::raw('now()'));
            $table->boolean('activo')->default(true);
            $table->primary(['id_sucursal', 'id_usuario']);
        });
        DB::statement("ALTER TABLE colaborador ADD COLUMN rol rol_sucursal NOT NULL DEFAULT 'empleado'");
        DB::statement("CREATE INDEX idx_colaborador_usuario ON colaborador(id_usuario)");

        Schema::create('categoria', function (Blueprint $table) {
            $table->id('id_categoria');
            $table->foreignUuid('id_negocio')->constrained('negocio', 'id_negocio');
            $table->text('nombre');
            $table->unique(['id_negocio', 'nombre']);
        });

        Schema::create('producto', function (Blueprint $table) {
            $table->uuid('id_producto')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_negocio')->constrained('negocio', 'id_negocio');
            $table->foreignId('id_categoria')->nullable()->constrained('categoria', 'id_categoria');
            $table->text('nombre');
            $table->text('descripcion')->nullable();
            $table->text('codigo_sku')->nullable();
            $table->text('imagen_url')->nullable();
            $table->decimal('precio_base', 12, 2);
            $table->boolean('activo')->default(true);
        });
        DB::statement("
            ALTER TABLE producto ADD COLUMN vector_busqueda tsvector
            GENERATED ALWAYS AS (
                setweight(to_tsvector('spanish', nombre), 'A') ||
                setweight(to_tsvector('spanish', coalesce(descripcion, '')), 'B')
            ) STORED
        ");
        DB::statement("CREATE INDEX idx_producto_vector ON producto USING GIN (vector_busqueda)");

        Schema::create('inventario', function (Blueprint $table) {
            $table->uuid('id_inventario')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_sucursal')->constrained('sucursal', 'id_sucursal')->cascadeOnDelete();
            $table->foreignUuid('id_producto')->constrained('producto', 'id_producto')->cascadeOnDelete();
            $table->integer('cantidad')->default(0);
            $table->decimal('precio_local', 12, 2)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestampTz('ultima_actualizacion')->default(DB::raw('now()'));
            $table->unique(['id_sucursal', 'id_producto']);
        });
        DB::statement("CREATE INDEX idx_inventario_sucursal ON inventario(id_sucursal)");

        Schema::create('galeria_sucursal', function (Blueprint $table) {
            $table->uuid('id_foto')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_sucursal')->constrained('sucursal', 'id_sucursal')->cascadeOnDelete();
            $table->text('url');
            $table->text('descripcion')->nullable();
            $table->integer('orden_visual')->default(0);
            $table->timestampTz('fecha_subida')->default(DB::raw('now()'));
        });

        DB::statement("CREATE INDEX idx_galeria_sucursal ON galeria_sucursal(id_sucursal)");

        Schema::create('galeria_producto', function (Blueprint $table) {
            $table->uuid('id_foto')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_producto')->constrained('producto', 'id_producto')->cascadeOnDelete();
            $table->text('url');
            $table->integer('orden_visual')->default(0);
            $table->timestampTz('fecha_subida')->default(DB::raw('now()'));
        });

        DB::statement("CREATE INDEX idx_galeria_producto ON galeria_producto(id_producto)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galeria_producto');
        Schema::dropIfExists('galeria_sucursal');
        Schema::dropIfExists('inventario');
        Schema::dropIfExists('producto');
        Schema::dropIfExists('categoria');
        Schema::dropIfExists('colaborador');
        Schema::dropIfExists('horario');
        Schema::dropIfExists('config_entrega');
        Schema::dropIfExists('sucursal');
        Schema::dropIfExists('negocio');
    }
};
