<?php

declare(strict_types=1);

namespace App\Enums;

enum ProposalOrigin: string
{
    case App = 'APP';
    case Site = 'SITE';
    case Api = 'API';
}
