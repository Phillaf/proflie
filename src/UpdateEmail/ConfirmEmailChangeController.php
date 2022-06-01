<?php

declare(strict_types=1);

namespace Proflie\UpdateEmail;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Database\MysqliPool;

class ConfirmEmailChangeController
{
    private $jwtSecret;
    private $mysqliPool;
    private $host;

    public function __construct($jwtSecret, MysqliPool $mysqliPool, string $host)
    {
        $this->jwtSecret = $jwtSecret;
        $this->mysqliPool = $mysqliPool;
        $this->host = $host;
    }

    public function execute(Request &$request, Response &$response, string $emailToken)
    {
        $data = $this->decodeEmailToken($emailToken);

        // recaptcha + email taken verif
        if (!$this->update($data->user_id, $data->email)) {
            ob_start();
            include("TokenError.html");
            $response->write(ob_get_clean());
            $response->end();
            return;
        }

        $expiration = time() + 60*60*24;
        $authToken = $this->buildAuthToken($data->user_id, $expiration);
        $response->cookie('auth', $authToken, $expiration, '/');
        $response->redirect("$this->host/admin", 302);
        return;
    }

    public function decodeEmailToken(string $token): ?\stdClass
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
        } catch (\Exception $e) {
            return null;
        }

        return $decoded;
    }

    public function buildAuthToken($id, $expiration): string
    {
        $payload = [
            "iss" => $this->host,
            "aud" => $this->host,
            "iat" => time(),
            "exp" => $expiration,
            "user_id" => $id
        ];
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    private function update(int $userId, string $email): bool
    {
        $mysqli = $this->mysqliPool->get();
        $stmt = $mysqli->prepare('UPDATE users SET email=? WHERE id=?');
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        $affectedRows = $mysqli->affected_rows;
        $this->mysqliPool->put($mysqli);
        return $affectedRows === 1;
    }
}
