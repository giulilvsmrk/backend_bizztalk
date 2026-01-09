<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumUtils;

enum CanalVenta: string
{
    use EnumUtils;

    case APP = 'app';
    case IA_VOZ = 'ia_voz';
    case IA_TEXTO = 'ia_texto';
    case WEB = 'web';

    public function label(): string
    {
        return match($this) {
            self::APP => 'Aplicación Móvil',
            self::IA_VOZ => 'Asistente de Voz (IA)',
            self::IA_TEXTO => 'Chatbot Inteligente',
            self::WEB => 'Plataforma Web',
        };
    }
}
