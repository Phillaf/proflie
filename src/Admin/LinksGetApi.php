<?php

declare(strict_types=1);

namespace Proflie\Admin;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Database\MysqliPool;
use Swoole\Database\MysqliProxy;

class LinksGetApi
{
    private $mysqliPool;

    public function __construct(MysqliPool &$mysqliPool)
    {
        $this->mysqliPool = $mysqliPool;
    }

    public function execute(Request &$request, Response &$response, int $userId): void
    {
        $mysqli = $this->mysqliPool->get();
        $results = $this->getRow($userId, $mysqli);
        $this->mysqliPool->put($mysqli);

        $response->write(json_encode($this->format($results)));
        $response->status(200);
        $response->end();
    }

    private function getRow(int $id, MysqliProxy $mysqli): ?array
    {
        $stmt = $mysqli->prepare("SELECT * FROM links WHERE links.user_id = ?");
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            return null;
        };
        $result = $stmt->get_result();
        return $result->fetch_all();
    }

    private function format(array $data): array
    {
        $output = [];
        foreach($data as $row) {
            $output[] = [
                'id' => $row[0],
                'socialMedia' => $row[2],
                'key' => $row[3],
            ];
        }
        return $output;
    }

}
