<?php

namespace PHPWebhookClient;

use Weebly\WeeblyClient;
use Util\HMAC;
use Util\MessageLog;

/**
 * Class Application
 * @package PHPWebhookClient
 *
 * Main PHP Webhook Client Application functionality
 */
class Application {

    /**
     * Instance of Message Log tied to messages.txt file
     */
    private $messageLog;

    /**
     * @var string $client_id
     */
    private $client_id;

    /**
     * @var string $client_secret
     */
    private $client_secret;

    /**
     * @var array Route Request
     */
    private $route;

    /**
     * @var array GET params
     */
    private $params;

    /**
     * Initialize the App information
     *
     * @param $client_id
     * @param $client_secret
     * @param $root_directory
     * @param $request
     */
    public function __construct($client_id, $client_secret, $root_directory, $request)
    {
        $this->messageLog = new MessageLog($root_directory . '/messages/messages.txt');
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->parseRequest($request);
    }

    /**
     * Route request
     */
    public function run() {
        // basic router
        switch($this->route[0]) {
            case '':
                // main url
                $this->appHome();
                break;

            case 'oauth':
                // oauth url
                switch($this->route[1]) {
                    case 'phase_one':
                        $this->oauthPhaseOne($this->params);
                        break;

                    case 'phase_two':
                        $this->oauthPhaseTwo($this->params);
                        break;
                };
                break;

            case 'webhooks':
                // webhook listener url
                switch($this->route[1]) {
                    case 'callback':
                        $this->processWebhook();
                        break;
                };
                break;


            default:
                // Unknown Request
                break;

        };
    }

    /**
     * Parse Route and GET Parameters from request
     *
     * @param $request Request URL after the host name
     */
    private function parseRequest($request) {
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
            list($route, $url_params) = explode('?', $request, 2);
            $route = explode('/', $route);
            $params = [];
            parse_str($url_params, $params);
        }

        /**
         * Route always starts with a / so first element will be blank
         *
         * remove initial element
         */
        $route = array_slice($route, 1);

        $this->route = $route;
        $this->params = $params;
    }

    /**
     * Handle the / route
     */
    private function appHome() {
        echo <<<HTML
<html>
    <head>
        <title>PHP Client App</title>
    </head>
    <body>
        <h2>PHP Client App</h2>
        <br />
        <a href="/messages/messages.txt">Download Log</a>
        <br />
        <h4>Webhook Output:</h4>
        <pre>

HTML;

        // output messages file to main page
        echo $this->messageLog->readLog();
        
        echo <<<HTML
        </pre>
    </body>
</html>
HTML;
    }

    /**
     * Handle the /oauth/phase_one route
     *
     * @param $params GET params passed via URL
     */
    private function oauthPhaseOne($params) {
        // validate hmac
        $hmac_params = array('user_id' => $params['user_id'], 'timestamp' => $params['timestamp']);
        if(isset($params['site_id'])) {
            $hmac_params['site_id'] = $params['site_id'];
        }

        if(HMAC::isHmacValid(http_build_query($hmac_params), $this->client_secret, $params['hmac']) === false) {
            echo "<h3>Unable to verify HMAC. Request is invalid.</h3>";
            exit();
        }

        // redirect to authorize endpoint
        $wc = new WeeblyClient($this->client_id, $this->client_secret, $params['user_id'], $params['site_id'], null);

        // build link for next step
        $phase_two_link = 'https://' . $_SERVER['HTTP_HOST'] . '/oauth/phase_two';

        // get OAuth URL
        $url = $wc->getAuthorizationUrl([], $phase_two_link, $params['callback_url']);

        // redirect to auth url
        header('location: ' . $url);
    }

    /**
     * Handle the /oauth/phase_two route
     *
     * @param $params GET params passed via URL
     */
    private function oauthPhaseTwo($params) {
        // build client instance
        $wc = new WeeblyClient($this->client_id, $this->client_secret, $params['user_id'], $params['site_id'], null);

        // get token from auth code
        $token = $wc->getAccessToken($params['authorization_code'], $params['callback_url']);

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
    }

    /**
     * Handle the /webhooks/callback route
     */
    private function processWebhook() {
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
        if(HMAC::isHmacValid(json_encode($comparison), $this->client_secret, $request_data->hmac) === false) {
            // record invalid attempt
            $data = "\nA new webhook was received, but it's calculated hmac didn't match what was passed.\n";
            $this->messageLog->writeLog($data);

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
        $this->messageLog->writeLog($data);

        // respond with 200 so webhook delivery isn't retried
        header('HTTP/1.1 200 OK');
    }
}