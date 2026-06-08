<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$envFile = $root.'/.env';

if (! is_readable($envFile)) {
    fwrite(STDERR, "Missing .env\n");
    exit(1);
}

$map = [
    'AI_PRESCAN_ENABLED' => getenv('AI_PRESCAN_ENABLED') ?: null,
    'AI_VISION_PROVIDER' => getenv('AI_VISION_PROVIDER') ?: null,
    'GOOGLE_AI_API_KEY' => getenv('GOOGLE_AI_API_KEY') ?: null,
    'OPENAI_API_KEY' => getenv('OPENAI_API_KEY') ?: null,
    'GOOGLE_AI_MODEL' => getenv('GOOGLE_AI_MODEL') ?: null,
    'OPENAI_VISION_MODEL' => getenv('OPENAI_VISION_MODEL') ?: null,
];

$map = array_filter($map, fn ($value) => $value !== null && $value !== '');

if ($map === []) {
    exit(0);
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES);
if ($lines === false) {
    exit(1);
}

$seen = array_fill_keys(array_keys($map), false);

foreach ($lines as $i => $line) {
    foreach ($map as $key => $value) {
        if (str_starts_with($line, $key.'=')) {
            $lines[$i] = $key.'='.escapeEnvValue($value);
            $seen[$key] = true;
        }
    }
}

foreach ($map as $key => $value) {
    if (! $seen[$key]) {
        $lines[] = $key.'='.escapeEnvValue($value);
    }
}

file_put_contents($envFile, implode(PHP_EOL, $lines).PHP_EOL);

function escapeEnvValue(string $value): string
{
    if ($value === '' || preg_match('/[\s#="\']/', $value)) {
        return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
    }

    return $value;
}
