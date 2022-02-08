<?php

namespace App\WebApi\Models;

use App\AppException;
use App\Utils;
use stdClass;

class ApiResponse
{
    private $code;
    private $contentType;
    private $body;

    public function __construct(int $responseCode = 200, mixed $responseBody = "", string $contentType = "application/json")
    {
        if (!isset(Utils::mimeMap[$contentType])) {
            throw new AppException("");
        }

        $this->code =           $responseCode;
        $this->contentType =    $contentType;
        $this->body =           $responseBody;
    }


    #region GETTERS

    public function getCode(): int
    {
        return $this->code;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getBody(): mixed
    {
        return $this->body;
    }

    public function getBodyString(): string
    {

        $string;

        switch(true){
            case (is_array($this->body) || $this->body instanceof stdClass):
                $string = \json_encode((array)$this->body, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
                break;
            case \is_string($this->body):
                $string = $this->body;
                break;
            case $this->body == null:
                $string = "";
                break;
            default:
                throw new AppException(AppException::RESPONSE_BODY_TYPE_UNSUPPORTED);
        }

        return $string;
    }

    #endregion
}
