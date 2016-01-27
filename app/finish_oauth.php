<?php

use Weebly\WeeblyClient;

// build client instance
$wc = new WeeblyClient($client_id, $client_secret, $_GET['user_id'], $_GET['site_id'], null);

// get token from auth code
$token = $wc->getAccessToken($_GET['authorization_code'], $_GET['callback_url']);

// check for a valid return
if($token->access_token !== null) {
    // token retrieved

    /**
     * Note: If you were going to make API calls on behalf of your application,
     * this is where you would want to store the access_token for reuse later.
     *
     * TODO: Store access_token for user/site
     */

    // redirect to final endpoint
    header("location: {$token->callback_url}");
} else {
    // unable to retrieve access_token

    // display error from server
    if($token->error !== null) {
        echo "<h3>Error: " . $token->error . "</h3>";
    } else {
        echo "<h3>Error: Unable to get Access Token</h3>";
    }
}
