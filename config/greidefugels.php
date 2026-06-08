<?php

return [

    'donation_url' => env(
        'ANF_DONATION_URL',
        'https://agrarischnatuurfondsfryslan.nl/donateur-worden/?utm_source=greidefugels&utm_medium=moment&utm_campaign=weidevogels',
    ),

    'ai' => [
        'enabled' => (bool) env('AI_PRESCAN_ENABLED', true),
        'provider' => env('AI_VISION_PROVIDER', 'gemini'),

        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_VISION_MODEL', 'gpt-4o-mini'),
        ],

        'gemini' => [
            'api_key' => env('GOOGLE_AI_API_KEY'),
            'model' => env('GOOGLE_AI_MODEL', 'gemini-2.0-flash'),
        ],
    ],

];
