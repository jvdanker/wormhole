<?php

$url = $_REQUEST['url'];
if (!empty($url)) {
    $parts = explode('/', $url);
    if (count($parts) !== 2) {
        http_response_code(403);
    }
    $method = 'download';

} else {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data)) {
        http_response_code(403);
    }

    $method = $data['method'];
}

if ($method === 'reset') {
    // Unset all of the session variables.
    $_SESSION = array();

    // If it's desired to kill the session, also delete the session cookie.
    // Note: This will destroy the session, and not just the session data!
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Finally, destroy the session.
    session_destroy();

    echo "ok";
    return;
}

function filterJson($item) {
    return !preg_match("/^.*\.json$/i", $item);
}

if ($method === 'startSession') {
    session_start();

    $response = [];

    header('Content-Type: application/json');
    echo json_encode($response);
    return;
}

if ($method === 'download') {
    session_start();

    $channelId = $parts[0];
    $fileId = $parts[1];

    header('Content-Disposition: attachment; filename="' . $fileId . '""');
    header("Pragma: public");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

    $filename = sprintf("/uploads/%s/%s", $channelId, $fileId);

    set_time_limit(0);
    $file = @fopen($filename,"rb");
    while(!feof($file)) {
        print(@fread($file, 1024*8));
        ob_flush();
        flush();
        if (connection_status()!=0) {
            @fclose($file);
            exit;
        }
    }

    // file save was a success
    @fclose($file);
    return;
}

http_response_code(403);