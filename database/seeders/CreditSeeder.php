<?php

namespace Database\Seeders;

use App\Models\Credit;
use Illuminate\Database\Seeder;

class CreditSeeder extends Seeder
{
    public function run(): void
    {
        $credits = [
            [
                'to' => 'Banque Populaire',
                'montant' => '500000.00',
                'entries' => [
                    ['amount' => '25000.00', 'month' => 1, 'year' => 2025],
                    ['amount' => '25000.00', 'month' => 2, 'year' => 2025],
                    ['amount' => '25000.00', 'month' => 3, 'year' => 2025],
                ],
            ],
            [
                'to' => 'Crédit Agricole',
                'montant' => '750000.00',
                'entries' => [
                    ['amount' => '35000.00', 'month' => 1, 'year' => 2025],
                    ['amount' => '35000.00', 'month' => 2, 'year' => 2025],
                    ['amount' => '40000.00', 'month' => 3, 'year' => 2025],
                ],
            ],
            [
                'to' => 'Attijariwafa Bank',
                'montant' => '1000000.00',
                'entries' => [
                    ['amount' => '50000.00', 'month' => 1, 'year' => 2025],
                    ['amount' => '50000.00', 'month' => 2, 'year' => 2025],
                    ['amount' => '55000.00', 'month' => 3, 'year' => 2025],
                ],
            ],
        ];

        foreach ($credits as $data) {
            $entries = $data['entries'];
            unset($data['entries']);

            $credit = Credit::create($data);
            $credit->entries()->createMany($entries);
        }
    }
}
