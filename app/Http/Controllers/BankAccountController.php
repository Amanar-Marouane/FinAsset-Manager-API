<?php

namespace App\Http\Controllers;

use App\Helpers\PaginatorParam;
use App\Http\Resources\{BankAccountResource, BankAccountYearlySummaryResource};
use App\Models\Bank;
use App\Models\BankAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Unk\LaravelApiResponse\Traits\{HttpResponse, HttpResponseWithDataTables};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

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

    public function all(): JsonResponse
    {
        $accounts = BankAccount::with(['bank', 'balances'])->get();

        return $this->success(
            BankAccountResource::collection($accounts),
            'Tous les comptes bancaires récupérés avec succès.'
        );
    }

    public function yearlySummary(int $year): JsonResponse
    {
        try {
            $accounts = BankAccount::with(['bank', 'balances' => function ($query) use ($year) {
                $query->where('year', $year);
            }])->get();

            // Explicitly fetch last balance from previous year
            $accounts->each(function ($account) use ($year) {
                $account->previous_year_last_balance = $account->balances()
                    ->where('year', $year - 1)
                    ->orderBy('date', 'desc')
                    ->first();
            });

            return $this->success(
                BankAccountYearlySummaryResource::collection($accounts),
                "Résumé annuel des comptes bancaires pour l'année {$year} récupéré avec succès."
            );
        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération du résumé annuel des comptes bancaires pour l'année {$year}: " . $e->getMessage());
            return $this->error('Une erreur est survenue lors de la récupération des données.', 500);
        }
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
            'account_number' => 'nullable|string|max:255',
            'currency' => 'sometimes|string|max:3',
            'account_name' => 'required|string|max:255',
        ]);

        // Set defaults for optional fields
        $validated['currency'] = $validated['currency'] ?? 'MAD';
        if (!isset($validated['account_number'])) {
            $validated['account_number'] = '';
        }

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
            'account_name' => 'sometimes|required|string|max:255',
            'currency' => 'sometimes|string|max:3',
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

        if ($account->balances()->exists()) {
            return $this->error('Impossible de supprimer un compte bancaire ayant des mouvements.', 422);
        }

        $account->delete();

        return $this->success(null, 'Compte bancaire supprimé avec succès.');
    }

    private function applyFilters(Builder $query, Request $request): Builder
    {
        $filters = ['id', 'account_number', 'bank_id', 'currency', 'account_name'];

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
