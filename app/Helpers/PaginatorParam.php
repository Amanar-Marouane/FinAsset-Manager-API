<?php

namespace App\Helpers;

use Illuminate\Http\Request;

class PaginatorParam
{
    public static function getNormalizedParams(Request $request): array
    {
        $defaults = [
            'start' => 0,
            'length' => 10,
            'sortBy' => 'created_at',
            'sortDir' => 'desc',
            'draw' => 1
        ];
        return collect($defaults)
            ->map(fn($default, $key) => Normalizer::normalizeRequestPayload($request, $key, $default))
            ->toArray();
    }
}
