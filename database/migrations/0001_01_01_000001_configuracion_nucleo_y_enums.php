<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto";');

        $enums = [
            'estado_pedido' => "'pendiente', 'pagado', 'preparando', 'en_camino', 'entregado', 'cancelado'",
            'canal_venta' => "'app', 'ia_voz', 'ia_texto', 'web'",
            'rol_sucursal' => "'empleado'",
            'tipo_entrega' => "'delivery', 'recojo', 'mesa'",
            'tipo_descuento' => "'porcentaje', 'monto_fijo', 'envio_gratis'",
            'alcance_promo' => "'producto', 'sucursal', 'global'",
            'tipo_metodo_pago' => "'efectivo', 'tarjeta_credito', 'tarjeta_debito', 'qr_bancario', 'billetera_movil'",
            'estado_transaccion' => "'pendiente', 'aprobado', 'rechazado', 'reembolsado', 'expirado'",
            'proveedor_pago' => "'stripe', 'cybersource', 'libelula', 'pasarela_qr_local', 'efectivo_manual', 'simulado', 'transferencia_manual'",
        ];

        foreach ($enums as $name => $values) {
            DB::statement("DROP TYPE IF EXISTS $name CASCADE");
            DB::statement("CREATE TYPE $name AS ENUM ($values)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP EXTENSION IF EXISTS "pgcrypto"');

        $types = [
            'estado_pedido',
            'canal_venta',
            'rol_sucursal',
            'tipo_entrega',
            'tipo_descuento',
            'alcance_promo',
            'tipo_metodo_pago',
            'estado_transaccion',
            'proveedor_pago',
        ];

        foreach ($types as $type) {
            DB::statement("DROP TYPE IF EXISTS $type CASCADE");
        }
    }
};
