<?php

namespace App\Http\Controllers;

use App\Helpers\PaginatorParam;
use App\Http\Resources\CreditEntryResource;
use App\Models\CreditEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Unk\LaravelApiResponse\Traits\{HttpResponse, HttpResponseWithDataTables};

class CreditEntryController extends Controller
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

        $baseQuery = CreditEntry::query();
        $recordsTotal = $baseQuery->count();
        $filteredQuery = $this->applyFilters(clone $baseQuery, $request);
        $recordsFiltered = (clone $filteredQuery)->count();

        $data = (clone $filteredQuery)
            ->orderBy($sortBy, $sortDir)
            ->skip($start)
            ->take($length)
            ->get();

        return $this->successDataTable(
            CreditEntryResource::collection($data),
            draw: $draw,
            start: $start,
            length: $length,
            message: 'Entrées de crédit chargées avec succès.',
            code: 200,
            recordsTotal: $recordsTotal,
            recordsFiltered: $recordsFiltered
        );
    }

    public function create(): JsonResponse
    {
        return $this->success(null, 'Prêt à créer une entrée de crédit.');
    }

    public function all(): JsonResponse
    {
        $entries = CreditEntry::all();

        return $this->success(
            CreditEntryResource::collection($entries),
            'Toutes les entrées de crédit chargées avec succès.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'credit_id' => 'required|exists:credits,id',
            'amount' => 'required|numeric|min:0',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
        ]);

        $entry = CreditEntry::create($validated);

        return $this->success(
            new CreditEntryResource($entry),
            'Entrée de crédit créée avec succès.',
            201
        );
    }

    public function edit(int $id): JsonResponse
    {
        $entry = CreditEntry::find($id);

        if (!$entry) {
            return $this->notFound('Entrée de crédit introuvable.');
        }

        return $this->success(
            new CreditEntryResource($entry),
            'Entrée de crédit récupérée avec succès.'
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $entry = CreditEntry::find($id);

        if (!$entry) {
            return $this->notFound('Entrée de crédit introuvable.');
        }

        $validated = $request->validate([
            'amount' => 'sometimes|required|numeric|min:0',
            'month' => 'sometimes|required|integer|min:1|max:12',
            'year' => 'sometimes|required|integer|min:2000',
        ]);

        $entry->update($validated);

        return $this->success(
            new CreditEntryResource($entry),
            'Entrée de crédit mise à jour avec succès.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $entry = CreditEntry::find($id);

        if (!$entry) {
            return $this->notFound('Entrée de crédit introuvable.');
        }

        $entry->delete();

        return $this->success(null, 'Entrée de crédit supprimée avec succès.');
    }

    private function applyFilters(Builder $query, Request $request): Builder
    {
        $filters = ['id', 'credit_id', 'amount', 'month', 'year'];

        foreach ($filters as $filter) {
            $value = $request->input($filter);
            if ($request->filled($filter)) {
                $query->where($filter, $value);
            }
        }

        return $query;
    }
}
