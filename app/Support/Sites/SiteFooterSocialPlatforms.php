<?php

namespace App\Support\Sites;

use App\Support\Http\PublicSiteUrl;

class SiteFooterSocialPlatforms
{
    /**
     * @return array<string, array{label: string, placeholder: string}>
     */
    public static function definitions(): array
    {
        return [
            'instagram' => [
                'label' => 'Instagram',
                'placeholder' => 'https://instagram.com/ditbrand',
            ],
            'facebook' => [
                'label' => 'Facebook',
                'placeholder' => 'https://facebook.com/ditbrand',
            ],
            'linkedin' => [
                'label' => 'LinkedIn',
                'placeholder' => 'https://linkedin.com/company/ditbrand',
            ],
            'threads' => [
                'label' => 'Threads',
                'placeholder' => 'https://threads.net/@ditbrand',
            ],
            'tiktok' => [
                'label' => 'TikTok',
                'placeholder' => 'https://tiktok.com/@ditbrand',
            ],
            'youtube' => [
                'label' => 'YouTube',
                'placeholder' => 'https://youtube.com/@ditbrand',
            ],
        ];
    }

    /**
     * @return array{label: string, placeholder: string}
     */
    public static function definition(string $platform): array
    {
        return self::definitions()[$platform] ?? self::definitions()['instagram'];
    }

    /**
     * @return array<string, array{enabled: bool, href: ?string}>
     */
    public static function normalize(mixed $value): array
    {
        $normalized = [];

        foreach (self::definitions() as $platform => $definition) {
            $normalized[$platform] = [
                'enabled' => false,
                'href' => null,
            ];
        }

        if (! is_array($value)) {
            return $normalized;
        }

        if (array_is_list($value)) {
            foreach ($value as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $platform = self::platformKey($item['platform'] ?? null);

                if ($platform === null) {
                    continue;
                }

                $normalized[$platform] = [
                    'enabled' => true,
                    'href' => self::nullableText($item['href'] ?? null),
                ];
            }

            return $normalized;
        }

        foreach (self::definitions() as $platform => $definition) {
            $item = $value[$platform] ?? null;

            if (! is_array($item)) {
                continue;
            }

            $normalized[$platform] = [
                'enabled' => self::toBoolean($item['enabled'] ?? false),
                'href' => self::nullableText($item['href'] ?? null),
            ];
        }

        return $normalized;
    }

    /**
     * @return array<string, array{enabled: bool, href: ?string}>
     */
    public static function sanitizeForStorage(mixed $value): array
    {
        $normalized = self::normalize($value);
        $sanitized = [];

        foreach (self::definitions() as $platform => $definition) {
            $sanitized[$platform] = [
                'enabled' => (bool) ($normalized[$platform]['enabled'] ?? false),
                'href' => PublicSiteUrl::sanitize($normalized[$platform]['href'] ?? null),
            ];
        }

        return $sanitized;
    }

    private static function platformKey(mixed $value): ?string
    {
        $normalized = strtolower(trim((string) $value));

        return match ($normalized) {
            'instagram' => 'instagram',
            'facebook' => 'facebook',
            'linkedin' => 'linkedin',
            'threads', 'threads.net' => 'threads',
            'tiktok', 'tik tok' => 'tiktok',
            'youtube', 'you tube' => 'youtube',
            default => null,
        };
    }

    private static function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'on', 'yes'], true);
    }

    private static function nullableText(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }
}
