<?php

declare(strict_types=1);

namespace App\Modules\MessagingOrders\Services;

use App\Models\Negocio;
use Illuminate\Database\Eloquent\Collection;

/**
 * PromptBuilder
 * 
 * Servicio para construir prompts bien estructurados
 * que Ollama puede entender y procesar.
 */
class PromptBuilder
{
    /**
     * Construir el prompt del sistema (instrucciones)
     */
    public function construirPromptSistema(Negocio $negocio): string
    {
        $productos = $this->formatearProductos($negocio->productos);

        return <<<PROMPT
Eres un asistente de vendedor inteligente para {$negocio->nombre}.

INFORMACIÓN DEL NEGOCIO:
- Nombre: {$negocio->nombre}
- Descripción: {$negocio->descripcion}

PRODUCTOS DISPONIBLES:
{$productos}

TUS RESPONSABILIDADES:
1. Ayudar clientes a encontrar productos
2. Responder sobre precios y disponibilidad
3. Sugerir productos según necesidades
4. Mantener contexto de la conversación
5. Ser amable y conciso

COMPORTAMIENTO:
- Responde en español
- Sé profesional y amable
- Si no sabes, di claramente
- Nunca inventes precios
- Respuestas breves (máximo 3 párrafos)
PROMPT;
    }

    /**
     * Construir mensaje del usuario con contexto
     */
    public function construirMensajeUsuario(
        string $mensaje,
        ?array $historial = null
    ): string {
        $partes = [];

        if (!empty($historial)) {
            $partes[] = "CONTEXTO DE CONVERSACIÓN:";
            foreach ($historial as $item) {
                $partes[] = "Cliente: {$item['mensaje_usuario']}";
                $partes[] = "Asistente: {$item['respuesta_bot']}";
            }
            $partes[] = "";
        }

        $partes[] = "Cliente: {$mensaje}";
        $partes[] = "Asistente:";

        return implode("\n", $partes);
    }

    /**
     * Formatear productos para el prompt
     */
    public function formatearProductos($productos): string
    {
        if ($productos->isEmpty()) {
            return "No hay productos disponibles.";
        }

        $lineas = [];
        foreach ($productos as $producto) {
            $lineas[] = "- {$producto->nombre}: \${$producto->precio_base}";
        }

        return implode("\n", $lineas);
    }

    /**
     * Construir prompt completo
     */
    public function construirPromptCompleto(
        Negocio $negocio,
        string $mensajeUsuario,
        ?array $historial = null
    ): string {
        $promptSistema = $this->construirPromptSistema($negocio);
        $promptUsuario = $this->construirMensajeUsuario($mensajeUsuario, $historial);

        return "{$promptSistema}\n\n{$promptUsuario}";
    }

    /**
     * Formatear historial
     */
    public function formatearHistorial(Collection $chats): array
    {
        return $chats->map(function ($chat) {
            return [
                'mensaje_usuario' => $chat->mensaje_usuario,
                'respuesta_bot' => $chat->respuesta_bot,
            ];
        })->toArray();
    }
}
