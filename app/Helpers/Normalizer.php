<?php

namespace App\Helpers;

use Illuminate\Http\Request;

class Normalizer
{
    public static function normalizeRequestHeader(Request $request, string $header, mixed $default = ''): string
    {
        $header = $request->header($header);
        if (is_array($header)) $header = $header[0] ?? $default;
        return trim((string) $header) ?: $default;
    }

    public static function normalizeRequestPayload(Request $request, string $input, mixed $default = ''): string
    {
        $input = $request->input($input);
        if (is_array($input)) $input = $input[0] ?? $default;
        $cleaned_val = trim((string) $input);
        return empty($cleaned_val) ? $default : $cleaned_val;
    }
}
