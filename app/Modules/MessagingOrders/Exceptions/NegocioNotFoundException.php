<?php

declare(strict_types=1);

namespace App\Modules\MessagingOrders\Exceptions;

use Exception;

/**
 * NegocioNotFoundException
 * Se lanza cuando no se encuentra el negocio
 */
class NegocioNotFoundException extends Exception
{
    public function __construct(string $message = "Negocio no encontrado")
    {
        parent::__construct($message);
    }
}
