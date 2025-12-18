<?php

namespace App\Http\Controllers;

use App\Helpers\PaginatorParam;
use App\Http\Resources\AccountBalanceResource;
use App\Models\AccountBalance;
use App\Models\BankAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Unk\LaravelApiResponse\Traits\{HttpResponse, HttpResponseWithDataTables};
use Illuminate\Database\Eloquent\Builder;

class AccountBalanceController extends Controller
{
    use HttpResponse, HttpResponseWithDataTables;

    public function index(Request $request): JsonResponse
    {
        $accountId = $request->input('account_id');

        if (!$accountId) {
            return $this->error('Le paramètre account_id est requis.', 400);
        }

        $account = BankAccount::find($accountId);
        if (!$account) {
            return $this->notFound('Compte bancaire introuvable.');
        }

        [
            'start' => $start,
            'length' => $length,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'draw' => $draw
        ] = PaginatorParam::getNormalizedParams($request);

        // Get all balances for this account, filtering by year/month to ensure one per month
        $baseQuery = AccountBalance::where('bank_account_id', $accountId);
        $recordsTotal = $baseQuery->count();

        $filteredQuery = $this->applyFilters(clone $baseQuery, $request);
        $recordsFiltered = (clone $filteredQuery)->count();

        $data = (clone $filteredQuery)
            ->orderBy($sortBy ?? 'date', $sortDir ?? 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        return $this->successDataTable(
            AccountBalanceResource::collection($data),
            draw: $draw,
            start: $start,
            length: $length,
            message: 'Soldes du compte chargés avec succès.',
            code: 200,
            recordsTotal: $recordsTotal,
            recordsFiltered: $recordsFiltered
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
        ]);

        // Extract year and month from date
        $date = \Carbon\Carbon::parse($validated['date']);
        $year = $date->year;
        $month = $date->month;

        // Check if a balance already exists for this account in this month
        $existingBalance = AccountBalance::where('bank_account_id', $validated['bank_account_id'])
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if ($existingBalance) {
            // Override the existing balance
            $existingBalance->update([
                'date' => $validated['date'],
                'amount' => $validated['amount'],
            ]);

            return $this->success(
                new AccountBalanceResource($existingBalance),
                'Solde mis à jour avec succès.',
                200
            );
        }

        // Create new balance if none exists
        $balance = AccountBalance::create($validated);

        return $this->success(
            new AccountBalanceResource($balance),
            'Solde enregistré avec succès.',
            201
        );
    }

    public function edit(int $id): JsonResponse
    {
        $balance = AccountBalance::find($id);

        if (!$balance) {
            return $this->notFound('Enregistrement de solde introuvable.');
        }

        return $this->success(
            new AccountBalanceResource($balance),
            'Enregistrement de solde récupéré avec succès.'
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $balance = AccountBalance::find($id);

        if (!$balance) {
            return $this->notFound('Enregistrement de solde introuvable.');
        }

        $validated = $request->validate([
            'date' => 'sometimes|required|date',
            'amount' => 'sometimes|required|numeric|min:0',
        ]);

        // If date is being updated, check for conflicts
        if ($request->filled('date')) {
            $date = \Carbon\Carbon::parse($validated['date']);
            $year = $date->year;
            $month = $date->month;

            // Check if another balance exists for this month (excluding current record)
            $conflictingBalance = AccountBalance::where('bank_account_id', $balance->bank_account_id)
                ->where('year', $year)
                ->where('month', $month)
                ->where('id', '!=', $id)
                ->first();

            if ($conflictingBalance) {
                // Override the conflicting balance and delete current one
                $conflictingBalance->update([
                    'date' => $validated['date'] ?? $conflictingBalance->date,
                    'amount' => $validated['amount'] ?? $balance->amount,
                ]);

                $balance->delete();

                return $this->success(
                    new AccountBalanceResource($conflictingBalance),
                    'Solde mis à jour avec succès (enregistrements fusionnés).',
                    200
                );
            }
        }

        $balance->update($validated);

        return $this->success(
            new AccountBalanceResource($balance),
            'Solde mis à jour avec succès.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $balance = AccountBalance::find($id);

        if (!$balance) {
            return $this->notFound('Enregistrement de solde introuvable.');
        }

        $balance->delete();

        return $this->success(null, 'Enregistrement de solde supprimé avec succès.');
    }

    private function applyFilters(Builder $query, Request $request): Builder
    {
        if ($request->filled('year')) {
            $query->where('year', $request->input('year'));
        }

        if ($request->filled('month')) {
            $query->where('month', $request->input('month'));
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->input('date_to'));
        }

        return $query;
    }
}
