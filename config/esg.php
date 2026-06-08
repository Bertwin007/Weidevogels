<?php

return [

    'brand' => 'Greidefûgels',

    'defaults' => [
        'package' => 'Wachter',
        'adopted_m2' => 2000,
        'partner_since' => null,
        'area_ha' => 12,
        'area_subtitle' => null,
    ],

    'habitat_defaults' => [
        'waterpeil_cm' => null,
        'kruidenrijk_pct' => null,
        'laat_gemaaid_pct' => null,
        'plasdras_ha' => null,
    ],

    'species_fy' => [
        'Grutto' => 'Skries',
        'Kievit' => 'Ljip',
        'Tureluur' => 'Tsjirk',
        'Scholekster' => 'Bonte wile',
        'Wulp' => 'Wylp',
        'Veldleeuwerik' => 'Ljurk',
        'Roerdomp' => 'Roerdomp',
        'Bekkasse' => 'Bekkasse',
    ],

    'richness_species_cap' => 8,

    /**
     * Optionele partner-metadata (sleutel = Str::slug(bedrijfsnaam)).
     *
     * @var array<string, array{email?: string, package?: string, adopted_m2?: int, partner_since?: int, area_name?: string, area_subtitle?: string, area_ha?: int, habitat?: array<string, int|float|null>}>
     */
    'partners' => [
        'de-friese-bakker' => [
            'email' => 'partner@friesebakker.example',
            'package' => 'Wachter',
            'adopted_m2' => 2000,
            'partner_since' => 2025,
            'area_name' => 'Gruttoland Tjerkwerd',
            'area_subtitle' => 'Botes Paradys',
            'area_ha' => 12,
            'habitat' => [
                'waterpeil_cm' => 90,
                'kruidenrijk_pct' => 62,
                'laat_gemaaid_pct' => 100,
                'plasdras_ha' => 0.8,
            ],
        ],
    ],

    'mail' => [
        'from_name' => 'Greidefûgels · ANF',
    ],

];
