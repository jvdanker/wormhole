<?php
namespace MyApp;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\WsRouter;

require dirname(__DIR__) . '/vendor/autoload.php';

$clients = new \SplObjectStorage;

$router = new WsRouter;
$router->setRoute('/channel', new Chat($clients));
$router->setRoute('/notify', new Notifier($clients));

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            $router
        )
    ),
    8080
);

echo "Server running...";
$server->run();
