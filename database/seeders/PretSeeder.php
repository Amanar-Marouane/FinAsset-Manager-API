<?php

namespace Database\Seeders;

use App\Models\Pret;
use Illuminate\Database\Seeder;

class PretSeeder extends Seeder
{
    public function run(): void
    {
        $prets = [
            [
                'organization' => 'Startup Tech Alpha',
                'montant' => '300000.00',
                'montant_net' => '285000.00',
                'monthly_payment' => '15000.00',
                'entries' => [
                    ['amount' => '15000.00', 'month' => 1, 'year' => 2025],
                    ['amount' => '15000.00', 'month' => 2, 'year' => 2025],
                    ['amount' => '15000.00', 'month' => 3, 'year' => 2025],
                ],
            ],
            [
                'organization' => 'Entreprise Beta SARL',
                'montant' => '500000.00',
                'montant_net' => '475000.00',
                'monthly_payment' => '25000.00',
                'entries' => [
                    ['amount' => '25000.00', 'month' => 1, 'year' => 2025],
                    ['amount' => '25000.00', 'month' => 2, 'year' => 2025],
                    ['amount' => '25000.00', 'month' => 3, 'year' => 2025],
                ],
            ],
            [
                'organization' => 'Commerce Delta',
                'montant' => '200000.00',
                'montant_net' => '195000.00',
                'monthly_payment' => '10000.00',
                'entries' => [
                    ['amount' => '10000.00', 'month' => 1, 'year' => 2025],
                    ['amount' => '10000.00', 'month' => 2, 'year' => 2025],
                    ['amount' => '10000.00', 'month' => 3, 'year' => 2025],
                ],
            ],
        ];

        foreach ($prets as $data) {
            $entries = $data['entries'];
            unset($data['entries']);

            $pret = Pret::create($data);
            $pret->entries()->createMany($entries);
        }
    }
}
