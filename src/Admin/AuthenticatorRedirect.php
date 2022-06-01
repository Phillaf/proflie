<?php

declare(strict_types=1);

namespace Proflie\Admin;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Swoole\Http\Request;
use Swoole\Http\Response;

class AuthenticatorRedirect
{
    private $controller;
    private $jwtSecret;
    private $host;

    public function __construct($controller, $jwtSecret, $host)
    {
        $this->controller = $controller;
        $this->jwtSecret = $jwtSecret;
        $this->host = $host;
    }

    public function execute(Request &$request, Response &$response) : void
    {
        if (!isset($request->cookie['auth'])){
            $response->cookie('auth', '', 1);
            $response->redirect($this->host, 302);
            return;
        }
        try {
            $decoded = JWT::decode($request->cookie['auth'], new Key($this->jwtSecret, 'HS256'));
        } catch (\Exception $e) {
            $response->cookie('auth', '', 1);
            $response->redirect($this->host, 302);
            return;
        }

        $this->controller->execute($request, $response, $decoded->user_id);
    }
}
