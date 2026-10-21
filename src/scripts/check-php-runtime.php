<?php

declare(strict_types=1);

function isSupportedPhpRuntime(string $version): bool
{
    return preg_match('/^8\.3\.(\d+)$/D', $version, $matches) === 1
        && (int) $matches[1] >= 36;
}

// Permit dependency-free policy tests without checking the test runner's runtime.
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') !== __FILE__) {
    return;
}

if (PHP_SAPI !== 'cli' || ! isSupportedPhpRuntime(PHP_VERSION)) {
    fwrite(STDERR, 'Unsupported PHP runtime: '.PHP_VERSION.'. Require stable PHP 8.3.36 or newer within 8.3.'.PHP_EOL);
    exit(1);
}

$process = proc_open(
    ['php-fpm', '-v'],
    [0 => ['file', '/dev/null', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
    $pipes,
);

if (! is_resource($process)) {
    fwrite(STDERR, 'Unable to inspect the PHP-FPM binary.'.PHP_EOL);
    exit(1);
}

$output = stream_get_contents($pipes[1]);
stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
$status = proc_close($process);

if ($status !== 0
    || preg_match('/^PHP (\S+) \(fpm-fcgi\)/m', $output, $matches) !== 1
    || ! isSupportedPhpRuntime($matches[1])
    || $matches[1] !== PHP_VERSION) {
    fwrite(STDERR, 'PHP-FPM must match the patched PHP CLI runtime.'.PHP_EOL);
    exit(1);
}

fwrite(STDOUT, 'Verified PHP CLI and FPM '.PHP_VERSION.' (security minimum: 8.3.36).'.PHP_EOL);
