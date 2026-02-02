<?php

namespace App;

use AdaiasMagdiel\Rubik\Rubik;
use PDO;
use Throwable;

/**
 * SQLite Cache Helper (Rubik-powered)
 */
class Cache
{
    public const SECOND = 1;
    public const MINUTE = 60 * self::SECOND;
    public const HOUR   = 60 * self::MINUTE;
    public const DAY    = 24 * self::HOUR;

    /**
     * Get a cached value or execute and cache it.
     *
     * @param string   $key    Unique identifier for the cache entry
     * @param callable $action Callback to produce the value on cache miss
     * @param int      $ttl    Time to live in seconds
     */
    public static function remember(string $key, callable $action, int $ttl = self::HOUR): mixed
    {
        $conn = Rubik::getConn();
        $now  = time();

        // 1) Try cache first (hit if not expired)
        $stmt = $conn->prepare("
            SELECT payload
            FROM cache
            WHERE cache_key = :key
              AND expires_at > :now
            LIMIT 1
        ");
        $stmt->execute([
            'key' => $key,
            'now' => $now,
        ]);

        $payload = $stmt->fetchColumn();

        if ($payload !== false && $payload !== null) {
            return json_decode($payload, true);
        }

        // 2) Cache miss → fetch fresh data
        $data = $action();

        if ($data === null) {
            return null;
        }

        // 3) Write-through cache (short transaction)
        $expiresAt = $now + max(0, $ttl);

        Rubik::beginTransaction();
        try {
            $stmt = $conn->prepare("
                INSERT INTO cache (cache_key, payload, expires_at)
                VALUES (:key, :payload, :expires_at)
                ON CONFLICT(cache_key) DO UPDATE SET
                    payload    = excluded.payload,
                    expires_at = excluded.expires_at
            ");

            $stmt->execute([
                'key'        => $key,
                'payload'    => json_encode($data, JSON_THROW_ON_ERROR),
                'expires_at' => $expiresAt,
            ]);

            Rubik::commit();
        } catch (Throwable $e) {
            Rubik::rollBack();
            throw $e;
        }

        return $data;
    }

    /**
     * Manually forget a cache key
     */
    public static function forget(string $key): void
    {
        $stmt = Rubik::getConn()->prepare("
            DELETE FROM cache
            WHERE cache_key = :key
        ");
        $stmt->execute(['key' => $key]);
    }

    /**
     * Purge expired cache entries
     */
    public static function purgeExpired(): void
    {
        // Using SQLite's clock avoids parameter binding here
        Rubik::getConn()->exec("
            DELETE FROM cache
            WHERE expires_at <= strftime('%s','now')
        ");
    }
}
