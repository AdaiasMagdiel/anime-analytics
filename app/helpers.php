<?php

define('ROOT_DIR', dirname(__DIR__));

if (!function_exists('isDev')) {
    function isDev(): bool
    {
        $whitelist = ['127.0.0.1', '::1'];
        return in_array($_SERVER['REMOTE_ADDR'], $whitelist) || $_SERVER['HTTP_HOST'] ?? '' === 'localhost:5013';
    }
}

if (!function_exists('dev_log')) {
    function dev_log(mixed $data)
    {
        if (isDev()) {
            $message = is_string($data) ? $data : json_encode($data, JSON_PRETTY_PRINT);
            error_log($message);
        }
    }
}
