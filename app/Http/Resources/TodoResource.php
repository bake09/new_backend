<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TodoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'content' => $this->content,
            'done' => (bool) $this->done,
            // 'due_date' => $this->due_date,
            'due_date' => Carbon::parse($this->due_date )->format('Y-m-d H:i'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')), // Lade Benutzerdaten, falls geladen
        ];
    }
}
