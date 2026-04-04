<?php

namespace App\Support\Http;

class PublicSiteUrl
{
    public static function sanitize(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        if ($normalized === '') {
            return null;
        }

        $normalized = preg_replace('/[\x00-\x1F\x7F]+/u', '', $normalized) ?? '';

        if ($normalized === '' || str_starts_with($normalized, '//')) {
            return null;
        }

        if (str_starts_with($normalized, '/') || str_starts_with($normalized, '#') || str_starts_with($normalized, '?')) {
            return $normalized;
        }

        if (! preg_match('/^([a-z][a-z0-9+\-.]*):/i', $normalized, $matches)) {
            return null;
        }

        $scheme = strtolower($matches[1]);

        return match ($scheme) {
            'http', 'https' => filter_var($normalized, FILTER_VALIDATE_URL) ? $normalized : null,
            'mailto' => self::sanitizeMailto($normalized),
            'tel' => self::sanitizeTelephone($normalized),
            default => null,
        };
    }

    private static function sanitizeMailto(string $value): ?string
    {
        $address = trim(substr($value, strlen('mailto:')));

        if ($address === '') {
            return null;
        }

        [$recipient] = explode('?', $address, 2);

        return filter_var($recipient, FILTER_VALIDATE_EMAIL) ? $value : null;
    }

    private static function sanitizeTelephone(string $value): ?string
    {
        $number = trim(substr($value, strlen('tel:')));

        if ($number === '') {
            return null;
        }

        $sanitized = preg_replace('/[^0-9+\-().\s]/', '', $number) ?? '';

        if ($sanitized === '' || ! preg_match('/\d/', $sanitized)) {
            return null;
        }

        return 'tel:'.$sanitized;
    }
}
