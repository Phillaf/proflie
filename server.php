#!/usr/bin/env php
<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Runtime;

$routes = require __DIR__ . '/bootstrap/routes.php';
$services = require __DIR__ . '/bootstrap/services.php';

$domainWithoutDotCom = str_replace(".com", "", explode('//', $_ENV['HOST'])[1]);

Runtime::enableCoroutine(true, SWOOLE_HOOK_ALL);

$https = new Swoole\Http\Server("0.0.0.0", 443, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$http = $https->addListener("0.0.0.0", 80, SWOOLE_SOCK_TCP);

$https->set([
    'ssl_cert_file' => '/etc/letsencrypt/live/proflie.com/fullchain.pem',
    'ssl_key_file' => '/etc/letsencrypt/live/proflie.com/privkey.pem',
    'log_level' => 0,
    //'open_http2_protocol' => true,
]);

$http->on("request", function (Request $request, Response $response) {
    try {
        accessLog($request, $response);
        if (letsEncrypt($request, $response))
            return;

        $response->redirect("https://{$request->header['host']}{$request->server['request_uri']}", 302); // todo: switch to 301 permanent redirect
    } catch(\Throwable $e) {
      var_dump($request);
      throw $e;
    }
});

$https->on("request", function (Request $request, Response $response) use ($routes, $services, $domainWithoutDotCom) {
    try {
        accessLog($request, $response);
        if (letsEncrypt($request, $response))
            return;

        if (getStatic($request, $response))
            return;

        if (getProfile($request, $response, $services, $domainWithoutDotCom))
            return;

        if (getPhp($request, $response, $services, $routes))
            return;

        $response->status(404, 'Not Found');
        $response->write('404 Not Found');
        $response->end();
    } catch(\Throwable $e) {
      var_dump($request);
      throw $e;
    }
});

$https->start();

function accessLog(Request $request, Response $response) : void
{
    $date =  date("c", $request->server['master_time']);
$log = <<< LOG
{$request->server['remote_addr']} - {$request->header['host']} - [$date] - {$request->server['request_method']} {$request->server['request_uri']} - {$request->header['user-agent']}\r\n
LOG;

    file_put_contents("/var/log/proflie/access.log", $log, FILE_APPEND);
}

function letsEncrypt(Request $request, Response $response): bool
{
    if (substr($request->server['request_uri'], 0, 28) !== "/.well-known/acme-challenge/") {
        return false;
    }

    $staticFile = __DIR__ . "/letsencrypt/.well-known/acme-challenge/" . substr($request->server['request_uri'], 28);

    if (!file_exists($staticFile)) {
        return false;
    }

    $response->header("Content-Type", "text/html; charset=utf-8");
    $response->write(file_get_contents($staticFile));
    return true;
}

function getStatic(Request $request, Response $response): bool
{
    $staticFile = __DIR__ . "/src" . $request->server['request_uri'];
    if (!file_exists($staticFile)) {
        return false;
    }

    if(!in_array(pathinfo($staticFile, PATHINFO_EXTENSION), ['js', 'txt', 'ico'])) {
        return false;
    };

    $response->sendfile($staticFile);
    return true;
}

function getProfile(Request $request, Response $response, array $services, string $domainWithoutDotCom): bool
{
    $subdomain = explode('.', $request->header['host'] ?? "")[0];
    if ($subdomain !== $domainWithoutDotCom) {
        $profilecontroller = $services['profile'];
        $profilecontroller->execute($request, $response);
        return true;
    };
    return false;
}

function getPhp(Request $request, Response $response, array $services, $routes): bool
{
    $httpMethod = $request->server['request_method'];
    $uri = $request->server['request_uri'];
    if (false !== $pos = strpos($uri, '?')) {
        $uri = substr($uri, 0, $pos);
    }
    $uri = rawurldecode($uri);

    $routeInfo = $routes->dispatch($httpMethod, $uri);

    if ($routeInfo[0] === FastRoute\Dispatcher::FOUND) {
        $controller = $services[$routeInfo[1]];
        $controller->execute($request, $response, ...$routeInfo[2]);
        return true;
    }
    if ($routeInfo[0] === FastRoute\Dispatcher::METHOD_NOT_ALLOWED) {
        $response->status(405, 'Method Not Allowed');
        $response->write('405 Method Not Allowed');
        $response->end();
        return true;
    }
    return false;
}
