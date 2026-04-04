<?php

namespace App\Support\Sites;

class SitePageLayoutModes
{
    public const STRUCTURED = 'structured';

    public const CUSTOM_MAIN = 'custom-main';

    public const CUSTOM_FULL = 'custom-full';

    public const LEGACY_CUSTOM = 'custom';

    /**
     * @return list<string>
     */
    public static function validationModes(): array
    {
        return [
            self::STRUCTURED,
            self::CUSTOM_MAIN,
            self::CUSTOM_FULL,
            self::LEGACY_CUSTOM,
        ];
    }

    public static function normalize(?string $mode): string
    {
        return match ((string) $mode) {
            self::STRUCTURED, self::CUSTOM_MAIN, self::CUSTOM_FULL => (string) $mode,
            self::LEGACY_CUSTOM => self::CUSTOM_MAIN,
            default => self::STRUCTURED,
        };
    }

    public static function usesCustomMain(?string $mode): bool
    {
        return self::normalize($mode) === self::CUSTOM_MAIN;
    }

    public static function usesCustomFull(?string $mode): bool
    {
        return self::normalize($mode) === self::CUSTOM_FULL;
    }

    public static function usesCustomCode(?string $mode): bool
    {
        return in_array(self::normalize($mode), [self::CUSTOM_MAIN, self::CUSTOM_FULL], true);
    }
}
