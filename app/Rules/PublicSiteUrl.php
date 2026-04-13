<?php

namespace App\Rules;

use App\Support\Http\PublicSiteUrl as PublicSiteUrlSanitizer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PublicSiteUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || trim((string) $value) === '') {
            return;
        }

        if (PublicSiteUrlSanitizer::sanitize($value) !== null) {
            return;
        }

        $fail('Indtast et gyldigt link med /sti, #anker, www.eksempel.dk, https://, mailto: eller tel:.');
    }
}
