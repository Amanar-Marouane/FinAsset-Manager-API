<?php

namespace App\Http\Controllers;

use App\Helpers\PaginatorParam;
use App\Http\Resources\CreditResource;
use App\Models\Credit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
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

    public function all(Request $request): JsonResponse
    {
        $year = $request->input('year', now()->year);

        $credits = Credit::with(['entries' => function ($query) use ($year) {
            $query->where('year', $year);
        }])->get();

        // fetch total amount of the entries before that year
        $credits->each(function ($credit) use ($year) {
            $credit->entries_total_before_current_year = $credit->entries()
                ->where('year', '!=', $year)
                ->sum('amount');
        });

        return $this->success(
            CreditResource::collection($credits),
            'Tous les crédits chargés avec succès.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'to' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
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
            'to' => 'sometimes|required|string|max:255',
            'montant' => 'sometimes|required|numeric|min:0',
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

        if ($credit->entries()->exists()) {
            return $this->error('Impossible de supprimer un crédit qui contient des entrées.', 409);
        }

        $credit->delete();

        return $this->success(null, 'Crédit supprimé avec succès.');
    }

    private function applyFilters(Builder $query, Request $request): Builder
    {
        $filters = ['id', 'to', 'montant'];

        foreach ($filters as $filter) {
            $value = $request->input($filter);
            if ($request->filled($filter)) {
                if ($filter === 'id' || $filter === 'montant') {
                    $query->where($filter, $value);
                } else {
                    $query->where($filter, 'like', "%{$value}%");
                }
            }
        }

        return $query;
    }
}
