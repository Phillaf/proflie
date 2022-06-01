<?php

declare(strict_types=1);

namespace Proflie\Signup;

use Swoole\Http\Request;
use Swoole\Http\Response;

class SendEmailVerificationController
{
    public function execute(Request &$request, Response &$response)
    {
        ob_start();
        include("SendEmailVerification.html");
        $response->write(ob_get_clean());
        $response->end();
    }
}
