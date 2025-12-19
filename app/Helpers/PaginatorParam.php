<?php

namespace App\Helpers;

use Illuminate\Http\Request;

class PaginatorParam
{
    public static function getNormalizedParams(Request $request): array
    {
        return [
            'start'   => (int) $request->input('start', 0),
            'length'  => (int) $request->input('length', 10),
            'sortBy'  => $request->input('sortBy', 'created_at'),
            'sortDir' => in_array($request->input('sortDir'), ['asc', 'desc'])
                ? $request->input('sortDir')
                : 'desc',
            'draw'    => (int) $request->input('draw', 1),
        ];
    }
}
