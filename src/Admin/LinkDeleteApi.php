<?php

declare(strict_types=1);

namespace Proflie\Admin;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Database\MysqliPool;
use Swoole\Database\MysqliProxy;

class LinkDeleteApi
{
    private $mysqliPool;

    public function __construct(MysqliPool &$mysqliPool)
    {
        $this->mysqliPool = $mysqliPool;
    }

    public function execute(Request &$request, Response &$response, int $userId, string $id): void
    {
        $mysqli = $this->mysqliPool->get();
        $stmt = $mysqli->prepare("DELETE FROM links WHERE `user_id` = ? AND `id` = ?");
        $stmt->bind_param("ii", $userId, $id);
        $result = $stmt->execute();
        $this->mysqliPool->put($mysqli);
        $response->status(204);
        $response->end();
    }
}

