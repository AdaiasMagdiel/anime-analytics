<?php

namespace App\Controllers;

use AdaiasMagdiel\Erlenmeyer\Request;
use AdaiasMagdiel\Erlenmeyer\Response;
use App\Services\JikanService;
use Exception;
use stdClass;

class ApiController
{
    public static function season(Request $req, Response $res, stdClass $params)
    {
        try {
            $year = (int) $req->getQueryParam('year', date('Y'));
            $season = $req->getQueryParam('season');

            return $res->withJson(["data" => JikanService::season($season, $year)]);
        } catch (Exception $e) {
            return $res->withError(400, $e->getMessage());
        }
    }

    public static function year(Request $req, Response $res, stdClass $params)
    {
        try {
            $year = (int) ($params->year ?? date('Y'));

            return $res->withJson(["data" => JikanService::year($year)]);
        } catch (Exception $e) {
            return $res->withError(400, $e->getMessage());
        }
    }

    public static function seasonAnalytics(Request $req, Response $res, stdClass $params)
    {
        try {
            $year = (int) $req->getQueryParam('year', date('Y'));
            $season = $req->getQueryParam('season');

            $stats = JikanService::seasonAnalytics($season, $year);

            return $res->withJson(["data" => $stats]);
        } catch (Exception $e) {
            return $res->withError(400, $e->getMessage());
        }
    }

    public static function yearAnalytics(Request $req, Response $res, stdClass $params)
    {
        try {
            $year = (int) ($params->year ?? date('Y'));

            $stats = JikanService::yearAnalytics($year);

            return $res->withJson(["data" => $stats]);
        } catch (Exception $e) {
            return $res->withError(400, $e->getMessage());
        }
    }
}
