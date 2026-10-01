<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['role', 'permission'])]
class RolePermission extends Model
{
    public static function forRole(UserRole $role): array
    {
        return static::where('role', $role->value)
            ->pluck('permission')
            ->flip()
            ->map(fn () => true)
            ->all();
    }

    public static function matrix(): array
    {
        $all = static::all(['role', 'permission']);

        $matrix = [];
        foreach (UserRole::cases() as $role) {
            $matrix[$role->value] = [];
        }

        foreach ($all as $rp) {
            $matrix[$rp->role][$rp->permission] = true;
        }

        return $matrix;
    }
}
