<?php

declare(strict_types=1);

namespace App;

use DateTimeImmutable;
use PDO;
use RuntimeException;


 //Handles core URL shortener operations.


final class UrlShortener
{
    private const CODE_LENGTH = 6;
    private const CODE_ALPHANUMERIC = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const MAX_GENERATION_ATTEMPTS = 10;

    public function __construct(
        private readonly PDO $db
    ) {}

    public function shorten(string $longUrl, ?string $expiresAt = null): string
    {
        $code = $this->generateUniqueCode();

        $statement = $this->db->prepare(
            'INSERT INTO urls (code, long_url, expires_at) VALUES (:code, :long_url, :expires_at)'
        );

        $statement->execute([
            ':code' => $code,
            ':long_url' => $longUrl,
            ':expires_at' => $expiresAt,
        ]);

        return $code;
    }

    public function resolve(string $code): ?string
    {
        $statement = $this->db->prepare(
            'SELECT long_url, expires_at FROM urls WHERE code = :code LIMIT 1'
        );
        $statement->execute([':code' => $code]);

        $record = $statement->fetch();

        if ($record === false) {
            return null;
        }

        if ($this->isExpired($record['expires_at'])) {
            return null; // Same as "not found", avoids revealing whether a code ever existed.
        }

        return (string) $record['long_url'];
    }

    private function generateUniqueCode(): string
    {
        for ($attempt = 0; $attempt < self::MAX_GENERATION_ATTEMPTS; $attempt++) {
            $code = $this->randomCode();

            if (!$this->codeExists($code)) {
                return $code;
            }
        }
        // fail loudly rather than retry forever or silently reuse a code.
        throw new RuntimeException('Unable to generate a unique short code.');
    }

    private function randomCode(): string
    {
        $alphabetLength = strlen(self::CODE_ALPHANUMERIC);
        $code = '';

        for ($index = 0; $index < self::CODE_LENGTH; $index++) {
            $code .= self::CODE_ALPHANUMERIC[random_int(0, $alphabetLength - 1)];
        }

        return $code;
    }

    private function codeExists(string $code): bool
    {
        $statement = $this->db->prepare('SELECT 1 FROM urls WHERE code = :code LIMIT 1');
        $statement->execute([':code' => $code]);

        return $statement->fetchColumn() !== false;
    }

    private function isExpired(?string $expiresAt): bool
    {
        if ($expiresAt === null) {
            return false; // No expiry set, link never expires.
        }

        $expiryDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $expiresAt)
            ?: new DateTimeImmutable($expiresAt);

        return $expiryDate < new DateTimeImmutable();
    }
}