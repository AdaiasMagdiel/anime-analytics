<?php

/**
 * FullCrawl Migration: createtables
 */
return [
    'up' => function (PDO $pdo) {
        $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS cache (
    cache_key   TEXT PRIMARY KEY,
    payload     TEXT NOT NULL, -- JSON encoded
    expires_at INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_cache_expires_at
ON cache (expires_at);
SQL);
    },
    'down' => function (PDO $pdo) {
        $pdo->exec("DROP TABLE IF EXISTS cache;");
    }
];
