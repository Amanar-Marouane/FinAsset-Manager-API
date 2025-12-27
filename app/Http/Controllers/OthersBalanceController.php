<?php

namespace App\Http\Controllers;

use App\Helpers\PaginatorParam;
use App\Http\Resources\OthersBalanceResource;
use App\Models\OthersBalance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Unk\LaravelApiResponse\Traits\HttpResponse;
use Unk\LaravelApiResponse\Traits\HttpResponseWithDataTables;

class OthersBalanceController extends Controller
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

        // Get all balances for this account, filtering by year/month to ensure one per month
        $baseQuery = OthersBalance::query();
        $recordsTotal = $baseQuery->count();

        $filteredQuery = $this->applyFilters(clone $baseQuery, $request);
        $recordsFiltered = (clone $filteredQuery)->count();

        $data = (clone $filteredQuery)
            ->orderBy($sortBy ?? 'date', $sortDir ?? 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        return $this->successDataTable(
            OthersBalanceResource::collection($data),
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
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
        ]);

        $balance = OthersBalance::where('year', \Carbon\Carbon::parse($validated['date'])->year)
            ->where('month', \Carbon\Carbon::parse($validated['date'])->month)
            ->first();

        if (!$balance) {
            $balance = new OthersBalance([
                'date' => $validated['date'],
                'amount' => $validated['amount'],
            ]);
        } else {
            $balance->amount = $validated['amount'];
        }

        $balance->save();

        return $this->success(
            new OthersBalanceResource($balance),
            'Montant des fonds d\'autrui mis à jour avec succès.'
        );
    }

    public function yearlySummary(int $year): JsonResponse
    {
        try {
            /** @var \Illuminate\Database\Eloquent\Collection|OthersBalance[] $balances */
            $balances = OthersBalance::where('year', $year)->get();

            // Explicitly fetch last balance from previous year
            $balances->each(function ($account) use ($year) {
                $account->previous_year_last_balance = $account
                    ->where('year', $year - 1)
                    ->orderBy('date', 'desc')
                    ->first();
            });

            return $this->success(
                OthersBalanceResource::collection($balances),
                "Résumé annuel des fonds d'autrui pour l'année {$year} récupéré avec succès."
            );
        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération du résumé annuel des fonds d'autrui pour l'année {$year}: " . $e->getMessage());
            return $this->error('Une erreur est survenue lors de la récupération des données.', 500);
        }
    }

    private function applyFilters(Builder $query, Request $request): Builder
    {
        // explicitly order by year from newest to oldest, then by month
        $query->orderBy('year', 'desc')->orderBy('month', 'desc');

        // Apply filters if provided
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
