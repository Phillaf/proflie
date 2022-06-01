<?php

declare(strict_types=1);

namespace Proflie\Admin;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Database\MysqliPool;
use Swoole\Database\MysqliProxy;

class LinkPostApi
{
    private $mysqliPool;
    private $query = <<< QUERY
INSERT INTO links (`user_id`, `social_media`, `key`)
VALUES (?, ?, ?)
QUERY;

    public function __construct(MysqliPool &$mysqliPool)
    {
        $this->mysqliPool = $mysqliPool;
    }

    public function execute(Request &$request, Response &$response, int $userId): void
    {
        $link = json_decode($request->getContent());

        if (!$this->isUrlSafe($link->key)) {
            $response->status(400, "Invalid characters in username");
            $response->end();
            return;
        }

        $mysqli = $this->mysqliPool->get();
        $stmt = $mysqli->prepare($this->query);

        $stmt->bind_param("iss", $userId, $link->socialMedia, $link->key);
        $result = $stmt->execute();

        $response->write(json_encode([
            'id' => $mysqli->insert_id,
            'socialMedia' => $link->socialMedia,
            'key' => $link->key,
        ]));
        $response->status(201);
        $response->end();

        $this->mysqliPool->put($mysqli);
    }

    public function isUrlSafe($key): bool
    {
        preg_match('/^([a-zA-Z]|\d|-|\.|_|~)*$/', $key, $results);
        return count($results) > 0;
    }
}

