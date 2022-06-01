<?php

declare(strict_types=1);

namespace Proflie\Admin;

use Firebase\JWT\JWT;
use Swoole\Http\Request;
use Swoole\Http\Response;
use \Mailjet\Client;
use \Mailjet\Resources;
use Swoole\Database\MysqliPool;

class RequestEmailChangeApi
{
    private $jwtSecret;
    private $mailjetPublicKey;
    private $mailjetPrivateKey;
    private $mysqliPool;
    private $host;

    public function __construct(
        string $jwtSecret,
        string $mailjetPublicKey,
        string $mailjetPrivateKey,
        MysqliPool $mysqliPool,
        string $host,
    )
    {
        $this->jwtSecret = $jwtSecret;
        $this->mailjetPublicKey = $mailjetPublicKey;
        $this->mailjetPrivateKey = $mailjetPrivateKey;
        $this->mysqliPool = $mysqliPool;
        $this->host = $host;
    }

    public function execute(Request &$request, Response &$response, int $userId): void
    {
        $data = json_decode($request->getContent());

        if ($this->emailTaken($data->email)) {
            $response->status(409, "This email is already has an account.");
            $response->end();
            return;
        }

        $token = $this->buildToken($data->email, $userId);
        $this->sendEmail($data->email, $token);
        $response->status(200);
        $response->end();
    }

    private function emailTaken(string $email): bool
    {
        $mysqli = $this->mysqliPool->get();
        $stmt = $mysqli->prepare("SELECT * FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            return true;
        };
        $result = $stmt->get_result();
        return $result->fetch_assoc() !== null;
    }

    public function buildToken($email, $userId): string
    {
        $payload = [
            "iss" => $this->host,
            "aud" => $this->host,
            "iat" => time(),
            "exp" => time() + 60*60*24*7,
            "email" => $email,
            "user_id" => $userId,
        ];
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    private function sendEmail(string $email, string $token): bool
    {
        $mj = new Client($this->mailjetPublicKey, $this->mailjetPrivateKey, true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "account-creation@proflie.com",
                        'Name' => "Proflie",
                    ],
                    'To' => [
                        ['Email' => $email],
                    ],
                    'Subject' => "Email change confirmation for Proflie.com",
                    'TextPart' => <<<TEXT
Hi!

Please visit the link below to complete your email change on Proflie.com.

$this->host/confirm-email-change/$token

If this email was sent to you by mistake, please ignore it.

Proflie
TEXT,

                    'HTMLPart' => <<<HTML
<p>Hi!</p>
<p>Please visit th link below to complete your email change on Proflie.com</p>
<p><a href="$this->host/confirm-email-change/$token">$this->host/confirm-email-change/$token</a></p>
<p>If this email was sent to you by mistake, please ignore it.</p>
<p>Proflie</p>
HTML,
                    'CustomID' => "EmailChange",
                ]
            ]
        ];
        $mailresponse = $mj->post(Resources::$Email, ['body' => $body]);
        return $mailresponse->success();
    }
}

