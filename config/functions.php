<?php

function redirect($url = ''): void {
    $base = appBaseUrl();
    header("Location: {$base}/{$url}");
}

function appBaseUrl(): string
{
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $baseUrl = str_replace('\\', '/', dirname($scriptName));

    if ($baseUrl === '/' || $baseUrl === '.') {
        return '';
    }

    return rtrim($baseUrl, '/');
}

function normalizePath(string $path): string
{
    $normalizedPath = str_replace('\\', '/', $path);

    if ($normalizedPath === '' || $normalizedPath === '/') {
        return '/';
    }

    return rtrim($normalizedPath, '/') . '/';
}

function appCurrentPath(): string
{
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($requestUri, PHP_URL_PATH) ?: '/';

    return normalizePath($path);
}
