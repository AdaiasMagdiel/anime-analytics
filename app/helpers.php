<?php

define('ROOT_DIR', dirname(__DIR__));

if (!function_exists('isDev')) {
    function isDev(): bool
    {
        $whitelist = ['127.0.0.1', '::1'];
        return in_array($_SERVER['REMOTE_ADDR'], $whitelist) || $_SERVER['HTTP_HOST'] ?? '' === 'localhost:5013';
    }
}
