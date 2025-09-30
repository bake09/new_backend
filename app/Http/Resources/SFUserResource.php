<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SFUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'sf_id' => $this->sf_id,
            'sf_login' => $this->sf_login,
            'firstname' => $this->firstname,
            'surname' => $this->surname,
            'email' => $this->email,
        ];
    }
}
