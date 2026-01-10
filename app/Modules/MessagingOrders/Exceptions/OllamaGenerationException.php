<?php

declare(strict_types=1);

namespace App\Modules\MessagingOrders\Exceptions;

use Exception;

/**
 * OllamaGenerationException
 * Se lanza cuando hay error generando respuesta
 */
class OllamaGenerationException extends Exception
{
    public function __construct(string $message = "Error generando respuesta")
    {
        parent::__construct($message);
    }
}
