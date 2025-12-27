<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PretResource extends JsonResource
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
            'organization' => $this->organization,
            'montant' => $this->montant,
            'montant_net' => $this->montant_net,
            'monthly_payment' => $this->monthly_payment,
            'entries' => PretEntryResource::collection($this->whenLoaded('entries')),
            'entries_total_before_current_year' => $this->entries_total_before_current_year ?? null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
