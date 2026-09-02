<?php
/**
 * env.php — Environment File Parser
 * Native PHP .env loader (no Composer / framework)
 */

function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\"'");

        putenv("{$key}={$value}");
        $_ENV[$key]    = $value;
        $_SERVER[$key] = $value;
    }
}

/**
 * Ambil nilai environment variable dengan fallback.
 */
function env(string $key, mixed $default = null): mixed
{
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    $lower = strtolower((string) $value);
    if ($lower === 'true'  || $lower === '(true)')  return true;
    if ($lower === 'false' || $lower === '(false)') return false;
    if ($lower === 'null'  || $lower === '(null)')  return null;
    return $value;
}
