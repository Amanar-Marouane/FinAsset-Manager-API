<?php

namespace App\Http\Controllers;

use App\Helpers\PaginatorParam;
use App\Http\Resources\CarResource;
use App\Models\Car;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Unk\LaravelApiResponse\Traits\{HttpResponse, HttpResponseWithDataTables};

class CarController extends Controller
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

        $baseQuery = Car::query();
        $recordsTotal = $baseQuery->count();
        $filteredQuery = $this->applyFilters(clone $baseQuery, $request);
        $recordsFiltered = (clone $filteredQuery)->count();

        $data = (clone $filteredQuery)
            ->orderBy($sortBy, $sortDir)
            ->skip($start)
            ->take($length)
            ->get();

        return $this->successDataTable(
            CarResource::collection($data),
            draw: $draw,
            start: $start,
            length: $length,
            message: 'Voitures chargées avec succès.',
            code: 200,
            recordsTotal: $recordsTotal,
            recordsFiltered: $recordsFiltered
        );
    }

    public function create(): JsonResponse
    {
        return $this->success(null, 'Prêt à créer une voiture.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'bought_at' => 'nullable|date',
            'price' => 'nullable|numeric|min:0',
        ]);

        $car = Car::create($validated);

        return $this->success(
            new CarResource($car),
            'Voiture créée avec succès.',
            201
        );
    }

    public function edit(int $id): JsonResponse
    {
        $car = Car::find($id);

        if (!$car) {
            return $this->notFound('Voiture introuvable.');
        }

        return $this->success(
            new CarResource($car),
            'Voiture récupérée avec succès.'
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $car = Car::find($id);

        if (!$car) {
            return $this->notFound('Voiture introuvable.');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'model' => 'nullable|string|max:255',
            'bought_at' => 'nullable|date',
            'price' => 'nullable|numeric|min:0',
        ]);

        $car->update($validated);

        return $this->success(
            new CarResource($car),
            'Voiture mise à jour avec succès.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $car = Car::find($id);

        if (!$car) {
            return $this->notFound('Voiture introuvable.');
        }

        $car->delete();

        return $this->success(null, 'Voiture supprimée avec succès.');
    }

    private function applyFilters(Builder $query, Request $request): Builder
    {
        $filters = ['id', 'name', 'model'];

        foreach ($filters as $filter) {
            $value = $request->input($filter);
            if ($request->filled($filter)) {
                if (in_array($filter, ['id'])) {
                    $query->where($filter, $value);
                } else {
                    $query->where($filter, 'like', "%{$value}%");
                }
            }
        }

        return $query;
    }
}
