<?php

namespace MyApp;

class ClientNotifier {
    protected $channelId;

    public function __construct($channelId) {
        $this->channelId = $channelId;
    }

    public function sendNotifications($files) {
        $filenames = [];
        foreach ($files as $file) {
            $filenames[] = array(
                'filename' => $file['uploadName'],
                'size' => $file['size']
            );
        }

        $message = [
            "channel" => $this->channelId,
            "sender" => session_id(),
            "files" => $filenames
        ];

        \Ratchet\Client\connect('ws://server:8080/notify')->then(function($conn) use ($message) {
            $conn->on('message', function($msg) use ($conn, $message) {
                $conn->close();
                $msg = json_decode($msg, true);
                $clients = $msg['clients'];

                $message['clients'] = $msg['clients'];
                $fp = fopen("/uploads/session.json", "w");
                fwrite($fp, json_encode($message));
                fclose($fp);
            });

            $conn->send(json_encode($message));
        }, function ($e) {
            echo "Could not connect: {$e->getMessage()}\n";
        });
    }
}
