<?php

declare(strict_types=1);

namespace Proflie\Signup;

use Firebase\JWT\JWT;
use Swoole\Http\Request;
use Swoole\Http\Response;
use \Mailjet\Client;
use \Mailjet\Resources;
use Swoole\Database\MysqliPool;

class SendEmailVerificationApi
{
    private $jwtSecret;
    private $mailjetPublicKey;
    private $mailjetPrivateKey;
    private $mysqliPool;
    private $recaptchaSecret;
    private $host;

    public function __construct(
        string $jwtSecret,
        string $mailjetPublicKey,
        string $mailjetPrivateKey,
        MysqliPool $mysqliPool,
        string $recaptchaSecret,
        string $host,
    )
    {
        $this->jwtSecret = $jwtSecret;
        $this->mailjetPublicKey = $mailjetPublicKey;
        $this->mailjetPrivateKey = $mailjetPrivateKey;
        $this->mysqliPool = $mysqliPool;
        $this->recaptchaSecret = $recaptchaSecret;
        $this->host = $host;
    }

    public function execute(Request &$request, Response &$response): void
    {
        $data = json_decode($request->getContent());

        if ($this->emailTaken($data->email)) {
            $response->status(409, "There's already an account for this email");
            $response->end();
            return;
        }

        if ($this->recaptcha($request->server['remote_addr'], $data->recaptchaToken) < 0.5) {
            $response->status(400);
            $response->end();
            return;
        }

        $token = $this->buildToken($data->email);
        $this->sendEmail($request, $data->email, $token);
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

    public function buildToken($email): string
    {
        $payload = [
            "iss" => $this->host,
            "aud" => $this->host,
            "iat" => time(),
            "exp" => time() + 60*60*24*7,
            "email" => $email
        ];
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    private function sendEmail(Request $request, string $email, string $token): bool
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
                    'Subject' => "Account creation confirmation for Proflie.com",
                    'TextPart' => <<<TEXT
Hi!

Please visit the link below to complete your account creation on Proflie.com.

$this->host/account/$token

If this email was sent to you by mistake, please ignore it.

Proflie
TEXT,

                    'HTMLPart' => <<<HTML
<p>Hi!</p>
<p>Please visit the link below to complete your account creation on Proflie.com.</p>
<p><a href="$this->host/account/$token">$this->host/account/$token</a></p>
<p>If this email was sent to you by mistake, please ignore it.</p>
<p>Proflie</p>
HTML,
                    'CustomID' => "EmailVerification",
                ]
            ]
        ];
        $mailresponse = $mj->post(Resources::$Email, ['body' => $body]);
        return $mailresponse->success();
    }
}
