<?php

declare(strict_types=1);

namespace Proflie\Admin;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Database\MysqliPool;
use Swoole\Database\MysqliProxy;

class AdminController
{
    private $mysqliPool;
    private $host;

    public function __construct(MysqliPool &$mysqliPool, string $host)
    {
        $this->mysqliPool = $mysqliPool;
        $this->host = $host;
    }

    public function execute(Request &$request, Response &$response, int $id) : void
    {
        $mysqli = $this->mysqliPool->get();
        $data = $this->getData($id, $mysqli);
        $this->mysqliPool->put($mysqli);

        extract(['profile' => $this->getProfile($data)]);
        extract(['host' => explode('://', $this->host)]);
        ob_start();
        include("Admin.html");
        $response->write(ob_get_clean());
        $response->end();
    }

    private function getData(int $id, MysqliProxy $mysqli): ?array
    {

        $stmt = $mysqli->prepare("SELECT * FROM users WHERE users.id = ?");
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            return null;
        };
        $result = $stmt->get_result();
        return $result->fetch_all();
    }

    private function getProfile(array $data) : array
    {
        return [
            'email' => $data[0][1],
            'username' => $data[0][2],
            'displayName' => $data[0][4],
            'title' => $data[0][5],
            'bio' => $data[0][6],
        ];
    }
}
