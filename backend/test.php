<?php

use App\AppException;
use App\WebApi;
use App\WebApi\Models\ApiRequest;
use App\WebApi\Models\ApiResponse;

require "./config.php";

try {
    try {

        $body = new stdClass;
        $rnd = rand(0, 9999);
        $body->username = "user_1";
        $body->email = "user_1@gmail.com";
        $body->password = "admin1";
        $body->action = "login";

        $r = new ApiRequest("hours/2022-02-01to2022-02-28", $body, "GET", [
            'Authorization' => 'Bearer 10524373-5ec2-4d78-b9b8-92046a174eb5'
        ]);

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
