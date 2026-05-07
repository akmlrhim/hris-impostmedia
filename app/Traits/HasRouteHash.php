<?php

namespace App\Traits;

trait HasRouteHash
{
    public function getRouteKey(): mixed
    {
        $hash = substr(md5('hris_'.static::class.'_'.$this->getKey()), 0, 8);

        return $hash.'_'.$this->getKey();
    }

    public function resolveRouteBinding($value, $field = null): ?static
    {
        $id = (int) last(explode('_', (string) $value));

        return $id > 0 ? static::query()->find($id) : null;
    }
}
