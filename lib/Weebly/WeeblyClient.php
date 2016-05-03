<?php
/**
 * WeeblyClient for using Weebly as an OAuth provider, and talking to Weebly APIs
 *
 * @package Weebly
 * @author Bryan Ashley <bryan@weebly.com>
 * @since 2015-03-30
 */

namespace Weebly;

class WeeblyClient 
{
    /**
     * Weebly domain
     */
    const WEEBLY_DOMAIN = 'https://www.weebly.com';

    /**
     * Weebly API domain
     */
    const WEEBLY_API_DOMAIN = 'https://api.weebly.com/v1';

    /**
     * Weebly User Id
     *
     * @var $user_id
     */
    public $user_id;

    /**
     * Weebly Site Id
     *
     * @var $site_id
     */
    public $site_id;

    /**
     * Weebly User's API Access token
     *
     * @var $access_token
     */
    private $access_token;

    /**
     * Application Client Id
     *
     * @var $client_id
     */
    private $client_id;

    /**
     * Application Client Secret
     *
     * @var $client_secret
     */
    private $client_secret;

    /**
     * Default Curl Options
     *
     * @var $default_curl_options
     */
    private $default_curl_options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'weebly/weebly_client'
    );

    /**
     * Cached Curl Handler
     *
     * @var $curl_handler
     */
    private $curl_handler;


    /**
     * Creates a new API interaction object.
     *
     * @param string $client_id                Your application client_id
     * @param string $client_secret            Your application client_secret
     * @param (optional) int $user_id          The Weebly User Id
     * @param (optional) int $site_id          The Weebly Site Id
     * @param (optional) string $access_token  The Weebly User's API access token used for accessing
     *                                         data from already permitted users
     *
     * @return instance
     */
    public function __construct($client_id, $client_secret, $user_id=null, $site_id=null, $access_token=null)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->user_id = $user_id;
        $this->site_id = $site_id;
        $this->access_token = $access_token;
    }

    /**
     * Returns the url to redirect to for oauth authentication (Step 1 in OAuth flow)
     *
     * @param (optional) array $scope           An array of the permissions your application is 
     *                                          requesting i.e (read:user, read:commerce)
     * @param (optional) string $redirect_uri   The url weebly will redirect to upon user's grant of 
     *                                          permissions. Defaults to application callback url
     * @param (optional) string $callback_url   The url provided by weebly to initiate the authorize
     *                                          step of the oauth process
     *
     *
     * @return string $authorization_url
     */
    public function getAuthorizationUrl($scope=array(), $redirect_uri=null, $callback_url=null)
    {
        if (isset($callback_url) === true) {
            $authorization_url = $callback_url;
        } else {
            $authorization_url = self::WEEBLY_DOMAIN.'/app-center/oauth/authorize';
        }

        $parameters = '?client_id='.$this->client_id.'&user_id='.$this->user_id;

        if (isset($this->site_id) === true) {
            $parameters .= '&site_id='.$this->site_id;
        }

        if (isset($redirect_uri) === true) {
            $parameters .= '&redirect_uri='.$redirect_uri;
        }

        if (is_array($scope) === true && count($scope) > 0) {
            $scope_parameters = implode(',', $scope);
            $parameters .= '&scope='.$scope_parameters;
        }

        return $authorization_url.$parameters;
    }

    /**
     * Makes authenticated API GET Request using provided URL, and parameters
     *
     * @param string $url
     * @param array $parameters
     * @return json $result
     */
    public function get($url, $parameters=[])
    {
        return $this->makeRequest(self::WEEBLY_API_DOMAIN.$url, $parameters, 'GET');
    }

    /**
     * Makes authenticated API POST Request using provided URL, and parameters
     *
     * @param string $url
     * @param array $parameters
     * @return json $result
     */
    public function post($url, $parameters=[])
    {
        return $this->makeRequest(self::WEEBLY_API_DOMAIN.$url, $parameters, 'POST');
    }

    /**
     * Makes authenticated API PATCH Request using provided URL, and parameters
     *
     * @param string $url
     * @param array $parameters
     * @return json $result
     */
    public function patch($url, $parameters=[])
    {
        return $this->makeRequest(self::WEEBLY_API_DOMAIN.$url, $parameters, 'PATCH');
    }

    /**
     * Makes authenticated API PUT Request using provided URL, and parameters
     *
     * @param string $url
     * @param array $parameters
     * @return json $result
     */
    public function put($url, $parameters=[])
    {
        return $this->makeRequest(self::WEEBLY_API_DOMAIN.$url, $parameters, 'PUT');
    }

    /**
     * Exchanges a temporary authorization code for a permanent access_token
     *
     * @param string $authorization_code        The authorization_code sent from weebly after the user has
     *                                          granted the application access to their data.
     * @param (optional) string $callback_url   The url provided by weebly to retrieve the access token
     *
     *
     * @return object       object contains the access_token and callback_url for the finish
     */
    public function getAccessToken($authorization_code, $callback_url=null)
    {
        if (isset($callback_url) === true) {
            $url = $callback_url;
        } else {
            $url = self::WEEBLY_DOMAIN.'/app-center/oauth/access_token';
        }

        $result = $this->makeRequest($url, $this->prepareAccessTokenParams($authorization_code));
        return $result;
    }

    /**
     * Returns an array of the parameters required for retrieving a weebly access token for a user
     *
     * @param string $authorization_code
     * @return array $params
     */
    private function prepareAccessTokenParams($authorization_code)
    {
        $params = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'authorization_code' => $authorization_code
        );
        return $params;
    }

    /**
     * Internal fucntion used for making curl requests to api
     *
     * @param string $url                    URL to make request to
     * @param (optional) array $paramenters  Array of parameters to pass
     * @param (optional) string $method      HTTP method, defaults to 'POST'
     *
     * @return array $response
     */
    private function makeRequest($url, $parameters=array(), $method='POST')
    {
        $curl_handler = $this->getCurlHandler();

        if ($method === 'POST') {
            $options = array(
                CURLOPT_POSTFIELDS => json_encode($parameters),
                CURLOPT_POST => $method === 'POST'
            );
        } else if ($method !== 'GET'){
            $options = array(
                CURLOPT_CUSTOMREQUEST, $method,
                CURLOPT_POSTFIELDS => json_encode($parameters)
            );
        }

        if ($this->access_token) {
            $header = array();
            $header[] = 'Content-type: application/json';
            $header[] = 'X-Weebly-Access-Token: '.$this->access_token;
            $options[CURLOPT_HTTPHEADER] = $header;
        }

        $options[CURLOPT_URL] = $url;

        curl_setopt_array($curl_handler, $this->default_curl_options + $options);
        $result = curl_exec($curl_handler);
        return json_decode($result);
    }

    /**
     * Retrieves the running instance of a curl handler if one exists; otherwise creates one.
     *
     * @return resource $this->curl_handler
     */
    private function getCurlHandler()
    {
        if (isset($this->curl_handler) === false) {
            $this->curl_handler = curl_init();
        }

        return $this->curl_handler;
    }
}
