<?php

declare(strict_types=1);

/**
 * Vercel serverless entry point.
 *
 * Static files are served from public/ while every other request is passed
 * to Laravel's regular HTTP front controller.
 */
$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$publicRoot = __DIR__.'/public';
$requestedFile = realpath($publicRoot.$uri);

if (
    $uri !== '/'
    && $requestedFile !== false
    && str_starts_with($requestedFile, realpath($publicRoot).DIRECTORY_SEPARATOR)
    && is_file($requestedFile)
) {
    $mimeType = mime_content_type($requestedFile) ?: 'application/octet-stream';

    header('Content-Type: '.$mimeType);
    header('Content-Length: '.filesize($requestedFile));
    readfile($requestedFile);
    exit;
}

require $publicRoot.'/index.php';