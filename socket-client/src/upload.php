<?php

namespace MyApp;

require __DIR__ . '/../vendor/autoload.php';

//Enable error reporting.
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
header('Content-Type: text/plain; charset=utf-8');

$channelId = $_REQUEST['channelId'];

$receiver = new StreamReceiver($channelId);
$files = $receiver->receiveFiles($_FILES, $channelId);

$notifier = new ClientNotifier($channelId);
$notifier->sendNotifications($files);

echo "Ok";
