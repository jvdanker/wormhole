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
    }

    $response = array(
        'files' => $files
    );

    header('Content-Type: application/json');
    echo json_encode($response);
    return;
}

if ($method === 'getSession') {
    session_start();

    $response['transferSessionId'] = $_SESSION['transferSessionId'];

    header('Content-Type: application/json');
    echo json_encode($response);
    return;
}

if ($method === 'startSession') {
    session_start();

    $_SESSION['transferSessionId'] = uniqid();
    $response['transferSessionId'] = $_SESSION['transferSessionId'];

    header('Content-Type: application/json');
    echo json_encode($response);
    return;
}


if ($method === 'joinSession') {
    session_start();

    // todo check if session is available

    $_SESSION['transferSessionId'] = $data['transferSessionId'];
    $response['transferSessionId'] = $_SESSION['transferSessionId'];

    header('Content-Type: application/json');
    echo json_encode($response);
    return;
}

http_response_code(403);