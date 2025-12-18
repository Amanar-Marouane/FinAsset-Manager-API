<?php

namespace App\Http\Controllers;

use App\Helpers\PaginatorParam;
use App\Http\Resources\BankResource;
use App\Models\Bank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Unk\LaravelApiResponse\Traits\{HttpResponse, HttpResponseWithDataTables};
use Illuminate\Database\Eloquent\Builder;

class BankController extends Controller
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

        /** @var \Illuminate\Database\Eloquent\Builder<Bank> $baseQuery */
        $baseQuery = Bank::query();

        $recordsTotal = $baseQuery->count();
        $filteredQuery = $this->applyFilters(clone $baseQuery, $request);
        $recordsFiltered = (clone $filteredQuery)->count();

        $data = (clone $filteredQuery)
            ->orderBy($sortBy, $sortDir)
            ->skip($start)
            ->take($length)
            ->withCount('accounts')
            ->get();

        return $this->successDataTable(
            BankResource::collection($data),
            draw: $draw,
            start: $start,
            length: $length,
            message: 'Banques chargées avec succès.',
            code: 200,
            recordsTotal: $recordsTotal,
            recordsFiltered: $recordsFiltered
        );
    }

    public function all(): JsonResponse
    {
        $banks = Bank::all();

        return $this->success(
            BankResource::collection($banks),
            'Toutes les banques ont été chargées avec succès.'
        );
    }

    public function create(): JsonResponse
    {
        return $this->success(null, 'Prêt à créer une banque.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:banks,name',
            'description' => 'nullable|string',
        ]);

        $bank = Bank::create($validated);

        return $this->success(
            new BankResource($bank),
            'Banque créée avec succès.',
            201
        );
    }

    public function edit(int $id): JsonResponse
    {
        $bank = Bank::withCount('accounts')->find($id);

        if (!$bank) {
            return $this->notFound('Banque introuvable.');
        }

        return $this->success(
            new BankResource($bank),
            'Banque récupérée avec succès.'
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $bank = Bank::find($id);

        if (!$bank) {
            return $this->notFound('Banque introuvable.');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:banks,name,' . $id,
            'description' => 'nullable|string',
        ]);

        $bank->update($validated);

        return $this->success(
            new BankResource($bank),
            'Banque mise à jour avec succès.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $bank = Bank::find($id);

        if (!$bank) {
            return $this->notFound('Banque introuvable.');
        }

        if ($bank->accounts()->exists()) {
            return $this->error('Impossible de supprimer une banque qui a des comptes.', 409);
        }

        $bank->delete();

        return $this->success(null, 'Banque supprimée avec succès.');
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
