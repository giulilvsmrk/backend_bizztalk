<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billetera_usuario', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id')->cascadeOnDelete();
            $table->text('token_externo');
            $table->text('marca_tarjeta')->nullable();
            $table->char('ultimos_4_digitos', 4)->nullable();
            $table->char('fecha_expiracion', 5)->nullable();
            $table->boolean('es_predeterminado')->default(false);
            $table->timestampTz('fecha_registro')->default(DB::raw('now()'));
            $table->boolean('activo')->default(true);
            $table->timestampTz('fecha_eliminacion')->nullable();
        });
        DB::statement("ALTER TABLE billetera_usuario ADD COLUMN tipo tipo_metodo_pago NOT NULL");
        DB::statement("ALTER TABLE billetera_usuario ADD COLUMN proveedor proveedor_pago NOT NULL");

        Schema::create('carrito', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id');
            $table->foreignUuid('id_sucursal_activa')->nullable()->constrained('sucursal', 'id');
            $table->timestampTz('fecha_actualizacion')->default(DB::raw('now()'));
            $table->unique('id_usuario');
        });

        Schema::create('item_carrito', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('id_carrito')->constrained('carrito', 'id')->cascadeOnDelete();
            $table->foreignUuid('id_producto')->constrained('producto', 'id');
            $table->integer('cantidad');
            $table->text('observacion')->nullable();
            $table->timestampTz('fecha_agregado')->default(DB::raw('now()'));
        });

        Schema::create('pedido', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->bigInteger('numero_orden_publico')->generatedAlways()->always();
            $table->foreignUuid('id_sucursal')->constrained('sucursal', 'id');
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id');
            $table->decimal('importe_subtotal', 12, 2)->default(0);
            $table->decimal('importe_envio', 12, 2)->default(0);
            $table->decimal('importe_descuento', 12, 2)->default(0);
            $table->decimal('importe_total', 12, 2)->default(0);
            $table->text('direccion_texto')->nullable();
            $table->timestampTz('fecha_programada')->nullable();
            $table->timestampTz('fecha_creacion')->default(DB::raw('now()'));
            $table->timestampTz('fecha_actualizacion')->default(DB::raw('now()'));
        });
        DB::statement('ALTER TABLE pedido ADD COLUMN ubicacion_entrega point');
        DB::statement("ALTER TABLE pedido ADD COLUMN estado estado_pedido DEFAULT 'pendiente'");
        DB::statement("ALTER TABLE pedido ADD COLUMN canal canal_venta NOT NULL");
        DB::statement("ALTER TABLE pedido ADD COLUMN tipo_entrega tipo_entrega NOT NULL");
        DB::statement("CREATE INDEX idx_pedido_numero ON pedido(numero_orden_publico)");

        Schema::create('detalle_pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('id_pedido')->constrained('pedido', 'id');
            $table->foreignUuid('id_producto')->constrained('producto', 'id');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('precio_original', 12, 2)->nullable();
            $table->decimal('descuento_aplicado', 12, 2)->default(0);
        });
        DB::statement("
            ALTER TABLE detalle_pedido
            ADD COLUMN subtotal decimal(12, 2)
            GENERATED ALWAYS AS (cantidad * precio_unitario) STORED
        ");

        Schema::create('historial_pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('id_pedido')->constrained('pedido', 'id');
            $table->timestampTz('fecha_cambio')->default(DB::raw('now()'));
            $table->text('observacion')->nullable();
            $table->foreignUuid('id_usuario_operador')->nullable()->constrained('usuario', 'id');
        });
        DB::statement("ALTER TABLE historial_pedido ADD COLUMN estado_anterior estado_pedido");
        DB::statement("ALTER TABLE historial_pedido ADD COLUMN estado_nuevo estado_pedido NOT NULL");

        Schema::create('transaccion_pago', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_pedido')->constrained('pedido', 'id');
            $table->foreignUuid('id_metodo_guardado')->nullable()->constrained('billetera_usuario', 'id');
            $table->decimal('monto_total', 12, 2);
            $table->char('moneda', 3)->default('BOB');
            $table->timestampTz('fecha_intento')->default(DB::raw('now()'));
            $table->timestampTz('fecha_confirmacion')->nullable();
            $table->text('id_referencia_banco')->nullable();
            $table->text('qr_imagen_url')->nullable();
            $table->jsonb('metadata_banco')->nullable();
        });
        DB::statement("ALTER TABLE transaccion_pago ADD COLUMN tipo_metodo tipo_metodo_pago NOT NULL");
        DB::statement("ALTER TABLE transaccion_pago ADD COLUMN estado estado_transaccion DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        Schema::dropIfExists('transaccion_pago');
        Schema::dropIfExists('historial_pedido');
        Schema::dropIfExists('detalle_pedido');
        Schema::dropIfExists('pedido');
        Schema::dropIfExists('item_carrito');
        Schema::dropIfExists('carrito');
        Schema::dropIfExists('billetera_usuario');
    }
};
