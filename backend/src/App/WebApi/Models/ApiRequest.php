<?php

namespace App\WebApi\Models;

use App\AppException;
use App\WebApi;
use Exception;
use stdClass;

class ApiRequest
{

    private $endpoint;

    private $requestMethod;

    private $requestHeaders = [];

    private $queryString = "";

    private $queryArray = [];

    private $requestBody;

    public function __construct(string $queryString, stdClass $body = null, string $method = "GET", array $headers = [])
    {

        /**
         * Check & set method
         */
        $method = strtoupper($method);
        if (!in_array($method, WebApi::SUPPORTED_REQUEST_METHODS)) {
            throw new AppException(AppException::REQUEST_METHOD_INVALID);
        }
        $this->requestMethod = $method;

        /**
         * Process & set queryString
         */
        $this->setQueryString($queryString);

        /**
         * Set body
         */
        $this->requestBody = $body;

        /**
         * Set headers
         */
        $this->setHeaders($headers);
    }


    public static function auto(string $queryString = ""): ApiRequest
    {
        $method = \strtoupper($_SERVER['REQUEST_METHOD']);     //Store request method
        $headers = \getallheaders();

        $body = json_decode(file_get_contents("php://input"));         //Store request body
        if (empty($body)) {
            $body = (object)$_POST;
        }

        if (empty($body)) {
            parse_str(file_get_contents("php://input"), $body);
        }

        $req = new ApiRequest($queryString, $body, $method, $headers);
        return $req;
    }

    #region GETTERS & SETTERS
    #region QUERY STRING / ENDPOINT / queryArray
    private function setQueryString($string = ""): void
    {
        $this->queryString = $string;
        $requestParts = explode("/", $string);                  //Split the request string into parts
        $this->endpoint = strtoupper($requestParts[0]);         //Store endpoint uppercased

        if (!empty($requestParts[1])) {                         //Check if extra parameters are present
            foreach ($requestParts as $key => $value) {
                if ($key < 1) {                                 //Skip endpoint part
                    continue;
                }
                $this->queryArray[] = $value;                    //Push into array of parameters
            }
        }
    }

    public function getQueryString(): string
    {
        return $this->queryString;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function getQueryArray(): array
    {
        return $this->queryArray;
    }
    #endregion


    private function setHeaders(array $headers): void
    {
        $this->requestHeaders = [];
        if (\function_exists('getallheaders')) {
            $this->requestHeaders = \getallheaders();
        }
        if ($this->requestHeaders === false) {
            $this->requestHeaders = [];
        }

        foreach ($headers as $key => $value) {
            $this->requestHeaders[$key] = $value;
        }
    }

    public function getHeaders(): array
    {
        return $this->requestHeaders;
    }


    public function getRequestBody(): stdClass
    {
        return $this->requestBody;
    }


    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }

    #endregion

}
