<?php
use Ratchet\Server\IoServer;
use MyApp\Chat;

require dirname(__FILE__) . '/vendor/autoload.php';

$server = IoServer::factory(
    new Chat(),
    80
);

$server->run();