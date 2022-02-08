<?php

use App\AppException;
use App\WebApi;
use App\WebApi\Models\ApiRequest;
use App\WebApi\Models\ApiResponse;

require "./config.php";

try {
    try {

        $queryString = (!empty($_GET['query'])) ? $_GET['query'] : '';

        $r = ApiRequest::auto($queryString);

        $api = new WebApi($r);
        $api->process();
        $api->respond();
    } catch (AppException $e) {

        $msg = "Not implemented yet";
        if ($cfg->getDevMode()) {
            $msg .= ": $e";
        }

        if ($e->is(AppException::ENDPOINT_NOT_FOUND)) {
            WebApi::respondWith(new ApiResponse(501, $msg, "application/json"));
        } else {
            throw $e;
        }
    }
} catch (\Throwable $e) {
    $msg = "Internal Error";
    if ($cfg->getDevMode()) {
        $msg .= ": $e";
    }

    WebApi::respondWith(new ApiResponse(200, $msg, "application/json"));
}
//var_dump($api);
