<?php

use AdaiasMagdiel\Erlenmeyer\App;
use AdaiasMagdiel\Erlenmeyer\Request;
use AdaiasMagdiel\Erlenmeyer\Response;
use App\Controllers\ApiController;

require_once __DIR__ . '/bootstrap.php';

$app = new App();

// --- Cors in development mode
$app->addMiddleware(function (Request $req, Response $res, callable $next, stdClass $params) {
	if (isDev()) {
		$res = $res->setCORS([
			"origin" => "http://localhost:8080",
			"methods" => "GET,OPTIONS",
			"headers" => "Content-Type,Accept",
			"credentials" => true
		]);

		if ($req->getMethod() === 'OPTIONS') {
			return $res->setStatusCode(204);
		}
	}

	return $next($req, $res, $params);
});

// --- Api ----------
$app->get('/api/season', [ApiController::class, 'season']);
$app->get('/api/year/[year]', [ApiController::class, 'year']);

$app->get('/api/season/analytics', [ApiController::class, 'seasonAnalytics']);
$app->get('/api/year/[year]/analytics', [ApiController::class, 'yearAnalytics']);

$app->run();
