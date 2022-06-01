<?php

declare(strict_types=1);

namespace Proflie\Admin;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Database\MysqliPool;
use Swoole\Database\MysqliProxy;

\Swoole\Runtime::enableCoroutine(true, SWOOLE_HOOK_ALL);

class ProfileGetApi
{
    private $mysqliPool;

    public function __construct(MysqliPool &$mysqliPool)
    {
        $this->mysqliPool = $mysqliPool;
    }

    public function execute(Request &$request, Response &$response, int $userId): void
    {
        $mysqli = $this->mysqliPool->get();
        $row = $this->getRow($userId, $mysqli);
        $this->mysqliPool->put($mysqli);

        $response->write(json_encode([
            'id' => $row[0],
            'email' => $row[1],
            'username' => $row[2],
            'displayName' => $row[4],
            'title' => $row[5],
            'bio' => $row[6],
        ]));
        $response->status(200);
        $response->end();
    }

    private function getRow(int $id, MysqliProxy $mysqli): ?array
    {
        $stmt = $mysqli->prepare("SELECT * FROM users WHERE users.id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            return null;
        };
        $result = $stmt->get_result();
        return $result->fetch_row();
    }
}
