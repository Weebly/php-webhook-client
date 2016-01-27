<?php

// get request data
$input = file_get_contents('php://input');

// decode request data
$request_data = json_decode($input);

// build comparison data for hmac validation
$comparison = [
    'client_id' => $request_data->client_id,
    'client_version' => $request_data->client_version,
    'event' => $request_data->event,
    'timestamp' => $request_data->timestamp,
    'data' => $request_data->data
];

// validate the request hmac
$verification_hash = hash_hmac('sha256', json_encode($comparison), $client_secret, false);
if($verification_hash != $request_data->hmac) {
    $data = "\nA new webhook was received, but it's calculated hmac didn't match what was passed.\n";

    // respond with an error HTTP header so the webhook queue knows to retry
    header('HTTP/1.1 401 HMAC Invalid');
    exit();
} else {
    $data = "\nA valid webhook was received:\n";
}

// build headers we want to track
$headers = [
    'Content-Length' => $_SERVER['CONTENT_LENGTH'],
    'Content-Type' => $_SERVER['CONTENT_TYPE'],
    'Accept' => $_SERVER['HTTP_ACCEPT'],
    'Host' => $_SERVER['HTTP_HOST'],
    'User-Agent' => $_SERVER['HTTP_USER_AGENT'],
    'X-Weebly-Attempt' => $_SERVER['HTTP_X_WEEBLY_ATTEMPT']
];

// add headers to output data
$data .= "Headers:\n".var_export($headers, true)."\n";

// append request data
$data .= "Data:\n".var_export($request_data, true)."\n";

// write data to messages file
file_put_contents($root_dir . '/messages/messages.txt', $data, FILE_APPEND);

// respond with 200 so webhook delivery isn't retried
header('HTTP/1.1 200 OK');