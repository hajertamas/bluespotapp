<?php

namespace App\WebApi;

use App\Models\Session;
use App\Models\User;
use App\WebApi\Models\ApiRequest;
use Carbon\Carbon;
use Exception;

class Auth
{

    private $user;
    private $session;
    private $notAuthenticatedReason = "";
    private $request;

    public function __construct(ApiRequest $request)
    {
        $this->request = $request;
        $headers = $request->getHeaders();

        try {
            if (empty($headers['Authorization'])) {
                throw new Exception('No Authorization header');
            }

            $explodedAuth = explode("Bearer ", $headers['Authorization']);

            if (empty($explodedAuth[1])) {
                throw new Exception('Invalid Authorization header');
            }

            $token = $explodedAuth[1];
            $session = Session::where('token', $token)->first();

            if ($session == NULL) {
                throw new Exception('Authorization token invalid');
            }

            if ($session->expires_at != NULL && !Carbon::parse($session->expires_at)->greaterThanOrEqualTo(Carbon::now())) {
                throw new Exception('Session expired');
            }

            $this->session = $session;
            $this->user = User::where('id', $session->user_id)->first();
        } catch (\Throwable $e) {
            $this->user = NULL;
            $this->session = NULL;
            $this->notAuthenticatedReason = $e;
        } finally {
            Session::where('expires_at', '<', Carbon::now())->whereNotNull('expires_at')->delete();
        }
    }

    public function isAuthenticated(): bool
    {
        if ($this->user == null || $this->session == null) {
            return false;
        }

        return true;
    }

    public function getNotAuthenticatedReason(): string{
        return $this->notAuthenticatedReason;
    }

    public function getUser(): User|NULL
    {
        return $this->user;
    }

    public function getSession(): Session|NULL
    {
        return $this->session;
    }
}
