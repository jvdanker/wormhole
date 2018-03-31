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
        $session['name'] = 'Me @ ' . $channel;
        $conn->session = $session;

        print_r($conn->session);

        $msg = json_encode(array(
            "channel" => $channel,
            "yourName" => 'Me @ ' . $channel,
            "members" => [[
                "resourceId" => $conn->resourceId,
                "name" => 'Me @ ' . $channel
            ]]
        ));

        $conn->send($msg);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "------------------------------------------------------------------\n";
        echo sprintf("New message on Connection %d, message %s\n", $from->resourceId, $msg);
        echo sprintf("PHPSESSID=%s\n", $this->getPhpSessionId($from));

        $message = json_decode($msg, true);
        $session = $from->session;

        $this->printClients();

        if ($message['command'] === 'joinChannel') {
            $oldChannel = $session['CHANNEL'];
            $newChannel = trim($message['channel']);
            if (!empty($newChannel) && $this->channelExists($newChannel)) {
                $session['CHANNEL'] = $newChannel;
                $from->session = $session;

                $this->updateChannelMembers($oldChannel);
                $this->updateChannelMembers($newChannel);
            } else {
                $this->sendError($from, $msg, "Invalid channel");
            }
        }

        if ($message['command'] === 'name') {
            $channel = $session['CHANNEL'];
            $session['name'] = $message['name'];
            $from->session = $session;

            $this->updateChannelMembers($channel);
        }

        $this->printClients();
    }

    public function onClose(ConnectionInterface $conn) {
        echo "onClose";
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        $this->removeClientFromChannel($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "onError";
        echo "An error has occurred: {$e->getMessage()}\n";

        $this->removeClientFromChannel($conn);

        $conn->close();
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

    private function channelExists($channel) {
        foreach ($this->clients as $client) {
            $session = $client->session;
            if ($session['CHANNEL'] === $channel) {
                return true;
            }
        }

        return false;
    }

    private function sendToSender($from, $message) {
        foreach ($this->clients as $client) {
            if ($from === $client) {
                $client->send($message);
            }
        }
    }

    private function sendError($from, $msg, $error) {
        $this->sendToSender($from, json_encode([
            "status" => $error,
            "request" => $msg
        ]));
    }

    private function removeClientFromChannel(ConnectionInterface $conn) {
        $session = $conn->session;
        $channel = $session['CHANNEL'];
        unset($conn->session);
        $this->updateChannelMembers($channel);
    }
}
