<?php

declare(strict_types=1);

namespace Proflie\Admin;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Database\MysqliPool;
use Swoole\Database\MysqliProxy;

class PasswordUpdateApi
{
    private $mysqliPool;

    public function __construct(MysqliPool &$mysqliPool)
    {
        $this->mysqliPool = $mysqliPool;
    }

    public function execute(Request &$request, Response &$response, int $userId): void
    {
        $account = json_decode($request->getContent());

        if (strlen($account->password) < 8) {
            $response->status(422, "Your p is too short");
            $response->end();
            return;
        }

        $hashedPassword = password_hash($account->password, PASSWORD_DEFAULT);
        $this->update($userId, $hashedPassword);
        $response->write("password updated");
        $response->end();
    }

    private function update(int $id, string $password): void
    {
        $mysqli = $this->mysqliPool->get();
        $stmt = $mysqli->prepare('UPDATE users SET password = ?  WHERE users.id = ?');
        $stmt->bind_param('si', $password, $id);
        try {
            $result = $stmt->execute();
        } catch (\Exception $e) {
            swoole_error_log(SWOOLE_LOG_ERROR, $e->getMessage());
        }
        $this->mysqliPool->put($mysqli);
    }
}
