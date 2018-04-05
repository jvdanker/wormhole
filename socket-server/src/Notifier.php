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

        $message = json_decode($msg, true);
        $message['action'] = "receiveFiles";

        $channel = $message['channel'];
        $sender = $message['sender'];
        var_dump($message);

        foreach ($this->clients as $client) {
            $session = $client->session;
            $clientPhpSessionId = $session['PHPSESSID'];
            $clientChannelId = $session['CHANNEL'];

            echo sprintf("%s %s %s %s", $clientChannelId, $channel, $clientPhpSessionId, $sender);

//            if ($clientChannelId === $channel && $clientPhpSessionId !== $sender) {
            if ($clientChannelId === $channel) {
                echo sprintf("Send notification to %s\n", $message['sender']);
                $client->send(json_encode($message));
            }
        }

        $message = json_encode(array(
            "status" => "ok",
        ));
        $from->send($message);
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

    private function getPhpSessionId(ConnectionInterface $conn) {
        $httpRequest = $conn->httpRequest;
        $cookies = $httpRequest->getHeader('Cookie');

        $headerCookies = explode('; ', $cookies[0]);
        $cookies = array();
        foreach($headerCookies as $itm) {
            list($key, $val) = explode('=', $itm,2);
            $cookies[$key] = $val;
        }
        return $cookies['PHPSESSID'];
    }
}

