<?php

declare(strict_types=1);

namespace Proflie\Robots;

use Swoole\Http\Request;
use Swoole\Http\Response;

class RobotsController
{
    public function execute(Request &$request, Response &$response)
    {
        $robots = <<<EOT
User-agent: *
Allow: /
EOT;
        $response->write($robots);
        $response->end();
    }
}
