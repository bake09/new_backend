<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllPermissionsWithRolesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'todo' => [
                'id' => $this->id,
                'name' => $this->name,
                'permissions' => $this->permissions
            ]
        ];
    }
}
