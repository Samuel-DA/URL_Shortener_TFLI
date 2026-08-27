<?php
declare(strict_types=1);

/**
 * Handler for creating a shortened URL (POST /urls or POST /),Validates user input, calls the UrlShortener, and renders either
 * validation errors on the home view or the result page.
 */

$errors = [];
$longUrl = trim((string) ($_POST['url'] ?? ''));
$expiresAtInput = trim((string) ($_POST['expires_at'] ?? ''));

if($longUrl === ''){
    $errors[] ='A URL is required';
}elseif (!filter_var($longUrl, FILTER_VALIDATE_URL)){
    $errors[]= 'Please enter a valid URL';
}

$expiresAt = null;
if($expiresAtInput !== ''){
    $parsed = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $expiresAtInput);

    if ($parsed === false){
        $errors[] = 'Please provide a valid expiry date and time';
    } elseif ($parsed < new DateTimeImmutable('now')){

        // Not required by spec, but creating an already expired link has no 
        // legitimate use case, so we reject it at submission time.
        $errors[] = 'Expiry date must be in the future';
    } else {
        $expiresAt = $parsed->format('Y-m-d H:i:s');
    }
}

if (!empty($errors)){
    require __DIR__ . '/../../views/home.php';
    return;
}


$code = $shortener->shorten($longUrl, $expiresAt);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$shortUrl = "{$scheme}://{$host}/urls/{$code}";

require __DIR__ . '/../../views/result.php';