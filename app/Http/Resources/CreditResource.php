<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreditResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'montant' => $this->montant,
            'monthly_payment' => $this->monthly_payment,
            'organization' => $this->organization,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
