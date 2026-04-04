<?php

namespace App\Support\Http;

class LocalRedirect
{
    public static function sanitize(?string $target): ?string
    {
        $target = trim((string) $target);

        if ($target === '' || ! str_starts_with($target, '/')) {
            return null;
        }

        if (str_starts_with($target, '//') || str_starts_with($target, '/\\')) {
            return null;
        }

        return $target;
    }
}
