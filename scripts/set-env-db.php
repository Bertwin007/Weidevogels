<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$envFile = $root.'/.env';

if ($argc < 4) {
    fwrite(STDERR, "Usage: php scripts/set-env-db.php DATABASE USER PASSWORD\n");
    exit(1);
}

[, $database, $username, $password] = $argv;

if (! is_readable($envFile)) {
    fwrite(STDERR, "Missing .env\n");
    exit(1);
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES);
if ($lines === false) {
    exit(1);
}

$map = [
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => '3306',
    'DB_DATABASE' => $database,
    'DB_USERNAME' => $username,
    'DB_PASSWORD' => $password,
];

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
