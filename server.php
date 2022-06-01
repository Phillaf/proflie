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

$http = new Swoole\Http\Server("0.0.0.0", 80);
$http->set([
    'log_level' => 0,
    'open_http2_protocol' => true,
]);
$http->on("request", function (Request $request, Response $response) use ($routes, $services, $domainWithoutDotCom) {

    if (getProfile($request, $response, $services, $domainWithoutDotCom))
        return;

    if (getStatic($request, $response))
        return;

    if (getPhp($request, $response, $services, $routes))
        return;

    $response->status(404, 'Not Found');
    $response->write('404 Not Found');
    $response->end();
});

$http->start();


function getProfile(Request $request, Response $response, array $services, string $domainWithoutDotCom): bool
{
    $subdomain = explode('.', $request->header['host'])[0];
    if ($subdomain !== $domainWithoutDotCom) {
        $profilecontroller = $services['profile'];
        $profilecontroller->execute($request, $response);
        return true;
    };
    return false;
}

function getStatic(Request $request, Response $response): bool
{
    $staticFile = __DIR__ . "/src" . $request->server['request_uri'];
    if (!file_exists($staticFile)) {
        return false;
    }

    if(pathinfo($staticFile, PATHINFO_EXTENSION) !== 'js') {
        return false;
    };

    $response->header('Content-Type', 'text/javascript');
    $response->sendfile($staticFile);
    return true;
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
