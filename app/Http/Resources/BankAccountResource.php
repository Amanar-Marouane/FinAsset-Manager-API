<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lastInsertedBalanceObject = $this->last_inserted_balance;
        return [
            'id' => $this->id,
            'bank' => $this->bank ? [
                'id' => $this->bank->id,
                'name' => $this->bank->name,
            ] : null,
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'last_inserted_balance' => $lastInsertedBalanceObject ? $lastInsertedBalanceObject->amount : null,
            'last_inserted_balance_date' => $lastInsertedBalanceObject ? $lastInsertedBalanceObject->date->format('Y-m') : null,
            'currency' => $this->currency,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
