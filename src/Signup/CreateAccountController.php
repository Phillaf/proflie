<?php

declare(strict_types=1);

namespace Proflie\Signup;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Swoole\Http\Request;
use Swoole\Http\Response;

class CreateAccountController
{
    private $jwtSecret;

    public function __construct($jwtSecret)
    {
        $this->jwtSecret = $jwtSecret;
    }

    public function execute(Request &$request, Response &$response, string $token)
    {
        if (!$email = $this->decodeToken($token)) {
            ob_start();
            include("TokenError.html");
            $response->write(ob_get_clean());
            $response->end();
            return;
        };
        ob_start();
        include("CreateAccount.html");
        $response->write(ob_get_clean());
        $response->end();
    }

    public function decodeToken(string $token): ?string
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
        } catch (\Exception $e) {
            return null;
        }

        return $decoded->email;
    }
}
