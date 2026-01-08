<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol', function (Blueprint $table) {
            $table->uuid('id_rol')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->text('nombre')->unique();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
        });

        DB::table('rol')->insert([
            ['id_rol' => DB::raw('gen_random_uuid()'), 'nombre' => 'usuario',  'descripcion' => 'Rol base por defecto', 'activo' => true],
            ['id_rol' => DB::raw('gen_random_uuid()'), 'nombre' => 'dueño',    'descripcion' => 'Propietario de un negocio', 'activo' => true],
            ['id_rol' => DB::raw('gen_random_uuid()'), 'nombre' => 'empleado', 'descripcion' => 'Trabajador de sucursal', 'activo' => true],
        ]);

        Schema::create('usuario', function (Blueprint $table) {
            $table->uuid('id_usuario')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->text('nombres');
            $table->text('apellidos');
            $table->text('correo')->nullable()->unique();
            $table->text('password_hash');
            $table->text('telefono')->unique();
            $table->timestampTz('fecha_registro')->default(DB::raw('now()'));
            $table->boolean('activo')->default(true);
        });
        DB::statement('ALTER TABLE usuario ADD COLUMN ubicacion_actual point');

        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id_usuario')->cascadeOnDelete();
            $table->foreignUuid('id_rol')->constrained('rol', 'id_rol')->restrictOnDelete();
            $table->timestampTz('fecha_asignacion')->default(DB::raw('now()'));
            $table->primary(['id_usuario', 'id_rol']);
        });

        Schema::create('direccion_usuario', function (Blueprint $table) {
            $table->uuid('id_direccion')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id_usuario')->cascadeOnDelete();
            $table->text('etiqueta');
            $table->text('direccion_texto');
            $table->text('referencia')->nullable();
            $table->boolean('es_predeterminada')->default(false);
            $table->timestampTz('ultima_fecha_uso')->default(DB::raw('now()'));
        });
        DB::statement('ALTER TABLE direccion_usuario ADD COLUMN ubicacion_gps point NOT NULL');
        DB::statement("CREATE INDEX idx_direccion_uso ON direccion_usuario(id_usuario, ultima_fecha_uso DESC)");

        Schema::create('billetera_usuario', function (Blueprint $table) {
            $table->uuid('id_metodo')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id_usuario')->cascadeOnDelete();
            $table->text('token_externo');
            $table->text('marca_tarjeta')->nullable();
            $table->char('ultimos_4_digitos', 4)->nullable();
            $table->char('fecha_expiracion', 5)->nullable();
            $table->boolean('es_predeterminado')->default(false);
            $table->timestampTz('fecha_registro')->default(DB::raw('now()'));
            $table->boolean('activo')->default(true);
        });
        DB::statement("ALTER TABLE billetera_usuario ADD COLUMN tipo tipo_metodo_pago NOT NULL");
        DB::statement("ALTER TABLE billetera_usuario ADD COLUMN proveedor proveedor_pago NOT NULL");
        DB::statement("CREATE INDEX idx_billetera_usuario ON billetera_usuario(id_usuario) WHERE activo = true");
    }

    public function down(): void
    {
        Schema::dropIfExists('billetera_usuario');
        Schema::dropIfExists('direccion_usuario');
        Schema::dropIfExists('usuario_rol');
        Schema::dropIfExists('usuario');
        Schema::dropIfExists('rol');
    }
};
