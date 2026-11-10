<?php

namespace App\Models\Concerns;

trait HasCode
{
    public static function nextCode(string $prefix): string
    {
        $last = static::query()->where('code', 'like', $prefix.'%')->orderByDesc('id')->value('code');
        $n = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }
}
