<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaginationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'page' => $this->current_page,
            'per_page' => $this->per_page,
            'total' => $this->total,
            'last_page' => $this->last_page,
            // ... weitere Felder nach Wunsch
            'links' => $this->links,
            // usw.
        ];
    }
}
