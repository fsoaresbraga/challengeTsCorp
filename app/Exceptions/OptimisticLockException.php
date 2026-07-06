<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class OptimisticLockException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Version conflict.');
    }
}
