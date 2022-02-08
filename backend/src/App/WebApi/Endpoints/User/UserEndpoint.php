<?php

namespace App\WebApi\Endpoints\User;

use App\Config;
use App\Models\User;
use App\WebApi\Auth;
use App\WebApi\Interfaces\WebApiEndpoint;
use App\WebApi\Models\ApiRequest;
use App\WebApi\Models\ApiResponse;
use Exception;

final class UserEndpoint implements WebApiEndpoint
{

    private $contentType = "text/plain";
    private $response;
    private $request;
    private $gateway;
    private $auth;

    public function __construct(ApiRequest $request)
    {
        $this->request = $request;
        $this->auth = new Auth($request);
        $this->gateway = new UserGateway;
    }

    public function process(): void
    {

        $method = $this->request->getRequestMethod();
        $response;
        switch ($method) {
            case "GET":
                $response = $this->get();
                break;
            case "POST":
                $response = $this->post();
                break;
            default:
                $response = new ApiResponse(501, "Not implemented");
                break;
        }

        $this->response = $response;
    }

    public function getResponse(): ApiResponse
    {
        if (empty($this->response)) {
            $msg = "Internal error";
            if (Config::get()->getDevMode()) {
                $msg += ": No response from" . self::class;
            }
            return new ApiResponse(500, $msg);
        }

        return $this->response;
    }

    #region GET
    private function get(): ApiResponse
    {
        $currentUser = $this->auth->getUser();

        if (!$this->auth->isAuthenticated()) {
            return new ApiResponse(401, "Must be logged in to retrieve user data");
        }

        $qArr = $this->request->getQueryArray();
        if (empty($qArr[0])) {
            return new ApiResponse(400, "Must specify parameter name to find user (token/id/name)");
        }

        if (empty($qArr[1]) && \strtoupper($qArr[0]) != "TOKEN") {
            return new ApiResponse(400, "Must specify parameter value to find user (name/john_doe)");
        }

        $user;
        switch (\strtoupper($qArr[0])) {
            case "ID":
                $user = $this->gateway->getUserById((int)$qArr[1]);
                break;
            case "NAME":
                $user = $this->gateway->getUserByUserName((string)$qArr[1]);
                break;
            case "TOKEN":
                $user = $this->gateway->getUserByToken($this->auth->getSession()->token);
                break;
            default:
                return new ApiResponse(400, "Invalid parameter name specified.");
        }

        if (empty($user)) {
            return new ApiResponse(404, "User can not be found by specified parameters");
        }

        if ($user->id != $currentUser->id) {
            return new ApiResponse(403, "You can not view other users personal data");
        }

        return new ApiResponse(200, [
            "username" => $user->username,
            "email" => $user->email
        ]);
    }
    #endregion

    #region POST
    private function post(): ApiResponse
    {
        $body = $this->request->getRequestBody();

        if (empty($body->action)) {
            return new ApiResponse(400, "Must specify \"action\" in request body (\"register\" or \"login\")");
        }

        switch (strtoupper($body->action)) {
            case 'REGISTER':
                return $this->register();
            case 'LOGIN':
                return $this->login();
            default:
                return new ApiResponse(400, "Invalid \"action\": {$body->action}, must be \"register\" or \"login\"");
        }

        return new ApiResponse(200);
    }

    private function register(): ApiResponse
    {
        $body = $this->request->getRequestBody();

        if (empty($body->username)) {
            return new ApiResponse(400, "Username is required");
        }
        if (empty($body->email)) {
            return new ApiResponse(400, "Email is required");
        }
        if (empty($body->password)) {
            return new ApiResponse(400, "Password is required");
        }

        try {
            $this->validateUsername($body->username);
            $this->validateEmail($body->email);
            $this->validatePassword($body->password);
            $user = $this->gateway->createUser(\strip_tags($body->username), $body->email, $body->password);
        } catch (Exception $e) {
            return new ApiResponse(400, $e->getMessage());
        }

        $result = [
            "id" => $user->id,
            "username" => $user->username,
            "email" => $user->email,
            "session" => (Config::get()->getDevMode()) ? "tokenvalue" : null
        ];

        return new ApiResponse(200, $result);
    }

    private function login(): ApiResponse
    {
        $body = $this->request->getRequestBody();

        if (empty($body->username) && empty($body->email)) {
            return new ApiResponse(400, "Username or Email is required");
        }

        if (empty($body->password)) {
            return new ApiResponse(400, "Password is required");
        }

        $user;
        $usingAsIdentifier;
        switch (true) {
            case !empty($body->email):
                $usingAsIdentifier = "email";
                $user = $this->gateway->getUserByEmail($body->email);
                break;
            case !empty($body->username):
                $usingAsIdentifier = "username";
                $user = $this->gateway->getUserByUserName($body->username);
                break;
        }

        if ($user == NULL || !\password_verify($body->password, $user->password_hash)) {
            return new ApiResponse(401, "Invalid $usingAsIdentifier or password");
        }

        $session = $this->gateway->createSession($user);

        return new ApiResponse(200, [
            "token" => $session->token,
            "lifetime_minutes" => $session->lifetime_minutes,
            "expires_at" => $session->expires_at,
            "created_at" => $session->created_at
        ]);
    }

    private function validatePassword($password): bool
    {
        if (\mb_strlen($password) < 6) {
            throw new Exception("Password must be longer than 6 characters");
        }

        return true;
    }

    private function validateUsername($username): bool
    {
        if (\mb_strlen($username) < 3) {
            throw new Exception("Username must be longer than 3 characters");
        }

        if (User::where('username', $username)->first() != null) {
            throw new Exception("Username is already in use");
        }

        return true;
    }

    private function validateEmail($email): bool
    {
        if (!\filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email must be a valid email address");
        }

        if (User::where('email', $email)->first() != null) {
            throw new Exception("Email is already in use");
        }

        return true;
    }

    #endregion

}
