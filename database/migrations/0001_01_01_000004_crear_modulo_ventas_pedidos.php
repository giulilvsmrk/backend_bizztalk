<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrito', function (Blueprint $table) {
            $table->uuid('id_carrito')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id_usuario');
            $table->timestampTz('ultima_modificacion')->default(DB::raw('now()'));
            $table->unique('id_usuario');
        });

        Schema::create('item_carrito', function (Blueprint $table) {
            $table->id('id_item');
            $table->foreignUuid('id_carrito')->constrained('carrito', 'id_carrito')->cascadeOnDelete();
            $table->foreignUuid('id_producto')->constrained('producto', 'id_producto');
            $table->foreignUuid('id_sucursal_origen')->constrained('sucursal', 'id_sucursal');
            $table->integer('cantidad');
            $table->text('observacion')->nullable();
            $table->timestampTz('fecha_agregado')->default(DB::raw('now()'));
        });
        DB::statement("CREATE INDEX idx_item_carrito_sucursal ON item_carrito(id_sucursal_origen)");

        Schema::create('orden_compra', function (Blueprint $table) {
            $table->uuid('id_orden_compra')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id_usuario');
            $table->decimal('importe_productos_total', 12, 2)->default(0);
            $table->decimal('importe_delivery_total', 12, 2)->default(0);
            $table->decimal('importe_descuento_total', 12, 2)->default(0);
            $table->decimal('importe_final_total', 12, 2)->default(0);
            $table->timestampTz('fecha_registro')->default(DB::raw('now()'));
        });

        Schema::create('pedido', function (Blueprint $table) {
            $table->uuid('id_pedido')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_orden_compra')->constrained('orden_compra', 'id_orden_compra')->cascadeOnDelete();
            $table->bigInteger('numero_orden_publico');
            $table->foreignUuid('id_sucursal')->constrained('sucursal', 'id_sucursal');
            $table->foreignUuid('id_usuario')->constrained('usuario', 'id_usuario');
            $table->decimal('importe_subtotal', 12, 2)->default(0);
            $table->decimal('importe_envio', 12, 2)->default(0);
            $table->decimal('importe_descuento', 12, 2)->default(0);
            $table->decimal('importe_total', 12, 2)->default(0);
            $table->text('direccion_texto')->nullable();
            $table->timestampTz('fecha_programada')->nullable();
            $table->timestampTz('fecha_creacion')->default(DB::raw('now()'));
        });
        DB::statement("ALTER TABLE pedido ALTER COLUMN numero_orden_publico ADD GENERATED ALWAYS AS IDENTITY");
        DB::statement("ALTER TABLE pedido ADD COLUMN estado estado_pedido DEFAULT 'pendiente'");
        DB::statement("ALTER TABLE pedido ADD COLUMN canal canal_venta NOT NULL");
        DB::statement("ALTER TABLE pedido ADD COLUMN tipo_entrega tipo_entrega NOT NULL");
        DB::statement('ALTER TABLE pedido ADD COLUMN ubicacion_entrega point');
        DB::statement("CREATE INDEX idx_pedido_numero_publico ON pedido(numero_orden_publico)");
        DB::statement("CREATE INDEX idx_pedido_orden_padre ON pedido(id_orden_compra)");
        DB::statement("CREATE INDEX idx_pedido_sucursal_estado ON pedido (id_sucursal, fecha_creacion) WHERE estado = 'pendiente'");

        Schema::create('detalle_pedido', function (Blueprint $table) {
            $table->id('id_detalle');
            $table->foreignUuid('id_pedido')->constrained('pedido', 'id_pedido');
            $table->foreignUuid('id_producto')->constrained('producto', 'id_producto');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('precio_original', 12, 2)->nullable();
            $table->decimal('descuento_aplicado', 12, 2)->default(0);
        });
        DB::statement("ALTER TABLE detalle_pedido ADD COLUMN subtotal numeric(12,2) GENERATED ALWAYS AS (cantidad * precio_unitario) STORED");

        Schema::create('transaccion_pago', function (Blueprint $table) {
            $table->uuid('id_transaccion')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('id_orden_compra')->constrained('orden_compra', 'id_orden_compra');
            $table->foreignUuid('id_metodo_guardado')->nullable()->constrained('billetera_usuario', 'id_metodo');
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
        DB::statement("CREATE INDEX idx_transaccion_orden ON transaccion_pago(id_orden_compra)");

        Schema::create('historial_pedido', function (Blueprint $table) {
            $table->id('id_historial');
            $table->foreignUuid('id_pedido')->constrained('pedido', 'id_pedido');
            $table->timestampTz('fecha_cambio')->default(DB::raw('now()'));
            $table->text('observacion')->nullable();
            $table->foreignUuid('id_usuario_operador')->nullable()->constrained('usuario', 'id_usuario');
        });
        DB::statement("ALTER TABLE historial_pedido ADD COLUMN estado_anterior estado_pedido");
        DB::statement("ALTER TABLE historial_pedido ADD COLUMN estado_nuevo estado_pedido NOT NULL");
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_pedido');
        Schema::dropIfExists('transaccion_pago');
        Schema::dropIfExists('detalle_pedido');
        Schema::dropIfExists('pedido');
        Schema::dropIfExists('orden_compra');
        Schema::dropIfExists('item_carrito');
        Schema::dropIfExists('carrito');
    }
};
