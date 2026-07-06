<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class EntityNotFoundException extends RuntimeException
{
    public function __construct(string $entity = 'Resource')
    {
        parent::__construct("{$entity} not found.");
    }
}
