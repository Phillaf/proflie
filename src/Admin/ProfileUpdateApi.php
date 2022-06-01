<?php

declare(strict_types=1);

namespace Proflie\Admin;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Database\MysqliPool;

class ProfileUpdateApi
{
    private $mysqliPool;
    private $query = <<< QUERY
UPDATE  users
SET display_name = ?, title = ?, bio = ?
WHERE users.id = ?
QUERY;

    public function __construct(MysqliPool &$mysqliPool)
    {
        $this->mysqliPool = $mysqliPool;
    }

    public function execute(Request &$request, Response &$response, int $userId): void
    {
        $profile = json_decode($request->getContent());
        $profile->bio = strip_tags($profile->bio);
        $profile->displayName = strip_tags($profile->displayName);
        $profile->title = strip_tags($profile->title);

        $this->update($userId, $profile->displayName, $profile->title, $profile->bio);
        $response->write("profile updated");
        $response->end();
    }

    private function update(int $id, string $displayName, string $title, string $bio): void
    {
        $mysqli = $this->mysqliPool->get();
        $stmt = $mysqli->prepare($this->query);
        $stmt->bind_param("sssi", $displayName, $title, $bio, $id);
        try {
            $result = $stmt->execute();
        } catch (\Exception $e) {
            swoole_error_log(SWOOLE_LOG_ERROR, $e->getMessage());
        }
        $this->mysqliPool->put($mysqli);
    }
}
