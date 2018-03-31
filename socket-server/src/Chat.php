<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
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

    public function onOpen(ConnectionInterface $conn) {
        echo "-----------------------------------------------------\n";
        echo "onOpen\n";

//        $conn->session = array();
//        $conn->session['test'] = 'hallo';

        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        if (empty($this->getPhpSessionId($conn))) {
            die("empty session id");
        }

//        var_dump($conn);
//        $test = array();
//        $test['test'] = 'testen';
//        $conn->test = $test;
//        var_dump($test);
//        var_dump($conn->test);

        $channel = uniqid("", true);

        $session = [];
        $session['PHPSESSID'] = $this->getPhpSessionId($conn);
        $session['CHANNEL'] = $channel;
        $conn->session = $session;

        print_r($conn->session);

        $msg = json_encode(array(
            "channel" => $channel,
            "members" => [[
                "resourceId" => $conn->resourceId,
                "name" => ''
            ]]
        ));

        $conn->send($msg);

        echo "New connection! ({$conn->resourceId})\n";
    }

    private function printClients() {
        echo "------------------------------------------------------------------\n";
        foreach ($this->clients as $client) {
            $session = $client->session;
            echo sprintf("\t%s - %s\n", $client->resourceId, json_encode($session));

        }
        echo "------------------------------------------------------------------\n";
    }

    private function updateChannelMembers($channel) {
        $members = [];
        $clientsToInform = [];
        foreach ($this->clients as $client) {
            $session = $client->session;
            if ($session['CHANNEL'] === $channel) {
                $clientsToInform[] = $client;
                $members[] = [
                    "resourceId" => $client->resourceId,
                    "name" => $session['name']
                ];
            }
        }

        $msg = json_encode(array(
            "channel" => $channel,
            "members" => $members
        ));

        print_r($msg);
        echo "\n";

        foreach ($clientsToInform as $client) {
            $client->send($msg);
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "------------------------------------------------------------------\n";
        echo sprintf("New message on Connection %d, message %s\n", $from->resourceId, $msg);
        echo sprintf("PHPSESSID=%s\n", $this->getPhpSessionId($from));

        $message = json_decode($msg, true);
        $session = $from->session;

        $this->printClients();

        if ($message['command'] === 'joinChannel') {
            $session['CHANNEL'] = $message['channel'];
            $from->session = $session;

            $this->updateChannelMembers($session['CHANNEL']);
        }

        if ($message['command'] === 'name') {
            $session['name'] = $message['name'];
            $from->session = $session;

            $this->updateChannelMembers($session['CHANNEL']);
        }

        $this->printClients();
    }

    public function onClose(ConnectionInterface $conn) {
        echo "onClose";
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "onError";
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
