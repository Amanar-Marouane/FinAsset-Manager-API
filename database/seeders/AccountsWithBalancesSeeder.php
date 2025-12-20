<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Bank;
use App\Models\BankAccount;

class AccountsWithBalancesSeeder extends Seeder
{
    public function run(): void
    {
        $year = now()->year;
        $prevYear = $year - 1;

        // Ensure banks with ids 1..3
        $banks = [
            ['id' => 1, 'name' => 'Bank Alpha'],
            ['id' => 2, 'name' => 'Bank Beta'],
            ['id' => 3, 'name' => 'Bank Gamma'],
        ];
        foreach ($banks as $b) {
            Bank::updateOrCreate(['id' => $b['id']], ['name' => $b['name']]);
        }

        // Six accounts (2 per bank)
        $accountsSpec = [
            ['bank_id' => 1, 'account_name' => 'Alpha Main',    'account_number' => 'ACC-1A'],
            ['bank_id' => 1, 'account_name' => 'Alpha Savings', 'account_number' => 'ACC-1B'],
            ['bank_id' => 2, 'account_name' => 'Beta Main',     'account_number' => 'ACC-2A'],
            ['bank_id' => 2, 'account_name' => 'Beta Treasury', 'account_number' => 'ACC-2B'],
            ['bank_id' => 3, 'account_name' => 'Gamma Main',    'account_number' => 'ACC-3A'],
            ['bank_id' => 3, 'account_name' => 'Gamma Intl',    'account_number' => 'ACC-3B'],
        ];

        foreach ($accountsSpec as $i => $spec) {
            $account = BankAccount::firstOrCreate(
                ['account_number' => $spec['account_number']],
                [
                    'bank_id' => $spec['bank_id'],
                    'account_name' => $spec['account_name'],
                    'currency' => 'MAD',
                ]
            );

            // Previous year balances
            $base = 5000 + ($i * 1500);
            $dateJunPrev = Carbon::create($prevYear, 6, 30);
            $account->balances()->updateOrCreate(
                ['year' => $prevYear, 'month' => $dateJunPrev->month],
                ['date' => $dateJunPrev, 'amount' => $base + 250]
            );

            $dateDecPrev = Carbon::create($prevYear, 12, 31);
            $account->balances()->updateOrCreate(
                ['year' => $prevYear, 'month' => $dateDecPrev->month],
                ['date' => $dateDecPrev, 'amount' => $base + 500]
            );

            // Current year balances
            $dateMar = Carbon::create($year, 3, 31);
            $account->balances()->updateOrCreate(
                ['year' => $year, 'month' => $dateMar->month],
                ['date' => $dateMar, 'amount' => $base + 700]
            );

            $dateJun = Carbon::create($year, 6, 30);
            $account->balances()->updateOrCreate(
                ['year' => $year, 'month' => $dateJun->month],
                ['date' => $dateJun, 'amount' => $base + 950]
            );

            $dateSep = Carbon::create($year, 9, 30);
            $account->balances()->updateOrCreate(
                ['year' => $year, 'month' => $dateSep->month],
                ['date' => $dateSep, 'amount' => $base + 1200]
            );
        }
    }
}
