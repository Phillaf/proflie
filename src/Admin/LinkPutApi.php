<?php

declare(strict_types=1);

namespace Proflie\Admin;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Database\MysqliPool;
use Swoole\Database\MysqliProxy;

class LinkPutApi
{
    private $mysqliPool;
    private $query = <<< QUERY
UPDATE links
SET `social_media` = ?, `key` = ?
WHERE `user_id` = ? AND `id` = ?
QUERY;

    public function __construct(MysqliPool &$mysqliPool)
    {
        $this->mysqliPool = $mysqliPool;
    }

    public function execute(Request &$request, Response &$response, int $userId, string $id): void
    {
        $id = (int)$id;
        $link = json_decode($request->getContent());

        if (!$this->isUrlSafe($link->key)) {
            $response->status(400, "Invalid characters in username");
            $response->end();
            return;
        }

        $mysqli = $this->mysqliPool->get();
        $stmt = $mysqli->prepare($this->query);
        $stmt->bind_param("ssii", $link->socialMedia, $link->key, $userId, $id);
        $result = $stmt->execute();
        $this->mysqliPool->put($mysqli);
        $response->status(204);
        $response->end();
    }

    public function isUrlSafe($key): bool
    {
        preg_match('/^([a-zA-Z]|\d|-|\.|_|~)*$/', $key, $results);
        return count($results) > 0;
    }
}
