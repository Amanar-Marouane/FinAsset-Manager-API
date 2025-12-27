<?php

namespace App\Http\Controllers;

use App\Models\AccountBalance;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\Credit;
use App\Models\CreditEntry;
use App\Models\Pret;
use App\Models\PretEntry;
use App\Models\Project;
use App\Models\ProjectEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Unk\LaravelApiResponse\Traits\HttpResponse;

class DashboardController extends Controller
{
    use HttpResponse;

    public function metrics(Request $request): JsonResponse
    {
        $now = now();
        $currentYear = $now->year;
        $currentMonth = $now->month;

        $counts = [
            'banks' => Bank::count(),
            'bank_accounts' => BankAccount::count(),
            'account_balances' => AccountBalance::count(),
            'projects' => Project::count(),
            'project_entries' => ProjectEntry::count(),
            'credits' => Credit::count(),
            'credit_entries' => CreditEntry::count(),
            'prets' => Pret::count(),
            'pret_entries' => PretEntry::count(),
        ];

        $totals = [
            // Projects
            'projects_total_capital' => (string) Project::sum('capital'),
            'projects_entries_total' => (string) ProjectEntry::sum('amount'),
            'projects_entries_current_year' => (string) ProjectEntry::where('year', $currentYear)->sum('amount'),

            // Credits (incoming loans)
            'credits_total_montant' => (string) Credit::sum('montant'),
            'credits_entries_total' => (string) CreditEntry::sum('amount'),
            'credits_entries_current_year' => (string) CreditEntry::where('year', $currentYear)->sum('amount'),
            'credits_entries_current_month' => (string) CreditEntry::where('year', $currentYear)
                ->where('month', $currentMonth)
                ->sum('amount'),

            // Prets (outgoing loans)
            'prets_total_montant' => (string) Pret::sum('montant'),
            'prets_total_montant_net' => (string) Pret::sum('montant_net'),
            'prets_total_monthly_payment' => (string) Pret::sum('monthly_payment'),
            'prets_entries_total' => (string) PretEntry::sum('amount'),
            'prets_entries_current_year' => (string) PretEntry::where('year', $currentYear)->sum('amount'),
            'prets_entries_current_month' => (string) PretEntry::where('year', $currentYear)
                ->where('month', $currentMonth)
                ->sum('amount'),

            // Bank Accounts
            'current_month_balances_total' => (string) AccountBalance::where('year', $currentYear)
                ->where('month', $currentMonth)
                ->sum('amount'),
            'current_month_balances_count' => AccountBalance::where('year', $currentYear)
                ->where('month', $currentMonth)
                ->count(),
            'all_balances_total' => (string) AccountBalance::sum('amount'),

            // Net positions
            'net_loans_position' => (string) (
                Pret::sum('montant') - Credit::sum('montant')
            ),
            'net_loans_entries_current_year' => (string) (
                PretEntry::where('year', $currentYear)->sum('amount') -
                CreditEntry::where('year', $currentYear)->sum('amount')
            ),
        ];

        return $this->success([
            'counts' => $counts,
            'totals' => $totals,
            'current_year' => $currentYear,
            'current_month' => $currentMonth,
        ], 'Statistiques du tableau de bord chargées avec succès.');
    }
}
