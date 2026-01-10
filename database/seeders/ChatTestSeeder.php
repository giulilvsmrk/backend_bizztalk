<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Negocio;
use App\Models\Producto;
use Illuminate\Support\Facades\Hash;

class ChatTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Este seeder crea datos de prueba para testear el chat IA:
     * - 1 Usuario propietario
     * - 1 Negocio (Pizzería)
     * - 5 Productos (Pizzas)
     */
    public function run(): void
    {
        echo "\n📍 Creando datos de prueba para Chat IA...\n";

        // 1. Obtener o crear usuario propietario
        echo "➤ Buscando usuario propietario...\n";
        $usuario = Usuario::where('correo', 'admin@pizzeria.com')->first();
        
        if (!$usuario) {
            echo "➤ Creando usuario propietario...\n";
            $usuario = Usuario::create([
                'nombres' => 'Admin',
                'apellidos' => 'Pizza',
                'correo' => 'admin@pizzeria.com',
                'password_hash' => Hash::make('password123'),
                'telefono' => '+591 99999999',
                'activo' => true,
            ]);
            echo "✓ Usuario creado: {$usuario->correo}\n";
        } else {
            echo "✓ Usuario existe: {$usuario->correo}\n";
        }

        // 2. Obtener o crear negocio
        echo "➤ Buscando negocio (Pizzería)...\n";
        $negocio = Negocio::where('nombre', 'Pizzería Express')->first();
        
        if (!$negocio) {
            echo "➤ Creando negocio...\n";
            $negocio = Negocio::create([
                'id_propietario' => $usuario->id,
                'nombre' => 'Pizzería Express',
                'nit' => '123456789',
                'descripcion' => 'Pizzería de calidad con ingredientes frescos. Entregamos en todo el centro de la ciudad.',
                'logotipo_url' => 'https://via.placeholder.com/200',
                'activo' => true,
            ]);
            echo "✓ Negocio creado: {$negocio->nombre}\n";
        } else {
            echo "✓ Negocio existe: {$negocio->nombre}\n";
        }
        
        echo "✓ ID del negocio: {$negocio->id}\n";

        // 3. Crear productos (Pizzas)
        echo "➤ Verificando productos...\n";
        $productosExistentes = $negocio->productos()->count();
        
        if ($productosExistentes > 0) {
            echo "✓ El negocio ya tiene {$productosExistentes} productos\n";
        } else {
            echo "➤ Creando productos (Pizzas)...\n";
            $productos = [
                [
                    'nombre' => 'Pizza Margarita',
                    'descripcion' => 'Tomate, mozzarella, albahaca fresca y aceite de oliva. Clásica italiana.',
                    'precio_base' => 89.99,
                ],
                [
                    'nombre' => 'Pizza Pepperoni',
                    'descripcion' => 'Mozzarella, pepperoni crujiente y tomate. Favorita de todos.',
                    'precio_base' => 99.99,
                ],
                [
                    'nombre' => 'Pizza Cuatro Quesos',
                    'descripcion' => 'Mozzarella, parmesano, azul y queso de cabra. Para amantes del queso.',
                    'precio_base' => 109.99,
                ],
                [
                    'nombre' => 'Pizza Hawaiana',
                    'descripcion' => 'Jamón, piña, mozzarella y tomate. Deliciosa combinación de sabores.',
                    'precio_base' => 104.99,
                ],
                [
                    'nombre' => 'Pizza Vegetariana',
                    'descripcion' => 'Espinaca, champiñones, pimientos, cebolla y tomate. Opción saludable.',
                    'precio_base' => 94.99,
                ],
            ];

            foreach ($productos as $productoData) {
                $producto = Producto::create([
                    'id_negocio' => $negocio->id,
                    'nombre' => $productoData['nombre'],
                    'descripcion' => $productoData['descripcion'],
                    'precio_base' => $productoData['precio_base'],
                    'activo' => true,
                ]);
                echo "✓ Producto: {$producto->nombre} - \${$producto->precio_base}\n";
            }
        }

        echo "\n✅ Datos de prueba listos!\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "📌 INFORMACIÓN PARA TESTEAR:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "ID del negocio: {$negocio->id}\n";
        echo "Nombre: {$negocio->nombre}\n";
        echo "Descripción: {$negocio->descripcion}\n";
        echo "Productos: " . $negocio->productos()->count() . "\n";
        echo "\nURL para testear el chat:\n";
        echo "POST http://localhost:8000/api/ai/chat/send\n";
        echo "{\n";
        echo "  \"business_id\": \"{$negocio->id}\",\n";
        echo "  \"message\": \"¿Tienes pizzas disponibles?\"\n";
        echo "}\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    }
}

