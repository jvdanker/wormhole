<?php

namespace MyApp;

require __DIR__ . '/../vendor/autoload.php';

//Enable error reporting.
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
header('Content-Type: text/plain; charset=utf-8');

$channelId = $_REQUEST['channelId'];
$transferSessionId = uniqid("", true);;

$receiver = new StreamReceiver($transferSessionId);
$files = $receiver->receiveFiles($_FILES);

$notifier = new ClientNotifier($transferSessionId, $channelId);
$notifier->sendNotifications($files);
