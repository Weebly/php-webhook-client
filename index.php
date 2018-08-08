<?php

require_once __DIR__ . '/vendor/autoload.php';

// Only use the Dotenv lib on development environments
if('production' !== getenv('NODE_ENV')) {
	$dotenv = new Dotenv\Dotenv(__DIR__);
	$dotenv->load();
}

/**
 * Client ID and Secret need to get set outside of the codebase, expecting environment var to store it
 */
$client_id = getenv('WEEBLY_CLIENT_ID') ? getenv('WEEBLY_CLIENT_ID') : null;
$client_secret = getenv('WEEBLY_SECRET_KEY') ? getenv('WEEBLY_SECRET_KEY') : null;

if($client_id === null || $client_secret === null) {
	echo "Error: Env vars not set for application.";
	exit();
}

// Create Application instance
$app = new PHPWebhookClient\Application($client_id, $client_secret, __DIR__, $_SERVER['REQUEST_URI']);

// remove vars from global scope
unset($client_id);
unset($client_secret);

$app->run();
