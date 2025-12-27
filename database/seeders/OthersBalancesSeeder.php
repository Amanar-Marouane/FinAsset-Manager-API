<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\OthersBalance;

class OthersBalancesSeeder extends Seeder
{
    public function run(): void
    {
        $year = now()->year;
        $prevYear = $year - 1;

        // Previous year balances (6 months)
        $prevYearMonths = [1, 4, 7, 8, 10, 12];
        foreach ($prevYearMonths as $month) {
            $date = Carbon::create($prevYear, $month, 15);
            OthersBalance::updateOrCreate(
                ['year' => $prevYear, 'month' => $month],
                ['date' => $date, 'amount' => 2000 + ($month * 150)]
            );
        }

        // Current year balances (6 months)
        $currentYearMonths = [6, 7, 8, 9, 11, 12];
        foreach ($currentYearMonths as $month) {
            $date = Carbon::create($year, $month, 15);
            OthersBalance::updateOrCreate(
                ['year' => $year, 'month' => $month],
                ['date' => $date, 'amount' => 2500 + ($month * 200)]
            );
        }
    }
}