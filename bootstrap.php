<?php

use AdaiasMagdiel\Rubik\Enum\Driver;
use AdaiasMagdiel\Rubik\Rubik;

require_once __DIR__ . '/vendor/autoload.php';

Rubik::connect(Driver::SQLITE, path: __DIR__ . "/database/cache.db");

$conn = Rubik::getConn();
$conn->exec("PRAGMA journal_mode = WAL;");
$conn->exec("PRAGMA synchronous = NORMAL;");
$conn->exec("PRAGMA busy_timeout = 5000;");
$conn->exec("PRAGMA temp_store = MEMORY;");
