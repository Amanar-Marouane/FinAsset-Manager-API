<?php

namespace App\Http\Controllers;

use App\Models\AccountBalance;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\Building;
use App\Models\BuildingType;
use App\Models\Car;
use App\Models\Credit;
use App\Models\Pret;
use App\Models\Project;
use App\Models\Terrain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Unk\LaravelApiResponse\Traits\HttpResponse;

class DashboardController extends Controller
{
    use HttpResponse;

    public function metrics(Request $request): JsonResponse
    {
        $now = now();

        $counts = [
            'buildings' => Building::count(),
            'building_types' => BuildingType::count(),
            'banks' => Bank::count(),
            'bank_accounts' => BankAccount::count(),
            'account_balances' => AccountBalance::count(),
            'cars' => Car::count(),
            'terrains' => Terrain::count(),
            'projects' => Project::count(),
            'credits' => Credit::count(),
            'prets' => Pret::count(),
        ];

        $totals = [
            'projects_total_net' => (string) Project::sum('net'),
            'projects_total_capital' => (string) Project::sum('capital'),

            'credits_total_montant' => (string) Credit::sum('montant'),
            'credits_total_monthly_payment' => (string) Credit::sum('monthly_payment'),
            'credits_total_montant_net' => (string) Credit::sum('montant_net'),

            'prets_total_montant' => (string) Pret::sum('montant'),

            'bank_accounts_total_initial_balance' => (string) BankAccount::sum('initial_balance'),

            'cars_total_value' => (string) Car::sum('price'),

            // Current month balances (only one per month per account enforced)
            'current_month_balances_total' => (string) AccountBalance::where('year', $now->year)->where('month', $now->month)->sum('amount'),
            'current_month_balances_count' => AccountBalance::where('year', $now->year)->where('month', $now->month)->count(),

            // Loan net position (incoming - outgoing)
            'net_loans_position' => (string) (Pret::sum('montant') - Credit::sum('montant_net')),
        ];

        return $this->success([
            'counts' => $counts,
            'totals' => $totals,
        ], 'Statistiques du tableau de bord chargées avec succès.');
    }
}
