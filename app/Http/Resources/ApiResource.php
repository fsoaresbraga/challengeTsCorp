<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\Concerns\FormatsApiDates;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class ApiResource extends JsonResource
{
    use FormatsApiDates;

    public static $wrap = 'data';
}
