<?php

use AdaiasMagdiel\Erlenmeyer\App;
use App\Controllers\ApiController;

require_once __DIR__ . '/bootstrap.php';

$app = new App();

// --- Api ----------
$app->get('/api/season', [ApiController::class, 'season']);
$app->get('/api/year/[year]', [ApiController::class, 'year']);

$app->get('/api/season/analytics', [ApiController::class, 'seasonAnalytics']);
$app->get('/api/year/[year]/analytics', [ApiController::class, 'yearAnalytics']);

$app->run();
