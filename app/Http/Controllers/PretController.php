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

    public function all(Request $request): JsonResponse
    {
        $year = $request->input('year', now()->year);

        $prets = Pret::with(['entries' => function ($query) use ($year) {
            $query->where('year', $year);
        }])->get();

        // fetch total amount of the entries without that year
        $prets->each(function ($pret) use ($year) {
            $pret->entries_total_before_current_year = $pret->entries()
                ->where('year', '!=', $year)
                ->sum('amount');
        });

        return $this->success(
            PretResource::collection($prets),
            'Tous les prêts chargés avec succès.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'organization' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'montant_net' => 'required|numeric|min:0',
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
            'organization' => 'sometimes|required|string|max:255',
            'montant' => 'sometimes|required|numeric|min:0',
            'montant_net' => 'sometimes|required|numeric|min:0',
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

        if ($pret->entries()->exists()) {
            return $this->error('Impossible de supprimer un prêt qui contient des entrées.', 409);
        }

        $pret->delete();

        return $this->success(null, 'Prêt supprimé avec succès.');
    }

    private function applyFilters(Builder $query, Request $request): Builder
    {
        $filters = ['id', 'organization', 'montant', 'montant_net', 'monthly_payment'];

        foreach ($filters as $filter) {
            $value = $request->input($filter);
            if ($request->filled($filter)) {
                if ($filter === 'id' || $filter === 'montant' || $filter === 'montant_net' || $filter === 'monthly_payment') {
                    $query->where($filter, $value);
                } else {
                    $query->where($filter, 'like', "%{$value}%");
                }
            }
        }

        return $query;
    }
}
