<?php

namespace App\Http\Controllers;

use App\Helpers\PaginatorParam;
use App\Http\Resources\TerrainResource;
use App\Models\Terrain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Unk\LaravelApiResponse\Traits\{HttpResponse, HttpResponseWithDataTables};

class TerrainController extends Controller
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

        $baseQuery = Terrain::query();
        $recordsTotal = $baseQuery->count();
        $filteredQuery = $this->applyFilters(clone $baseQuery, $request);
        $recordsFiltered = (clone $filteredQuery)->count();

        $data = (clone $filteredQuery)
            ->orderBy($sortBy, $sortDir)
            ->skip($start)
            ->take($length)
            ->get();

        return $this->successDataTable(
            TerrainResource::collection($data),
            draw: $draw,
            start: $start,
            length: $length,
            message: 'Terrains chargés avec succès.',
            code: 200,
            recordsTotal: $recordsTotal,
            recordsFiltered: $recordsFiltered
        );
    }

    public function create(): JsonResponse
    {
        return $this->success(null, 'Prêt à créer un terrain.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $terrain = Terrain::create($validated);

        return $this->success(
            new TerrainResource($terrain),
            'Terrain créé avec succès.',
            201
        );
    }

    public function edit(int $id): JsonResponse
    {
        $terrain = Terrain::find($id);

        if (!$terrain) {
            return $this->notFound('Terrain introuvable.');
        }

        return $this->success(
            new TerrainResource($terrain),
            'Terrain récupéré avec succès.'
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $terrain = Terrain::find($id);

        if (!$terrain) {
            return $this->notFound('Terrain introuvable.');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $terrain->update($validated);

        return $this->success(
            new TerrainResource($terrain),
            'Terrain mis à jour avec succès.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $terrain = Terrain::find($id);

        if (!$terrain) {
            return $this->notFound('Terrain introuvable.');
        }

        $terrain->delete();

        return $this->success(null, 'Terrain supprimé avec succès.');
    }

    private function applyFilters(Builder $query, Request $request): Builder
    {
        $filters = ['id', 'name', 'address'];

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
