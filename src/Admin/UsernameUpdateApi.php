<?php

declare(strict_types=1);

namespace Proflie\Admin;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Database\MysqliPool;
use Swoole\Database\MysqliProxy;

class UsernameUpdateApi
{
    private $mysqliPool;

    public function __construct(MysqliPool &$mysqliPool)
    {
        $this->mysqliPool = $mysqliPool;
    }

    public function execute(Request &$request, Response &$response, int $userId): void
    {
        $account = json_decode($request->getContent());
        $mysqli = $this->mysqliPool->get();

        if (!$this->isUrlSafe($account->username)) {
            $response->status(400, "Invalid characters in username");
            $response->end();
            return;
        }

        if ($this->usernameTaken($mysqli, $account->username)) {
            $response->status(409, "This username is already in use");
            $response->end();
            return;
        }
        $this->update($mysqli, $userId, $account->username);
        $response->write("username updated");
        $response->end();
        $this->mysqliPool->put($mysqli);
    }

    public function isUrlSafe($key): bool
    {
        preg_match('/^([a-zA-Z]|\d|-|\.|_|~)*$/', $key, $results);
        return count($results) > 0;
    }

    private function usernameTaken(MysqliProxy &$mysqli, string $username): bool
    {
        $stmt = $mysqli->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $row =[];
        try {
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_all();
        } catch (\Exception $e) {
            swoole_error_log(SWOOLE_LOG_ERROR, $e->getMessage());
        }
        return $row !== [];
    }

    private function update(MysqliProxy &$mysqli, int $id, string $username): void
    {
        $stmt = $mysqli->prepare('UPDATE users SET username = ?  WHERE users.id = ?');
        $stmt->bind_param('si', $username, $id);
        try {
            $result = $stmt->execute();
        } catch (\Exception $e) {
            swoole_error_log(SWOOLE_LOG_ERROR, $e->getMessage());
        }
    }
}
