<?php

namespace MyApp;

class ClientNotifier {
    protected $channelId;

    public function __construct($channelId) {
        $this->channelId = $channelId;
    }

    public function sendNotifications($files) {
        $message = [
            "channel" => $this->channelId,
            "sender" => session_id(),
            "files" => $files
        ];

        \Ratchet\Client\connect('ws://server:8080/notify')->then(function($conn) use ($message) {
            $conn->on('message', function($msg) use ($conn) {
                echo "Received: {$msg}\n";
                $conn->close();
            });

            $conn->send(json_encode($message));
        }, function ($e) {
            echo "Could not connect: {$e->getMessage()}\n";
        });

    }



}