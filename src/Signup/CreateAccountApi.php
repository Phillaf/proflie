<?php

declare(strict_types=1);

namespace Proflie\Signup;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Database\MysqliPool;
use Swoole\Database\MysqliProxy;

class CreateAccountApi
{
    private $mysqliPool;
    private $jwtSecret;
    private $host;

    public function __construct(MysqliPool &$mysqliPool, string $jwtSecret, string $host)
    {
        $this->mysqliPool = $mysqliPool;
        $this->jwtSecret = $jwtSecret;
        $this->host = $host;
    }

    public function execute(Request &$request, Response &$response): void
    {
        $account = json_decode($request->getContent());
        if (!$email = $this->decodeEmailToken($account->token)) {
            $response->status(400);
            $response->end();
            return;
        }

        if (strlen($account->password) < 8) {
            $response->status(422, "Your p is too short");
            $response->end();
            return;
        }

        if (!$this->isUrlSafe($account->username)) {
            $response->status(400, "Invalid characters in username");
            $response->end();
            return;
        }

        if ($this->isDuplicate($email, $account->username)) {
            $response->status(409, "There's already an account for this email or username");
            $response->end();
            return;
        }

        $hashedPassword = password_hash($account->password, PASSWORD_DEFAULT);
        $id = $this->insert($email, $hashedPassword, $account->username);
        $response->write(json_encode([
            'token' => $this->buildAuthToken($id)
        ]));
        $response->status(201, 'Created');
        $response->end();
    }

    public function isUrlSafe($key): bool
    {
        preg_match('/^([a-zA-Z]|\d|-|\.|_|~)*$/', $key, $results);
        return count($results) > 0;
    }
     
    private function decodeEmailToken($token): ?string
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
        } catch (\Exception $e) {
            return null;
        }

        return $decoded->email;
    }

    private function isDuplicate(string $email, string $username): bool
    {
        $mysqli = $this->mysqliPool->get();
        $stmt = $mysqli->prepare("SELECT * FROM users WHERE email=? OR username=?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $record = $result->fetch_assoc();
        return $record !== null;
    }

    private function insert(string $email, string $password, string $username): int
    {
        $mysqli = $this->mysqliPool->get();
        $stmt = $mysqli->prepare("INSERT INTO users(email, password, username) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $password, $username);
        $stmt->execute();
        $id = $mysqli->insert_id;
        $this->mysqliPool->put($mysqli);
        return $id;
    }

    private function buildAuthToken($id): string
    {
        $payload = [
            "iss" => $this->host,
            "aud" => $this->host,
            "iat" => time(),
            "exp" => time() + 60*60*24,
            "user_id" => $id
        ];
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
}
