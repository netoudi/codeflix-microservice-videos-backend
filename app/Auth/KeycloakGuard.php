<?php

declare(strict_types=1);

namespace App\Auth;

use BadMethodCallException;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Traits\Macroable;
use Tymon\JWTAuth\JWT;

class KeycloakGuard implements Guard
{
    use GuardHelpers, Macroable {
        __call as macroCall;
    }

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

    public function user()
    {
        // TODO: Implement user() method.
    }

    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        throw new \Exception('Not implemented');
    }

    /**
     * Magically call the JWT instance.
     *
     * @param string $method
     * @param array $parameters
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->jwt, $method)) {
            return call_user_func_array([$this->jwt, $method], $parameters);
        }

        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        throw new BadMethodCallException("Method [$method] does not exist.");
    }
}
