<?php

namespace App\Http\Controllers;

use App\Helpers\PaginatorParam;
use App\Http\Resources\BuildingTypeResource;
use App\Models\BuildingType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Unk\LaravelApiResponse\Traits\{HttpResponse, HttpResponseWithDataTables};
use Illuminate\Database\Eloquent\Builder;

class BuildingTypeController extends Controller
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

        /** @var \Illuminate\Database\Eloquent\Builder<BuildingType> $baseQuery */
        $baseQuery = BuildingType::query();

        $recordsTotal = $baseQuery->count();
        $filteredQuery = $this->applyFilters(clone $baseQuery, $request);
        $recordsFiltered = (clone $filteredQuery)->count();

        $data = (clone $filteredQuery)
            ->orderBy($sortBy, $sortDir)
            ->skip($start)
            ->take($length)
            ->withCount('buildings')
            ->get();

        return $this->successDataTable(
            BuildingTypeResource::collection($data),
            draw: $draw,
            start: $start,
            length: $length,
            message: 'Types de bâtiments chargés avec succès.',
            code: 200,
            recordsTotal: $recordsTotal,
            recordsFiltered: $recordsFiltered
        );
    }

    public function all(): JsonResponse
    {
        $buildingTypes = BuildingType::all();

        return $this->success(
            BuildingTypeResource::collection($buildingTypes),
            'Tous les types de bâtiments ont été chargés avec succès.'
        );
    }

    public function create(): JsonResponse
    {
        return $this->success(
            null,
            'Prêt à créer un type de bâtiment.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:building_types,name',
        ]);

        $buildingType = BuildingType::create($validated);

        return $this->success(
            new BuildingTypeResource($buildingType),
            'Type de bâtiment créé avec succès.',
            201
        );
    }

    public function edit(int $id): JsonResponse
    {
        $buildingType = BuildingType::withCount('buildings')->find($id);

        if (!$buildingType) {
            return $this->notFound('Type de bâtiment introuvable.');
        }

        return $this->success(
            new BuildingTypeResource($buildingType),
            'Type de bâtiment récupéré avec succès.'
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $buildingType = BuildingType::find($id);

        if (!$buildingType) {
            return $this->notFound('Type de bâtiment introuvable.');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:building_types,name,' . $id,
        ]);

        $buildingType->update($validated);

        return $this->success(
            new BuildingTypeResource($buildingType),
            'Type de bâtiment mis à jour avec succès.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $buildingType = BuildingType::find($id);

        if (!$buildingType) {
            return $this->notFound('Type de bâtiment introuvable.');
        }

        // Check if building type is in use
        if ($buildingType->buildings()->exists()) {
            return $this->error('Impossible de supprimer un type de bâtiment qui est utilisé.', 409);
        }

        $buildingType->delete();

        return $this->success(
            null,
            'Type de bâtiment supprimé avec succès.'
        );
    }

    /**
     * Apply request filters to the query.
     *
     * @param Builder<\App\Models\BuildingType> $query
     * @param Request $request
     * @return Builder<\App\Models\BuildingType>
     */
    private function applyFilters(Builder $query, Request $request): Builder
    {
        $filters = [
            'id',
            'name',
        ];

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
