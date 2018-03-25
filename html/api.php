<?php

$data = json_decode(file_get_contents('php://input'), true);

$method = $data['method'];
if ($method === 'reset') {
    session_start();

    unset($_SESSION['files']);
    echo "ok";

    return;
}

if ($method === 'getFileList') {
    session_start();

    $files = $_SESSION['files'];
    if (!isset($files)) {
        $files = array();
    } else {
        $files = array($files);
    }

    $response = array(
        'files' => $files
    );

    header('Content-Type: application/json');
    echo json_encode($response);

    return;
}

http_response_code(403);