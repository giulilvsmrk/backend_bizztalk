<?php

declare(strict_types=1);

namespace App\Modules\MessagingOrders\Exceptions;

use Exception;

/**
 * AssistantGenerationException
 * Se lanza cuando hay error procesando en el Asistente Virtual
 */
class AssistantGenerationException extends Exception
{
    public function __construct(string $message = "Error en el Asistente Virtual")
    {
        parent::__construct($message);
    }
}
