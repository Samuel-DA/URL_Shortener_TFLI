<?php

declare(strict_types=1);

namespace App;

use PDO;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $dbPath = __DIR__ . '/../database/urls.sqlite';

            self::$connection = new PDO('sqlite:' . $dbPath);
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Enforce foreign key constraints
            self::$connection->exec('PRAGMA foreign_keys = ON;');

            self::bootstrapSchema(self::$connection);
        }

        return self::$connection;
    }

    private static function bootstrapSchema(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS urls (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                code        TEXT NOT NULL UNIQUE,
                long_url    TEXT NOT NULL,
                expires_at  TEXT NULL,
                created_at  TEXT NOT NULL DEFAULT (datetime('now'))
                
            );
        ");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_urls_code ON urls(code);");
    }
}