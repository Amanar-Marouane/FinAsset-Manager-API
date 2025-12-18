<?php

namespace App\Http\Controllers;

use App\Helpers\PaginatorParam;
use App\Http\Resources\BuildingResource;
use App\Models\Building;
use App\Models\BuildingType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Unk\LaravelApiResponse\Traits\{HttpResponse, HttpResponseWithDataTables};
use Illuminate\Database\Eloquent\Builder;

class BuildingController extends Controller
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

        /** @var \Illuminate\Database\Eloquent\Builder<Building> $baseQuery */
        $baseQuery = Building::query();

        $recordsTotal = $baseQuery->count();
        $filteredQuery = $this->applyFilters(clone $baseQuery, $request);
        $recordsFiltered = (clone $filteredQuery)->count();

        $data = (clone $filteredQuery)
            ->orderBy($sortBy, $sortDir)
            ->skip($start)
            ->take($length)
            ->with(['type:id,name'])
            ->get();

        return $this->successDataTable(
            BuildingResource::collection($data),
            draw: $draw,
            start: $start,
            length: $length,
            message: 'Bâtiments chargés avec succès.',
            code: 200,
            recordsTotal: $recordsTotal,
            recordsFiltered: $recordsFiltered
        );
    }

    /**
     * Apply request filters to the query.
     *
     * @param Builder<\App\Models\Building> $query
     * @param Request $request
     * @return Builder<\App\Models\Building>
     */
    private function applyFilters(Builder $query, Request $request): Builder
    {
        $filters = [
            'id',
            'name',
            'building_type_id',
        ];

        foreach ($filters as $filter) {
            $value = $request->input($filter);

            if ($request->filled($filter)) {
                if ($filter === 'building_type_id' || $filter === 'id') {
                    $query->where($filter, $value);
                } else {
                    $query->where($filter, 'like', "%{$value}%");
                }
            }
        }

        return $query;
    }

    public function create(): JsonResponse
    {
        $buildingTypes = BuildingType::select('id', 'name')->orderBy('name')->get();

        return $this->success([
            'building_types' => $buildingTypes,
        ], 'Données du formulaire récupérées avec succès.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'building_type_id' => 'required|exists:building_types,id',
        ]);

        $building = Building::create($validated);

        return $this->success(
            new BuildingResource($building->load(['type'])),
            'Bâtiment créé avec succès.',
            201
        );
    }

    public function edit(int $id): JsonResponse
    {
        $building = Building::with(['type'])->find($id);

        if (!$building) {
            return $this->notFound('Bâtiment introuvable.');
        }

        return $this->success(
            new BuildingResource($building),
            'Bâtiment récupéré avec succès.'
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $building = Building::find($id);

        if (!$building) {
            return $this->notFound('Bâtiment introuvable.');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'nullable|string|max:255',
            'building_type_id' => 'sometimes|required|exists:building_types,id',
        ]);

        $building->update($validated);

        return $this->success(
            new BuildingResource($building->load(['type'])),
            'Bâtiment mis à jour avec succès.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $building = Building::find($id);

        if (!$building) {
            return $this->notFound('Bâtiment introuvable.');
        }

        $building->delete();

        return $this->success(
            null,
            'Bâtiment supprimé avec succès.'
        );
    }
}
