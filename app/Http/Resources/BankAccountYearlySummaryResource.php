<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankAccountYearlySummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lastInsertedBalanceObject = $this->last_inserted_balance;
        $previousYearLastBalance = $this->previous_year_last_balance;

        return [
            'id' => $this->id,
            'bank' => $this->bank ? [
                'id' => $this->bank->id,
                'name' => $this->bank->name,
            ] : null,
            'bank_id' => $this->bank_id,
            'balances' => AccountBalanceResource::collection($this->whenLoaded('balances')),
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'last_inserted_balance' => $lastInsertedBalanceObject ? $lastInsertedBalanceObject->amount : null,
            'last_inserted_balance_date' => $lastInsertedBalanceObject ? $lastInsertedBalanceObject->date->format('Y-m') : null,
            'previous_year_last_balance' => $previousYearLastBalance ? $previousYearLastBalance->amount : null,
            'previous_year_last_balance_date' => $previousYearLastBalance ? $previousYearLastBalance->date->format('Y-m') : null,
            'currency' => $this->currency,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
