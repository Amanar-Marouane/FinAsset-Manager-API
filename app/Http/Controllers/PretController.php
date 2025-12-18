<?php

namespace App\Http\Controllers;

use App\Helpers\PaginatorParam;
use App\Http\Resources\PretResource;
use App\Models\Pret;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Unk\LaravelApiResponse\Traits\{HttpResponse, HttpResponseWithDataTables};

class PretController extends Controller
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

        $baseQuery = Pret::query();
        $recordsTotal = $baseQuery->count();
        $filteredQuery = $this->applyFilters(clone $baseQuery, $request);
        $recordsFiltered = (clone $filteredQuery)->count();

        $data = (clone $filteredQuery)
            ->orderBy($sortBy, $sortDir)
            ->skip($start)
            ->take($length)
            ->get();

        return $this->successDataTable(
            PretResource::collection($data),
            draw: $draw,
            start: $start,
            length: $length,
            message: 'Prêts chargés avec succès.',
            code: 200,
            recordsTotal: $recordsTotal,
            recordsFiltered: $recordsFiltered
        );
    }

    public function create(): JsonResponse
    {
        return $this->success(null, 'Prêt à créer un prêt.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'montant' => 'required|numeric|min:0',
            'monthly_payment' => 'nullable|numeric|min:0',
        ]);

        $pret = Pret::create($validated);

        return $this->success(
            new PretResource($pret),
            'Prêt créé avec succès.',
            201
        );
    }

    public function edit(int $id): JsonResponse
    {
        $pret = Pret::find($id);

        if (!$pret) {
            return $this->notFound('Prêt introuvable.');
        }

        return $this->success(
            new PretResource($pret),
            'Prêt récupéré avec succès.'
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $pret = Pret::find($id);

        if (!$pret) {
            return $this->notFound('Prêt introuvable.');
        }

        $validated = $request->validate([
            'montant' => 'sometimes|required|numeric|min:0',
            'monthly_payment' => 'nullable|numeric|min:0',
        ]);

        $pret->update($validated);

        return $this->success(
            new PretResource($pret),
            'Prêt mis à jour avec succès.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $pret = Pret::find($id);

        if (!$pret) {
            return $this->notFound('Prêt introuvable.');
        }

        $pret->delete();

        return $this->success(null, 'Prêt supprimé avec succès.');
    }

    private function applyFilters(Builder $query, Request $request): Builder
    {
        if ($request->filled('id')) {
            $query->where('id', $request->input('id'));
        }

        return $query;
    }
}
