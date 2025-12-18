<?php

namespace App\Http\Controllers;

use App\Helpers\PaginatorParam;
use App\Http\Resources\BankAccountResource;
use App\Models\Bank;
use App\Models\BankAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Unk\LaravelApiResponse\Traits\{HttpResponse, HttpResponseWithDataTables};
use Illuminate\Database\Eloquent\Builder;

class BankAccountController extends Controller
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

        /** @var \Illuminate\Database\Eloquent\Builder<BankAccount> $baseQuery */
        $baseQuery = BankAccount::query();

        $recordsTotal = $baseQuery->count();
        $filteredQuery = $this->applyFilters(clone $baseQuery, $request);
        $recordsFiltered = (clone $filteredQuery)->count();

        $data = (clone $filteredQuery)
            ->orderBy($sortBy, $sortDir)
            ->skip($start)
            ->take($length)
            ->with(['bank:id,name'])
            ->get();

        return $this->successDataTable(
            BankAccountResource::collection($data),
            draw: $draw,
            start: $start,
            length: $length,
            message: 'Comptes bancaires chargés avec succès.',
            code: 200,
            recordsTotal: $recordsTotal,
            recordsFiltered: $recordsFiltered
        );
    }

    public function create(): JsonResponse
    {
        $banks = Bank::select('id', 'name')->orderBy('name')->get();

        return $this->success([
            'banks' => $banks,
        ], 'Données du formulaire récupérées avec succès.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'account_number' => 'required|string|max:255',
            'currency' => 'sometimes|string|max:3',
            'initial_balance' => 'sometimes|numeric|min:0',
        ]);

        // Set defaults for optional fields
        $validated['currency'] = $validated['currency'] ?? 'MAD';
        $validated['initial_balance'] = $validated['initial_balance'] ?? 0;

        $account = BankAccount::create($validated);

        return $this->success(
            new BankAccountResource($account->load(['bank'])),
            'Compte bancaire créé avec succès.',
            201
        );
    }

    public function edit(int $id): JsonResponse
    {
        $account = BankAccount::with(['bank'])->find($id);

        if (!$account) {
            return $this->notFound('Compte bancaire introuvable.');
        }

        return $this->success(
            new BankAccountResource($account),
            'Compte bancaire récupéré avec succès.'
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $account = BankAccount::find($id);

        if (!$account) {
            return $this->notFound('Compte bancaire introuvable.');
        }

        $validated = $request->validate([
            'bank_id' => 'sometimes|required|exists:banks,id',
            'account_number' => 'sometimes|required|string|max:255',
            'currency' => 'sometimes|string|max:3',
            'initial_balance' => 'sometimes|numeric|min:0',
        ]);

        $account->update($validated);

        return $this->success(
            new BankAccountResource($account->load(['bank'])),
            'Compte bancaire mis à jour avec succès.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $account = BankAccount::find($id);

        if (!$account) {
            return $this->notFound('Compte bancaire introuvable.');
        }

        $account->delete();

        return $this->success(null, 'Compte bancaire supprimé avec succès.');
    }

    private function applyFilters(Builder $query, Request $request): Builder
    {
        $filters = ['id', 'account_number', 'bank_id', 'currency'];

        foreach ($filters as $filter) {
            $value = $request->input($filter);

            if ($request->filled($filter)) {
                if (in_array($filter, ['id', 'bank_id'])) {
                    $query->where($filter, $value);
                } else {
                    $query->where($filter, 'like', "%{$value}%");
                }
            }
        }

        return $query;
    }
}
