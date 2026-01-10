<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('negocio', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_propietario')->constrained('usuario', 'id');
            $table->text('nombre');
            $table->text('nit')->nullable()->unique();
            $table->text('descripcion')->nullable();
            $table->text('logotipo_url')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestampTz('fecha_creacion')->default(DB::raw('now()'));
            $table->timestampTz('fecha_eliminacion')->nullable();
        });

        Schema::create('sucursal', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_negocio')->constrained('negocio', 'id')->cascadeOnDelete();
            $table->text('nombre_sucursal');
            $table->text('direccion_texto');
            $table->text('qr_estatico_url')->nullable();
            $table->text('imagen_portada_url')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestampTz('fecha_creacion')->default(DB::raw('now()'));
            $table->timestampTz('fecha_eliminacion')->nullable();
        });
        DB::statement('ALTER TABLE sucursal ADD COLUMN ubicacion_gps point NOT NULL');
        DB::statement('CREATE INDEX idx_sucursal_geo ON sucursal USING GIST (ubicacion_gps)');

        Schema::create('zona_cobertura', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_sucursal')->constrained('sucursal', 'id')->cascadeOnDelete();
            $table->text('nombre');
            $table->decimal('costo_envio', 12, 2)->default(0);
            $table->integer('tiempo_min_extra')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestampTz('fecha_creacion')->default(DB::raw('now()'));
            $table->timestampTz('fecha_eliminacion')->nullable();
        });
        DB::statement('ALTER TABLE zona_cobertura ADD COLUMN area polygon NOT NULL');
        DB::statement("ALTER TABLE zona_cobertura ADD CONSTRAINT chk_costo_envio CHECK (costo_envio >= 0)");
        DB::statement('CREATE INDEX idx_zona_cobertura_area ON zona_cobertura USING GIST (area)');

        Schema::create('contacto_telefonico', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->text('numero');
            $table->text('etiqueta')->nullable();
            $table->boolean('es_principal')->default(false);
            $table->foreignUuid('id_negocio')->nullable()->constrained('negocio', 'id')->cascadeOnDelete();
            $table->foreignUuid('id_sucursal')->nullable()->constrained('sucursal', 'id')->cascadeOnDelete();
            $table->timestampTz('fecha_creacion')->default(DB::raw('now()'));
        });
        DB::statement("ALTER TABLE contacto_telefonico ADD COLUMN tipo tipo_contacto NOT NULL DEFAULT 'movil'");
        DB::statement("ALTER TABLE contacto_telefonico ADD CONSTRAINT chk_pertenencia CHECK ((id_negocio IS NOT NULL AND id_sucursal IS NULL) OR (id_negocio IS NULL AND id_sucursal IS NOT NULL))");

        Schema::create('horario', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_sucursal')->constrained('sucursal', 'id')->cascadeOnDelete();
            $table->integer('dia_semana');
            $table->time('hora_apertura');
            $table->time('hora_cierre');
            $table->boolean('es_feriado')->default(false);
            $table->unique(['id_sucursal', 'dia_semana']);
        });
        DB::statement("ALTER TABLE horario ADD CONSTRAINT chk_horas CHECK (hora_cierre > hora_apertura)");
        DB::statement("ALTER TABLE horario ADD CONSTRAINT chk_dia_semana CHECK (dia_semana BETWEEN 0 AND 6)");

        Schema::create('config_entrega', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_sucursal')->constrained('sucursal', 'id')->cascadeOnDelete();
            $table->text('alias_personalizado')->nullable();
            $table->decimal('costo_base', 12, 2)->default(0);
            $table->integer('tiempo_min_aprox')->nullable();
            $table->jsonb('vehiculos_permitidos')->default('[]');
            $table->boolean('activo')->default(true);
        });
        DB::statement("ALTER TABLE config_entrega ADD COLUMN tipo tipo_entrega NOT NULL");
        DB::statement("ALTER TABLE config_entrega ADD CONSTRAINT chk_costo_base CHECK (costo_base >= 0)");
        DB::statement("ALTER TABLE config_entrega ADD CONSTRAINT config_entrega_unique UNIQUE (id_sucursal, tipo, alias_personalizado)");

        Schema::create('colaborador', function (Blueprint $table) {
            $table->foreignUuid('id_sucursal')->constrained('sucursal', 'id');
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id');
            $table->timestampTz('fecha_vinculo')->default(DB::raw('now()'));
            $table->boolean('activo')->default(true);
            $table->primary(['id_sucursal', 'id_usuario']);
        });
        DB::statement("ALTER TABLE colaborador ADD COLUMN rol rol_sucursal NOT NULL DEFAULT 'empleado'");

        Schema::create('categoria', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('id_negocio')->constrained('negocio', 'id');
            $table->text('nombre');
            $table->unique(['id_negocio', 'nombre']);
        });

        Schema::create('producto', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_negocio')->constrained('negocio', 'id');
            $table->foreignId('id_categoria')->nullable()->constrained('categoria', 'id');
            $table->text('nombre');
            $table->text('descripcion')->nullable();
            $table->text('imagen_url')->nullable();
            $table->decimal('precio_base', 12, 2);
            $table->boolean('activo')->default(true);
            $table->timestampTz('fecha_creacion')->default(DB::raw('now()'));
            $table->timestampTz('fecha_eliminacion')->nullable();
        });
        DB::statement("ALTER TABLE producto ADD CONSTRAINT chk_precio_base CHECK (precio_base >= 0)");
        DB::statement("ALTER TABLE producto ADD COLUMN vector_busqueda tsvector");
        DB::statement("
            CREATE OR REPLACE FUNCTION actualizar_vector_busqueda_producto() RETURNS TRIGGER AS $$
            BEGIN
                NEW.vector_busqueda :=
                    setweight(to_tsvector('spanish', unaccent(coalesce(NEW.nombre, ''))), 'A') ||
                    setweight(to_tsvector('spanish', unaccent(coalesce(NEW.descripcion, ''))), 'B');
                RETURN NEW;
            END
            $$ LANGUAGE plpgsql;
        ");
        DB::statement("
            CREATE TRIGGER trg_vector_busqueda_producto
            BEFORE INSERT OR UPDATE ON producto
            FOR EACH ROW
            EXECUTE FUNCTION actualizar_vector_busqueda_producto();
        ");
        DB::statement("CREATE INDEX idx_producto_vector ON producto USING GIN (vector_busqueda)");

        Schema::create('inventario', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_sucursal')->constrained('sucursal', 'id')->cascadeOnDelete();
            $table->foreignUuid('id_producto')->constrained('producto', 'id')->cascadeOnDelete();
            $table->integer('cantidad')->default(0);
            $table->decimal('precio_local', 12, 2)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestampTz('ultima_actualizacion')->default(DB::raw('now()'));
            $table->unique(['id_sucursal', 'id_producto']);
        });
        DB::statement("ALTER TABLE inventario ADD CONSTRAINT chk_cantidad CHECK (cantidad >= 0)");
        DB::statement("ALTER TABLE inventario ADD CONSTRAINT chk_precio_local CHECK (precio_local >= 0)");

        Schema::create('galeria_sucursal', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_sucursal')->constrained('sucursal', 'id')->cascadeOnDelete();
            $table->text('url');
            $table->text('descripcion')->nullable();
            $table->integer('orden_visual')->default(0);
            $table->timestampTz('fecha_subida')->default(DB::raw('now()'));
        });

        Schema::create('galeria_producto', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_producto')->constrained('producto', 'id')->cascadeOnDelete();
            $table->text('url');
            $table->integer('orden_visual')->default(0);
            $table->timestampTz('fecha_subida')->default(DB::raw('now()'));
        });
        DB::statement("CREATE INDEX idx_colaborador_usuario ON colaborador(id_usuario)");
        DB::statement("CREATE INDEX idx_inventario_sucursal ON inventario(id_sucursal)");
        DB::statement("CREATE INDEX idx_horario_sucursal ON horario(id_sucursal)");
        DB::statement("CREATE INDEX idx_config_entrega_sucursal ON config_entrega(id_sucursal) WHERE activo = true");
        DB::statement("CREATE INDEX idx_zona_cobertura_sucursal ON zona_cobertura(id_sucursal)");
        DB::statement("CREATE INDEX idx_galeria_sucursal ON galeria_sucursal(id_sucursal)");
        DB::statement("CREATE INDEX idx_galeria_producto ON galeria_producto(id_producto)");
        DB::statement("CREATE INDEX idx_contacto_entidad ON contacto_telefonico(id_sucursal, id_negocio)");
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS trg_vector_busqueda_producto ON producto');
        DB::statement('DROP FUNCTION IF EXISTS actualizar_vector_busqueda_producto');

        Schema::dropIfExists('galeria_producto');
        Schema::dropIfExists('galeria_sucursal');
        Schema::dropIfExists('inventario');
        Schema::dropIfExists('producto');
        Schema::dropIfExists('categoria');
        Schema::dropIfExists('colaborador');
        Schema::dropIfExists('config_entrega');
        Schema::dropIfExists('horario');
        Schema::dropIfExists('contacto_telefonico');
        Schema::dropIfExists('zona_cobertura');
        Schema::dropIfExists('sucursal');
        Schema::dropIfExists('negocio');
    }
};
