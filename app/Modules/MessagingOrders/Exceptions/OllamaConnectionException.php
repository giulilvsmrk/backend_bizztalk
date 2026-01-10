<?php

declare(strict_types=1);

namespace App\Modules\MessagingOrders\Exceptions;

use Exception;

/**
 * OllamaConnectionException
 * Se lanza cuando hay problemas de conexión con Ollama
 */
class OllamaConnectionException extends Exception
{
    public function __construct(string $message = "No se puede conectar a Ollama")
    {
        parent::__construct($message);
    }
}
