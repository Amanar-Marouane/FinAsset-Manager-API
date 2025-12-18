<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Unk\LaravelApiResponse\Traits\HttpResponse;

abstract class Controller
{
    use HttpResponse;

    /**
     * Return a 404 not found response.
     */
    protected function notFound(string $message = 'Ressource introuvable.'): JsonResponse
    {
        return $this->error($message, 404);
    }

    protected function multiProcess(callable $callback): JsonResponse
    {
        DB::beginTransaction();
        try {
            $result = $callback();
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), code: 500);
        }
    }
}
