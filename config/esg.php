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
     * @var array<string, array{package?: string, adopted_m2?: int, partner_since?: int, area_subtitle?: string, area_ha?: int, habitat?: array<string, int|float|null>}>
     */
    'partners' => [],

];
