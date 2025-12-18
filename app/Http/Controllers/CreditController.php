<?php

namespace App\Http\Controllers;

use App\Helpers\PaginatorParam;
use App\Http\Resources\CreditResource;
use App\Models\Credit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Unk\LaravelApiResponse\Traits\{HttpResponse, HttpResponseWithDataTables};

class CreditController extends Controller
{
    use HttpResponse, HttpResponseWithDataTables;

    public function index(Request $request): JsonResponse
    {
        [
            'start' => $start,
            'length' => $length,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'draw' => $draw
        ] = PaginatorParam::getNormalizedParams($request);

        $baseQuery = Credit::query();
        $recordsTotal = $baseQuery->count();
        $filteredQuery = $this->applyFilters(clone $baseQuery, $request);
        $recordsFiltered = (clone $filteredQuery)->count();

        $data = (clone $filteredQuery)
            ->orderBy($sortBy, $sortDir)
            ->skip($start)
            ->take($length)
            ->get();

        return $this->successDataTable(
            CreditResource::collection($data),
            draw: $draw,
            start: $start,
            length: $length,
            message: 'Crédits chargés avec succès.',
            code: 200,
            recordsTotal: $recordsTotal,
            recordsFiltered: $recordsFiltered
        );
    }

    public function create(): JsonResponse
    {
        return $this->success(null, 'Prêt à créer un crédit.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'montant' => 'required|numeric|min:0',
            'monthly_payment' => 'nullable|numeric|min:0',
            'organization' => 'nullable|string|max:255',
        ]);

        $credit = Credit::create($validated);

        return $this->success(
            new CreditResource($credit),
            'Crédit créé avec succès.',
            201
        );
    }

    public function edit(int $id): JsonResponse
    {
        $credit = Credit::find($id);

        if (!$credit) {
            return $this->notFound('Crédit introuvable.');
        }

        return $this->success(
            new CreditResource($credit),
            'Crédit récupéré avec succès.'
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $credit = Credit::find($id);

        if (!$credit) {
            return $this->notFound('Crédit introuvable.');
        }

        $validated = $request->validate([
            'montant' => 'sometimes|required|numeric|min:0',
            'monthly_payment' => 'nullable|numeric|min:0',
            'organization' => 'nullable|string|max:255',
        ]);

        $credit->update($validated);

        return $this->success(
            new CreditResource($credit),
            'Crédit mis à jour avec succès.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $credit = Credit::find($id);

        if (!$credit) {
            return $this->notFound('Crédit introuvable.');
        }

        $credit->delete();

        return $this->success(null, 'Crédit supprimé avec succès.');
    }

    private function applyFilters(Builder $query, Request $request): Builder
    {
        $filters = ['id', 'organization'];

        foreach ($filters as $filter) {
            $value = $request->input($filter);
            if ($request->filled($filter)) {
                if ($filter === 'id') {
                    $query->where($filter, $value);
                } else {
                    $query->where($filter, 'like', "%{$value}%");
                }
            }
        }

        return $query;
    }
}
