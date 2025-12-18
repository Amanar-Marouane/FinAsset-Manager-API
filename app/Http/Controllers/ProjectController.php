<?php

namespace App\Http\Controllers;

use App\Helpers\PaginatorParam;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Unk\LaravelApiResponse\Traits\{HttpResponse, HttpResponseWithDataTables};

class ProjectController extends Controller
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

        $baseQuery = Project::query();
        $recordsTotal = $baseQuery->count();
        $filteredQuery = $this->applyFilters(clone $baseQuery, $request);
        $recordsFiltered = (clone $filteredQuery)->count();

        $data = (clone $filteredQuery)
            ->orderBy($sortBy, $sortDir)
            ->skip($start)
            ->take($length)
            ->get();

        return $this->successDataTable(
            ProjectResource::collection($data),
            draw: $draw,
            start: $start,
            length: $length,
            message: 'Projets chargés avec succès.',
            code: 200,
            recordsTotal: $recordsTotal,
            recordsFiltered: $recordsFiltered
        );
    }

    public function create(): JsonResponse
    {
        return $this->success(null, 'Prêt à créer un projet.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capital' => 'required|numeric|min:0',
            'net' => 'required|numeric|min:0',
        ]);

        $project = Project::create($validated);

        return $this->success(
            new ProjectResource($project),
            'Projet créé avec succès.',
            201
        );
    }

    public function edit(int $id): JsonResponse
    {
        $project = Project::find($id);

        if (!$project) {
            return $this->notFound('Projet introuvable.');
        }

        return $this->success(
            new ProjectResource($project),
            'Projet récupéré avec succès.'
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $project = Project::find($id);

        if (!$project) {
            return $this->notFound('Projet introuvable.');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'capital' => 'sometimes|required|numeric|min:0',
            'net' => 'sometimes|required|numeric|min:0',
        ]);

        $project->update($validated);

        return $this->success(
            new ProjectResource($project),
            'Projet mis à jour avec succès.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $project = Project::find($id);

        if (!$project) {
            return $this->notFound('Projet introuvable.');
        }

        $project->delete();

        return $this->success(null, 'Projet supprimé avec succès.');
    }

    private function applyFilters(Builder $query, Request $request): Builder
    {
        $filters = ['id', 'name'];

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
