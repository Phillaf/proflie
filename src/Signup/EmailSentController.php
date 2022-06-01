<?php

declare(strict_types=1);

namespace Proflie\Signup;

use Swoole\Http\Request;
use Swoole\Http\Response;

class EmailSentController
{
    public function execute(Request &$request, Response &$response)
    {
        ob_start();
        include("EmailSent.html");
        $response->write(ob_get_clean());
        $response->end();
    }
}

