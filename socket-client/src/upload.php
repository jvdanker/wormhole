<?php

namespace MyApp;

use Wrench\Client;

session_start();
header('Content-Type: text/plain; charset=utf-8');

$channelId = $_REQUEST['channelId'];
if (empty($channelId)) {
    http_response_code(403);
    return;
}

$uploads = '/uploads';
if (!is_dir($uploads)) {
    throw new Exception($uploads . ' does not exist!');
}

if (!is_dir('/uploads/' . $channelId)) {
    mkdir('/uploads/' . $channelId);
}

//Enable error reporting.
error_reporting(E_ALL);
ini_set("display_errors", 1);

//Use ini_get to get the value of
//the file_uploads directive
if(ini_get('file_uploads')){
    echo 'file_uploads is set to "1". File uploads are allowed.';
} else{
    echo 'Warning! file_uploads is set to "0". File uploads are NOT allowed.';
}

$tempFolder = ini_get('upload_tmp_dir');
if (empty($tempFolder)) {
    $tempFolder = sys_get_temp_dir();
}

echo 'Your upload_tmp_dir directive has been set to: "' . $tempFolder . '"<br>';

//Firstly, lets make sure that the upload_tmp_dir
//actually exists.
if(!is_dir($tempFolder)){
    throw new Exception($tempFolder . ' does not exist!');
} else{
    echo 'The directory "' . $tempFolder . '" does exist.<br>';
}

if(!is_writable($tempFolder)){
    throw new Exception($tempFolder . ' is not writable!');
} else{
    echo 'The directory "' . $tempFolder . '" is writable. All is good.<br>';
}

//var_dump($_FILES);
//var_dump($_PUT);

$files = array();

/* Handle moving the file(s) */
if (count($_FILES) > 0) {
    foreach($_FILES as $key => $value) {
        $tmpName = $value['tmp_name'];
        $newName = sha1_file($tmpName);
        $uploadName = sprintf('/uploads/%s/%s', $channelId, $newName);

        if (!is_uploaded_file($tmpName)) {
            rename($tmpName, $uploadName);
        } else {
            move_uploaded_file($tmpName, $uploadName);
        }

        $manifest = array(
            'filename' => $value['name'],
            'size' => $value['size'],
            'uploadName' => $newName,
            'timestamp' => time()
        );

        $myfile = fopen($uploadName . ".json", "w") or die("Unable to open file!");
        fwrite($myfile, json_encode($manifest));
        fclose($myfile);
    }
}

$client = new Client("ws://server:8080", "http://server/");
$client->connect();
$client->sendData('hello');
$response = $client->receive()[0]->getPayload();
$client->disconnect();

echo "Ok";
