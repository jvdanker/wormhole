<?php

namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class WsRouter implements MessageComponentInterface {
    private $routes = array();

    public function setRoute($path, MessageComponentInterface $component) {
        $this->routes[$path] = $component;
    }

    public function onOpen(ConnectionInterface $conn) {
        $path = $conn->httpRequest->getUri()->getPath();
        echo sprintf('WsRouter: onOpen: %s\n', $path);

        if (array_key_exists($path, $this->routes)) {
            $conn->route = $this->routes[$path];
            $conn->route->onOpen($conn);
        } else {
            echo sprintf("WsRouter: invalid path %s\n", $path);
            $conn->close();
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $from->route->onMessage($from, $msg);
    }

    public function onClose(ConnectionInterface $conn) {
        if (isset($conn->route)) {
            $conn->route->onClose($conn);
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->route->onError($conn, $e);
    }
}
