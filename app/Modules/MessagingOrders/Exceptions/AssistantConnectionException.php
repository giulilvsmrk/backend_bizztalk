<?php

declare(strict_types=1);

namespace App\Modules\MessagingOrders\Exceptions;

use Exception;

/**
 * AssistantConnectionException
 * Se lanza cuando hay problemas de conexión con el Asistente Virtual
 */
class AssistantConnectionException extends Exception
{
    public function __construct(string $message = "No se puede conectar al Asistente Virtual")
    {
        parent::__construct($message);
    }
}
