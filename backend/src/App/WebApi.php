<?php

namespace App;

use App\WebApi\Endpoints\Hours\HoursEndpoint;
use App\WebApi\Endpoints\User\UserEndpoint;
use App\WebApi\Models\ApiRequest;
use App\WebApi\Models\ApiResponse;

class WebApi
{

    public const SUPPORTED_REQUEST_METHODS = [
        "GET",
        "POST",
        "PATCH",
        "DEL",
        "OPTIONS"
    ];

    private $validEndpoints = [];

    private $apiRequest;

    private $endpoint;

    public function __construct(ApiRequest $request)
    {
        $this->apiRequest = $request;

        $this->sendHeaders();

        $this->setupEndpoint();
    }

    public function setupEndpoint()
    {
        switch ($this->apiRequest->getEndPoint()) {
            case "USER":
                $this->endpoint = new UserEndpoint($this->apiRequest);
                break;
            case "HOURS":
                $this->endpoint = new HoursEndpoint($this->apiRequest);
                break;
        }
    }

    public function process(): void
    {
        if ($this->endpoint == null) {
            throw new AppException(AppException::ENDPOINT_NOT_FOUND);
        }
        $this->endpoint->process();
    }

    public function respond(): void
    {
        if ($this->endpoint == null) {
            throw new AppException(AppException::ENDPOINT_NOT_FOUND);
        }
        self::respondWith($this->endpoint->getResponse());
    }

    public static function respondWith(ApiResponse $response): void
    {
        \http_response_code($response->getCode());
        header("Content-Type: " . $response->getContentType());
        echo $response->getBodyString();
    }

    private function sendHeaders(): void
    {

        #region SEND ALLOW ORIGIN HEADER
        $origin = false;
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $origin = rtrim($_SERVER['HTTP_REFERER'], "/");
        } else if (!empty($_SERVER['HTTP_ORIGIN'])) {
            $origin = rtrim($_SERVER['HTTP_ORIGIN'], "/");
        }

        if (Config::get()->getAppOrigin() == "*") {
            if ($origin === false) {
                throw new AppException(AppException::UNKNOWN_ERROR);
            }
            header("Access-Control-Allow-Origin: $origin");
        } else {
            header("Access-Control-Allow-Origin: " . Config::get()->getAppOrigin());
        }

        #endregion

        #region SEND ETC HEADERS
        header("Access-Control-Allow-Methods: " . implode(",", self::SUPPORTED_REQUEST_METHODS));
        header("Access-Control-Allow-Headers: Authorization, Origin, Referer, X-API-KEY, X-CLIENT-ID, X-PINGOTHER, X-REQUESTED-WITH, Content-Type, Accept");
        header("Access-Control-Allow-Credentials: true");
        header("Vary: Authorization");
        #endregion

        #region HANDLE OPTIONS METHOD
        if ($this->apiRequest->getRequestMethod() === "OPTIONS") {
            header("Content-Type: application/json; charset=UTF-8");
            die;
        }
        #endregion

    }
}
