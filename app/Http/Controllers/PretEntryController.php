<?php

namespace App\Http\Controllers;

use App\Helpers\PaginatorParam;
use App\Http\Resources\PretEntryResource;
use App\Models\PretEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Unk\LaravelApiResponse\Traits\{HttpResponse, HttpResponseWithDataTables};

class PretEntryController extends Controller
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

        $baseQuery = PretEntry::query();
        $recordsTotal = $baseQuery->count();
        $filteredQuery = $this->applyFilters(clone $baseQuery, $request);
        $recordsFiltered = (clone $filteredQuery)->count();

        $data = (clone $filteredQuery)
            ->orderBy($sortBy, $sortDir)
            ->skip($start)
            ->take($length)
            ->get();

        return $this->successDataTable(
            PretEntryResource::collection($data),
            draw: $draw,
            start: $start,
            length: $length,
            message: 'Entrées de prêt chargées avec succès.',
            code: 200,
            recordsTotal: $recordsTotal,
            recordsFiltered: $recordsFiltered
        );
    }

    public function create(): JsonResponse
    {
        return $this->success(null, 'Prêt à créer une entrée de prêt.');
    }

    public function all(): JsonResponse
    {
        $entries = PretEntry::all();

        return $this->success(
            PretEntryResource::collection($entries),
            'Toutes les entrées de prêt chargées avec succès.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pret_id' => 'required|exists:prets,id',
            'amount' => 'required|numeric|min:0',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
        ]);

        $entry = PretEntry::create($validated);

        return $this->success(
            new PretEntryResource($entry),
            'Entrée de prêt créée avec succès.',
            201
        );
    }

    public function edit(int $id): JsonResponse
    {
        $entry = PretEntry::find($id);

        if (!$entry) {
            return $this->notFound('Entrée de prêt introuvable.');
        }

        return $this->success(
            new PretEntryResource($entry),
            'Entrée de prêt récupérée avec succès.'
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $entry = PretEntry::find($id);

        if (!$entry) {
            return $this->notFound('Entrée de prêt introuvable.');
        }

        $validated = $request->validate([
            'amount' => 'sometimes|required|numeric|min:0',
            'month' => 'sometimes|required|integer|min:1|max:12',
            'year' => 'sometimes|required|integer|min:2000',
        ]);

        $entry->update($validated);

        return $this->success(
            new PretEntryResource($entry),
            'Entrée de prêt mise à jour avec succès.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $entry = PretEntry::find($id);

        if (!$entry) {
            return $this->notFound('Entrée de prêt introuvable.');
        }

        $entry->delete();

        return $this->success(null, 'Entrée de prêt supprimée avec succès.');
    }

    private function applyFilters(Builder $query, Request $request): Builder
    {
        $filters = ['id', 'pret_id', 'amount', 'month', 'year'];

        foreach ($filters as $filter) {
            $value = $request->input($filter);
            if ($request->filled($filter)) {
                $query->where($filter, $value);
            }
        }

        return $query;
    }
}
