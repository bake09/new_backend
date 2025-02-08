<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupedPermissionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $permissions = [];

        // Schleife durch alle Rollen und Berechtigungen
        foreach ($this as $role) {
            foreach ($role->permissions as $permission) {
                $category = $this->getCategory($permission->name);

                // Kategorie initialisieren, falls noch nicht vorhanden
                if (!isset($permissions[$category])) {
                    $permissions[$category] = [];
                }

                // Überprüfen, ob Berechtigung schon existiert
                $existing = array_filter($permissions[$category], fn($perm) => $perm['id'] === $permission->id);

                if (!$existing) {
                    $permissions[$category][] = [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'assignedRoleIds' => [$role->id],
                    ];
                } else {
                    // Role-ID hinzufügen, falls noch nicht vorhanden
                    $index = array_search($permission->id, array_column($permissions[$category], 'id'));
                    if ($index !== false && !in_array($role->id, $permissions[$category][$index]['assignedRoleIds'])) {
                        $permissions[$category][$index]['assignedRoleIds'][] = $role->id;
                    }
                }
            }
        }

        // Ausgabe formatieren
        return [
            'todo' => $permissions['todo'] ?? []
        ];
    }

    /**
     * Bestimmt die Kategorie einer Berechtigung basierend auf ihrem Namen.
     */
    private function getCategory(string $permissionName): string
    {
        // Kategorienlogik (anpassbar)
        if (str_contains($permissionName, 'todo')) {
            return 'todo';
        }

        return 'other';
    }
}
