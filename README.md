# URL Shortener

A lightweight URL shortener built with PHP, vanilla JavaScript, and SQLite3.

## Requirements

- PHP 8.2 or higher (currently supported/non-EOL branches: 8.2–8.5), with the `pdo_sqlite` extension enabled
- SQLite3 extension enabled (`php-sqlite3`)

## Setup & Running Locally

This app is tested against PHP's built-in development server.

1. Clone the repo
2. From the project root, start the server:

```
   php -S localhost:8000 -t public public/router.php
```

3. Visit http://localhost:8000

## Usage

- Go to `/` or `/urls` to shorten a URL, with an optional expiry date/time
- Visiting a short URL (`/urls/{code}`) redirects to the original URL, or returns a
  404 if the code is missing or has expired.

## Note

- The SQLite database file is created automatically on first run, no manual setup needed.
