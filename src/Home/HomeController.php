<?php

declare(strict_types=1);

namespace Proflie\Home;

use Swoole\Http\Request;
use Swoole\Http\Response;

class HomeController
{
    public function execute(Request &$request, Response &$response)
    {
        ob_start();
        include("Home.html");
        $response->write(ob_get_clean());
        $response->end();
    }
}
