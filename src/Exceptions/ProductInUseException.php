<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class ProductInUseException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Products included in an order cannot be deleted.');
    }
}
