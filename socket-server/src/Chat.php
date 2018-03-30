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

//        var_dump($conn);
//        $test = array();
//        $test['test'] = 'testen';
//        $conn->test = $test;
//        var_dump($test);
//        var_dump($conn->test);

//        $conn->session['PHPSESSID'] = $this->getPhpSessionId($conn);
//        echo sprintf("PHPSESSID=%s\n", $conn->session['PHPSESSID']);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        echo sprintf("PHPSESSID=%s\n", $this->getPhpSessionId($from));

//        var_dump($from->test);

        $message = json_decode($msg, true);
        var_dump($message);

        if ($message['command'] === 'name') {
            echo sprintf("Set name to %s\n", $message['name']);
            // todo sync to php session on other side
            $session = [];
            $session['name'] = $message['name'];

            $from->session = $session;
        }

        if (isset($from->session)) {
            echo "From = ";
            echo $from->session['name'];
            echo "\n";
//            var_dump($from->session);
        }

       foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);
            }
        }
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
