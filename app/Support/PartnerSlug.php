<?php

namespace App\Support;

use Illuminate\Support\Str;

class PartnerSlug
{
    public static function fromCompany(string $company): string
    {
        return Str::slug($company);
    }

    public static function matchesCompany(string $slug, string $company): bool
    {
        return self::fromCompany($company) === $slug;
    }
}
