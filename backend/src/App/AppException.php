<?php

namespace App;

use Exception;

class AppException extends Exception
{

    const CONFIG_ALREADY_EXISTS             = "App config already exists";
    const CONFIG_NOT_CREATED                = "App config does not exist yet";
    const REQUEST_METHOD_INVALID            = "Request method is not supported or invalid";
    const REQUEST_ENDPOINT_INVALID          = "Requested enpoint does not exist";
    const RESPONSE_BODY_TYPE_UNSUPPORTED    = "Response body type unsupported";
    const ENDPOINT_NOT_FOUND                = "Endpoint is not found or does not exist";
    const UNKNOWN_ERROR                     = "An unknown error have occured";
    

    const LOG_EXCLUDE = [
        self::ENDPOINT_NOT_FOUND
    ];

    private $errorMsg;

    public function __construct($message, $code = 0, Throwable $previous = null)
    {

        $this->errorMsg = $message;

        $cfg = Config::get();
        if (!in_array($message, self::LOG_EXCLUDE) && (!file_exists($cfg->getLogFile()) || $cfg->getLogFileMaxSize() >= filesize($cfg->getLogFile()))) {
            $msg = date("Y-m-d H:i:s") . ": " . $message;   //Prepend current time to start of message
            $msg .= "\n\n";                                 //New line characters to separate logged exceptions
            error_log($msg, 3, $cfg->getLogFile());
        }
        

        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function is(string $errorMsg): bool
    {
        return $errorMsg == $this->errorMsg;
    }
}
