<?php

require_once __DIR__ . '/vendor/autoload.php';

// Route comes in as /1/2/3/4
$request =  $_SERVER['REQUEST_URI'];

/**
 * Client ID and Secret need to get set outside of the codebase, preverably in an ENV
 */
$client_id = getenv('WEEBLY_CLIENT_ID') ? getenv('WEEBLY_CLIENT_ID') : null;
$client_secret = getenv('WEEBLY_CLIENT_SECRET') ? getenv('WEEBLY_CLIENT_SECRET') : null;

if($client_id === null || $client_secret === null) {
	echo "Error: Env vars not set for application.";
	exit();
}

// store root directory for file access
$root_dir = __DIR__;

/**
 * URL parsing
 *
 * break the Request URL into route sections and GET parameters
 */
if(stripos($request,'?') === false) {
	// no params
	$route = explode('/', $request);
	$params = [];
} else {
	// params included
	list($route, $params) = explode('?', $request);
	$route = explode('/', $route);
	$params = explode('&', $params);
}

/**
 * Route always starts with a / so first element will be blank
 *
 * remove initial element
 */
$route = array_slice($route, 1);

// basic router
switch($route[0]) {
	case '':
		// main url
		include('app/main.php');
		break;

	case 'oauth':
		// oauth url
		switch($route[1]) {
			case 'phase_one':
				include('app/start_oauth.php');
				break;

			case 'phase_two':
				include('app/finish_oauth.php');
				break;
		};
		break;

	case 'webhooks':
		// webhook listener url
		switch($route[1]) {
			case 'callback':
				include('app/webhook.php');
				break;
		};
		break;


	default:
		// Unknown Request
		break;

};
