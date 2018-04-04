<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Notifier implements MessageComponentInterface {
    protected $clients;

    public function __construct($clients)
    {
        $this->clients = $clients;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        echo "-----------------------------------------------------\n";
        echo "onOpen - Notifier\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        echo "------------------------------------------------------------------\n";
        echo sprintf("New message on Connection %d, message %s\n", $from->resourceId, $msg);

        $message = json_encode(array(
            "status" => "ok",
        ));

        $from->send($message);

        foreach ($this->clients as $client) {
            $client->send($message);
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        echo "onClose";
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "onError";
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}

