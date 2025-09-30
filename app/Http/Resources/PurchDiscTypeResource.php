<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchDiscTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'Rabattcode'    => $this->DISCOUNT_CD,
            'Herstellermodellcode'  => $this->FACTORY_MODEL_CODE,
            'Erstellungsdatum'      => Carbon::parse($this->TRANSACT_DATE)->format('Y-m-d'),
            'Ersteller'         => $this->HANDLER,
            'Nachlasstext'      => $this->DISCOUNT_TEXT,
            'UNIQUE_IDENT' => $this->UNIQUE_IDENT
        ];
    }
}