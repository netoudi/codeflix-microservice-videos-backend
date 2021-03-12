<?php

declare(strict_types=1);

namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWT;

class KeycloakGuard implements Guard
{
    /**
     * @var JWT
     */
    private $jwt;

    /**
     * @var Request
     */
    private $request;

    /**
     * KeycloakGuard constructor.
     *
     * @param JWT $jwt
     * @param Request $request
     */
    public function __construct(JWT $jwt, Request $request)
    {
        $this->jwt = $jwt;
        $this->request = $request;
    }

    public function check()
    {
        // TODO: Implement check() method.
    }

    public function guest()
    {
        // TODO: Implement guest() method.
    }

    public function user()
    {
        // TODO: Implement user() method.
    }

    public function id()
    {
        // TODO: Implement id() method.
    }

    public function validate(array $credentials = [])
    {
        // TODO: Implement validate() method.
    }

    public function setUser(Authenticatable $user)
    {
        // TODO: Implement setUser() method.
    }
}
