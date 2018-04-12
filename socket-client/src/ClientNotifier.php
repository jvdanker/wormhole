<?php

namespace MyApp;

class ClientNotifier {
    protected $channelId;
    protected $transferSessionId;

    public function __construct($transferSessionId, $channelId) {
        $this->transferSessionId = $transferSessionId;
        $this->channelId = $channelId;
    }

    public function sendNotifications($files) {
        $filenames = [];
        foreach ($files as $file) {
            $filenames[] = array(
                'uploadName' => $file['uploadName'],
                'filename' => $file['name'],
                'size' => $file['size']
            );
        }

        $message = [
            "transferSessionId" => $this->transferSessionId,
            "channel" => $this->channelId,
            "sender" => session_id(),
            "files" => $filenames
        ];

        \Ratchet\Client\connect('ws://server:8080/notify')->then(function($conn) use ($message) {
            $conn->on('message', function($msg) use ($conn, $message) {
                $conn->close();
                $msg = json_decode($msg, true);
                $clients = $msg['clients'];

                $message['clients'] = $clients;
                $fp = fopen(
                    sprintf("/uploads/%s/session.json",$this->transferSessionId),
                    "w");
                fwrite($fp, json_encode($message));
                fclose($fp);
            });

            $conn->send(json_encode($message));
        }, function ($e) {
            echo "Could not connect: {$e->getMessage()}\n";
        });
    }
}
