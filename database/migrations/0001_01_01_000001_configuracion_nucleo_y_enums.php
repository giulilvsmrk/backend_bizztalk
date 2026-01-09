<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "unaccent";');
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto";');

        $enums = [
            'estado_pedido' => "'pendiente', 'pagado', 'preparando', 'en_camino', 'entregado', 'cancelado'",
            'canal_venta' => "'app', 'ia_voz', 'ia_texto', 'web'",
            'rol_sucursal' => "'empleado', 'encargado'",
            'tipo_entrega' => "'delivery', 'recojo', 'mesa'",
            'tipo_descuento' => "'porcentaje', 'monto_fijo', 'envio_gratis'",
            'alcance_promo' => "'producto', 'sucursal', 'global'",
            'tipo_metodo_pago' => "'efectivo', 'tarjeta_credito', 'tarjeta_debito', 'qr_bancario', 'billetera_movil'",
            'estado_transaccion' => "'pendiente', 'aprobado', 'rechazado', 'reembolsado', 'expirado'",
            'proveedor_pago' => "'stripe', 'cybersource', 'libelula', 'pasarela_qr_local', 'efectivo_manual', 'simulado', 'transferencia_manual'",
            'tipo_contacto' => "'whatsapp', 'fijo', 'movil', 'fax', 'call_center'",
            'tipo_vehiculo' => "'moto', 'bicicleta', 'auto', 'camion', 'pie', 'drone'",
        ];

        foreach ($enums as $name => $values) {
            DB::statement("DROP TYPE IF EXISTS $name CASCADE");
            DB::statement("CREATE TYPE $name AS ENUM ($values)");
        }
    }

    public function down(): void
    {
        DB::statement('DROP EXTENSION IF EXISTS "pgcrypto"');
        DB::statement('DROP EXTENSION IF EXISTS "unaccent"');

        $types = ['estado_pedido', 'canal_venta', 'rol_sucursal', 'tipo_entrega',
                    'tipo_descuento', 'alcance_promo', 'tipo_metodo_pago',
                    'estado_transaccion', 'proveedor_pago', 'tipo_contacto', 'tipo_vehiculo'];

        foreach ($types as $type) {
            DB::statement("DROP TYPE IF EXISTS $type CASCADE");
        }
    }
};
