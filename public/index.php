<?php

declare(strict_types=1);

namespace App;

date_default_timezone_set('Europe/London');

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/UrlShortener.php';

$db = Database::connection();
$shortener = new UrlShortener($db);



$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$rawPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$path = rtrim($rawPath, '/') ?: '/';

// GET / and GET /urls -> show the form
if ($method === 'GET' && ($path === '/' || $path === '/urls')) {
    require __DIR__ . '/../views/home.php';
    return;
}

// POST /urls -> create a short URL
if ($method === 'POST' && $path === '/urls') {
    require __DIR__ . '/../src/handler/create.php';
    return;
}

// GET /urls/{code} -> redirect or 404
if ($method === 'GET' && preg_match('#^/urls/([a-zA-Z0-9]+)$#', $path, $matches)) {
    $code = $matches[1];
    $longUrl = $shortener->resolve($code);

    if ($longUrl === null) {
        http_response_code(404);
        require __DIR__ . '/../views/404.php';
        return;
    }

    header('Location: ' . $longUrl, true, 302);
    exit;
}

// Anything else -> 404
http_response_code(404);
require __DIR__ . '/../views/404.php';
