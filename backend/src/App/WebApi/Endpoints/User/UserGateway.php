<?php

namespace App\WebApi\Endpoints\User;

use App\Config;
use App\Models\Session;
use App\Models\User;
use App\Utils;
use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager;

final class UserGateway
{

    public function __construct()
    {
    }

    public function getUserById(int $id): User|NULL
    {
        return User::where('id', $id)->first();
    }

    public function getUserByUserName(string $username): User|NULL
    {
        return User::where('username', $username)->first();
    }

    public function getUserByEmail(string $email): User|NULL
    {
        return User::where('email', $email)->first();
    }

    public function getUserByToken(string $token): User|NULL
    {
        $session = Session::where("token", $token)->first();
        if ($session == NULL) {
            return NULL;
        }
        return User::where("id", $session->user_id)->first();
    }

    public function createUser(string $username, string $email, string $password): User
    {
        $user = User::create([
            'username' => $username,
            'email' => $email,
            'password_hash' => \password_hash($password, \PASSWORD_BCRYPT)
        ]);

        return $user;
    }

    public function createSession(User $user): Session
    {

        $token = Utils::guidv4();
        $lifetime_minutes = Config::get()->getSessionLifeTimeMinutes();
        $expires_at = Carbon::now()->addMinutes($lifetime_minutes)->toDateTimeString();

        $session = $user->session()->create([
            "user_id" => $user->id,
            "lifetime_minutes" => $lifetime_minutes,
            "expires_at" => $expires_at,
            "token" => $token
        ]);

        return $session;
    }
}
