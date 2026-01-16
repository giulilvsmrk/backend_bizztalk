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
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->text('nombre')->unique();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestampTz('fecha_creacion')->default(DB::raw('now()'));
            $table->timestampTz('fecha_actualizacion')->default(DB::raw('now()'));
        });

        DB::table('rol')->insert([
            ['id' => DB::raw('gen_random_uuid()'), 'nombre' => 'cliente', 'descripcion' => 'Usuario final', 'activo' => true],
            ['id' => DB::raw('gen_random_uuid()'), 'nombre' => 'dueno', 'descripcion' => 'Propietario', 'activo' => true],
            ['id' => DB::raw('gen_random_uuid()'), 'nombre' => 'admin_plataforma', 'descripcion' => 'Admin', 'activo' => true],
        ]);

        Schema::create('usuario', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->text('nombres');
            $table->text('apellidos');
            $table->text('correo')->nullable()->unique();
            $table->text('password_hash');
            $table->text('telefono')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestampTz('fecha_creacion')->default(DB::raw('now()'));
            $table->timestampTz('fecha_actualizacion')->default(DB::raw('now()'));
            $table->timestampTz('fecha_eliminacion')->nullable();
        });
        DB::statement('ALTER TABLE usuario ADD COLUMN ubicacion_actual point');
        DB::statement('CREATE INDEX idx_usuario_telefono ON usuario(telefono)');

        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id')->cascadeOnDelete();
            $table->foreignUuid('id_rol')->constrained('rol', 'id')->restrictOnDelete();
            $table->timestampTz('fecha_asignacion')->default(DB::raw('now()'));
            $table->primary(['id_usuario', 'id_rol']);
        });

        Schema::create('direccion_usuario', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id')->cascadeOnDelete();
            $table->text('etiqueta');
            $table->text('direccion_texto');
            $table->text('referencia')->nullable();
            $table->boolean('es_predeterminada')->default(false);
            $table->timestampTz('ultima_fecha_uso')->default(DB::raw('now()'));
            $table->timestampTz('fecha_creacion')->default(DB::raw('now()'));
            $table->timestampTz('fecha_eliminacion')->nullable();
        });
        DB::statement('ALTER TABLE direccion_usuario ADD COLUMN ubicacion_gps point');
        DB::statement("CREATE INDEX idx_direccion_uso ON direccion_usuario(id_usuario, ultima_fecha_uso DESC)");
    }

    public function down(): void
    {
        Schema::dropIfExists('direccion_usuario');
        Schema::dropIfExists('usuario_rol');
        Schema::dropIfExists('usuario');
        Schema::dropIfExists('rol');
    }
};
