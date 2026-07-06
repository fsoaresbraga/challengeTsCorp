<?php

declare(strict_types=1);

namespace Documentation\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Proposal Management API',
    description: 'REST API for client and proposal management (TsCorp challenge).',
)]
#[OA\Server(url: '/api', description: 'API base path')]
final class OpenApiSpec
{
}
