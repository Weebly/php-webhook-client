<?php

use Weebly\WeeblyClient;

// validate hmac
$hmac_params = array('user_id' => $_GET['user_id'], 'timestamp' => $_GET['timestamp']);
if(isset($_GET['site_id'])) {
    $hmac_params['site_id'] = $_GET['site_id'];
}

$verification_hash = hash_hmac('sha256', http_build_query($hmac_params), $client_secret, false);
if($verification_hash != $_GET['hmac']) {
    echo "<h3>Unable to verify HMAC. Request is invalid.</h3>";
    exit();
}

// redirect to authorize endpoint
$wc = new WeeblyClient($client_id, $client_secret, $_GET['user_id'], $_GET['site_id'], null);

// build link for next step
$phase_two_link = 'https://' . $_SERVER['HTTP_HOST'] . '/oauth/phase_two';

// get OAuth URL
$url = $wc->getAuthorizationUrl(['webhooks'], $phase_two_link, $_GET['callback_url']);

// redirect to auth url
header('location: ' . $url);