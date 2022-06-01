<?php

declare(strict_types=1);

namespace Proflie\Admin;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Swoole\Http\Request;
use Swoole\Http\Response;

class AuthenticatorUnauthorize
{
    private $controller;
    private $jwtSecret;

    public function __construct($controller, $jwtSecret)
    {
        $this->controller = $controller;
        $this->jwtSecret = $jwtSecret;
    }

    public function execute(Request &$request, Response &$response, ...$extra) : void
    {
        try {
            $decoded = JWT::decode($request->cookie['auth'], new Key($this->jwtSecret, 'HS256'));
        } catch (\Exception $e) {
            $response->cookie('auth', '', 1);
            $response->status(401);
            return;
        }
        $extra['userId'] = $decoded->user_id;

        $this->controller->execute($request, $response, ...$extra);
    }
}
