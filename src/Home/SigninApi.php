<?php

declare(strict_types=1);

namespace Proflie\Home;

use Firebase\JWT\JWT;
use Swoole\Database\MysqliPool;
use Swoole\Database\MysqliProxy;
use Swoole\Http\Request;
use Swoole\Http\Response;

class SigninApi
{
    private $jwtSecret;
    private $mysqliPool;
    private $recaptchaSecret;
    private $host;

    public function __construct(MysqliPool &$mysqliPool, $jwtSecret, $recaptchaSecret, $host)
    {
        $this->jwtSecret = $jwtSecret;
        $this->mysqliPool = $mysqliPool;
        $this->recaptchaSecret = $recaptchaSecret;
        $this->host = $host;
    }

    public function execute(Request &$request, Response &$response): void
    {
        $creds = json_decode($request->getContent());

        if ($this->recaptcha($request->server['remote_addr'], $creds->recaptchaToken) < 0.5) {
            $this->error($response);
            return;
        }

        $mysqli = $this->mysqliPool->get();
        $account = $this->findAccount($creds->email, $mysqli);
        $this->mysqliPool->put($mysqli);

        if (is_null($account)) {
            $this->error($response);
            return;
        }

        if (!password_verify($creds->password, $account['password'])) {
            $this->error($response);
            return;
        }

        $response->write(json_encode([
            'token' => $this->buildToken($account['id'])
        ]));
        $response->status(200, 'Success');
        $response->end();
    }

    private function recaptcha(string $ip, string $token): float
    {
        $data = http_build_query([
            'secret' => $this->recaptchaSecret,
            'response' => $token,
            'remoteip' => $ip,
        ]);
        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $data,
            ]
        ]);
        $result = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
        $recaptcha = json_decode($result);
        return $recaptcha->score;
    }

    private function findAccount(string $email, MysqliProxy $mysqli): ?array
    {
        $stmt = $mysqli->prepare("SELECT * FROM users where email=?");
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            return null;
        };
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    private function error(Response &$response): void
    {
        $response->status(401);
        $response->write('{"Error": "Login failure"}');
        $response->end();
    }

    public function buildToken($id): string
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
