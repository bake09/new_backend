<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'is_inactive' => $this->is_inactive ? true : false, // Gibt true zurück, wenn is_inactive gesetzt ist
            'team' => $this->team ? [
                'id' => $this->team->id,
                'name' => $this->team->name,
            ] : null, // Gibt null zurück, falls kein Team vorhanden ist
            'roles' => $this->roles,
            'permissions' => $this->getAllPermissions(),
        ];
    }
}
