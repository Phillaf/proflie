<?php

declare(strict_types=1);

namespace Proflie\Profile;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Database\MysqliPool;
use Swoole\Database\MysqliProxy;

class ProfileController
{
    private $mysqliPool;

    public function __construct(MysqliPool &$mysqliPool)
    {
        $this->mysqliPool = $mysqliPool;
    }

    public function execute(Request &$request, Response &$response)
    {
        $username = explode('.', $request->header['host'] ?? "")[0];

        $mysqli = $this->mysqliPool->get();
        if (!$profile = $this->getProfile($username, $mysqli)) {
            $response->status(404);
            $response->end();
            return;
        }

        $profile['display_name'] = htmlspecialchars($profile['display_name'] ?? "");
        $profile['title'] = htmlspecialchars($profile['title'] ?? "");
        $profile['bio'] = htmlspecialchars($profile['bio'] ?? "");

        extract($profile);
        extract(['links' => $this->links($username, $mysqli)]);

        ob_start();
        include("Profile.html");
        $response->write(ob_get_clean());
        $response->end();

        $this->mysqliPool->put($mysqli);
   }

    public function getProfile(string $username, MysqliProxy $mysqli): false|array
    {
        $stmt = $mysqli->prepare("SELECT * FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        if (!$stmt->execute()) {
            return false;
        };
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        return $data ?? false;
    }

    public function links(string $username, MysqliProxy $mysqli): array
    {
        $query = <<<EOT
SELECT * FROM links
JOIN users ON users.id = links.user_id
WHERE users.username="{$username}";
EOT;
        $result = $mysqli->query($query);
        return $result->fetch_all();
    }
}
