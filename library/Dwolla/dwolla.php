<?php

/**
 * Dwolla REST API Library for PHP
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * SUPPORT
 * Users should seek support on our API Support board located at:
 * https://discuss.dwolla.com/category/api-support
 *
 * @package   Dwolla
 * @author    Michael Schonfeld <michael@dwolla.com>
 * @copyright Copyright (c) 2012 Dwolla Inc. (http://www.dwolla.com)
 * @license   http://opensource.org/licenses/MIT MIT
 * @version   1.6.1
 * @link      http://www.dwolla.com
 */

if (!function_exists('curl_init')) {
    throw new Exception("Dwolla's API Client Library requires the CURL PHP extension.");
}

if (!function_exists('json_decode')) {
    throw new Exception("Dwolla's API Client Library requires the JSON PHP extension.");
}

if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = '';

        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }
}

class DwollaRestClient
{

    const API_SERVER = "https://www.dwolla.com/";
    const SANDBOX_SERVER = "https://uat.dwolla.com/";

    /**
     * @var string Dwolla API key
     */
    private $apiKey;
    /**
     * @var string Dwolla API key
     */
    private $apiSecret;
    /**
     * @var string oauth token
     */
    private $oauthToken;
    /**
     * @var array oauth authentication scopes
     */
    private $permissions;
    /**
     *
     * @var string URL to return the user to after the authentication request
     */
    private $redirectUri;
    /**
     * @var string Transaction mode. Can be 'live' or 'test'
     */
    private $mode;
    /**
     * @var array Off-Site Gateway order items
     */
    private $gatewaySession;
    /**
     * @var string error messages returned from Dwolla
     */
    private $errorMessage = false;
    /**
     * @var string operate in debug mode
     */
    private $debugMode = false;
    /**
     * @var bool use sandbox for API_SERVER
     */
    private $sandboxMode = false;
    /** @var string */
    private $OAUTH_TAIL = "oauth/rest/";

    /**
     * Sets the initial state of the client
     *
     * @param bool|string $apiKey
     * @param bool|string $apiSecret
     * @param bool|string $redirectUri
     * @param array $permissions
     * @param string $mode
     * @param bool $debugMode
     * @param bool $sandboxMode
     */
    public function __construct($apiKey = false, $apiSecret = false, $redirectUri = false, $permissions = array("send", "transactions", "balance", "request", "contacts", "accountinfofull", "funding"), $mode = 'live', $debugMode = false, $sandboxMode = false)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->redirectUri = $redirectUri;
        $this->permissions = $permissions;
        $this->apiServerUrl = $sandboxMode ? self::SANDBOX_SERVER : self::API_SERVER;
        $this->setMode($mode);
        $this->debugMode = $debugMode;
    }

    /**
     * Get oauth authentication URL
     *
     * @return string URL
     */
    public function getAuthUrl()
    {
        $params = array(
            'client_id' => $this->apiKey,
            'response_type' => 'code',
            'scope' => implode('|', $this->permissions)
        );

        // Only append a redirectURI if one was explicitly specified
        if ($this->redirectUri) {
            $params['redirect_uri'] = $this->redirectUri;
        }

        $url = $this->apiServerUrl . 'oauth/v2/authenticate?' . http_build_query($params);

        return $url;
    }

    /**
     * Request oauth token from Dwolla
     *
     * @param string $code Temporary code returned from Dwolla
     * @return string oauth token
     */
    public function requestToken($code)
    {
        if (!$code) {
            return $this->setError('Please pass an oauth code.');
        }

        $params = array(
            'client_id' => $this->apiKey,
            'client_secret' => $this->apiSecret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code',
            'code' => $code
        );
        $url = $this->apiServerUrl . 'oauth/v2/token?' . http_build_query($params);
        $response = $this->curl($url, 'GET');

        if (isset($response['error'])) {
            return $this->setError($response['error_description']);
        }

        if (!$response['access_token']) {
            return $this->setError($response['Message'] ? $response['Message'] : 'Failed to request token. No error message given. Use debug mode to find out more.');
        }

        return $response['access_token'];
    }

    /**
     * @param string $message Error message
     * @return bool
     */
    protected function setError($message)
    {
        $this->errorMessage = $message;

        return false;
    }

    /**
     * Execute curl request
     *
     * @param string $url URL to send requests
     * @param string $method HTTP method
     * @param array $params request params
     * @return array|null Returns array of results or null if json_decode fails
     */
    protected function curl($url, $method = 'GET', $params = array())
    {
        // Encode POST data
        $data = json_encode($params);

        // Set request headers
        $headers = array('Accept: application/json', 'Content-Type: application/json;charset=UTF-8');
        if ($method == 'POST') {
            $headers[] = 'Content-Length: ' . strlen($data);
        }

        // Set up our CURL request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Windows require this certificate
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $ca = dirname(__FILE__);
            curl_setopt($ch, CURLOPT_CAINFO, $ca); // Set the location of the CA-bundle
            curl_setopt($ch, CURLOPT_CAINFO, $ca . '/cacert.pem'); // Set the location of the CA-bundle
        }

        // Initiate request
        $rawData = curl_exec($ch);

        // If HTTP response wasn't 200,
        // log it as an error!
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($code !== 200) {
            if ($this->debugMode) {
                echo "Here is all the information we got from curl: \n";
                print_r(curl_getinfo($ch));
                print_r(curl_error($ch));
            }

            return array(
                'Success' => false,
                'Message' => "Request failed. Server responded with: {$code}"
            );
        }

        // All done with CURL
        curl_close($ch);

        // Otherwise, assume we got some
        // sort of a response
        return json_decode($rawData, true);
    }

    /**
     * Grabs the account information for the
     * authenticated user
     *
     * @return array Authenticated user's account information
     */
    public function me()
    {
        $response = $this->get('users/');
        return $this->parse($response);
    }

    /**
     * Executes GET requests against API
     *
     * @param string $request
     * @param array $params
     * @return array|null Array of results or null if json_decode fails in curl()
     */
    protected function get($request, $params = array())
    {
        $params['oauth_token'] = $this->oauthToken;

        $delimiter = (strpos($request, '?') === false) ? '?' : '&';
        $url = $this->apiServerUrl . $this->OAUTH_TAIL . $request . $delimiter . http_build_query($params);

        if ($this->debugMode) {
            echo "Getting request from: {$url} \n";
        }

        $rawData = $this->curl($url, 'GET');

        if ($this->debugMode) {
            echo "Got response:";
            print_r($rawData);
            echo "\n";
        }

        return $rawData;
    }

    /**
     * Parse Dwolla API response
     *
     * @param array $response
     * @return array
     */
    protected function parse($response)
    {
        if (!$response['Success']) {
            $this->setError($response['Message']);

            // Exception for /register method
            if ($response['Response']) {
                $this->errorMessage .= " :: " . json_encode($response['Response']);
            }

            return false;
        }

        return $response['Response'];
    }

    /**
     * Grabs the basic account information for
     * the provided Dwolla account Id
     *
     * @param string $userId Dwolla Account Id
     * @return array Basic account information
     */
    public function getUser($userId)
    {
        $params = array(
            'client_id' => $this->apiKey,
            'client_secret' => $this->apiSecret
        );

        $response = $this->get("users/{$userId}", $params);
        $user = $this->parse($response);

        return $user;
    }

    /**
     * Get a list of users nearby a
     * given geo location
     *
     * @param $lat
     * @param $long
     * @return array Users
     */
    public function usersNearby($lat, $long)
    {
        $params = array(
            'client_id' => $this->apiKey,
            'client_secret' => $this->apiSecret,
            'latitude' => $lat,
            'longitude' => $long
        );

        $response = $this->get("users/nearby", $params);
        $users = $this->parse($response);

        return $users;
    }

    /**
     * Register new Dwolla user
     *
     * @param string $email
     * @param string $password
     * @param int $pin
     * @param string $firstName
     * @param string $lastName
     * @param string $address
     * @param string $city
     * @param string $state
     * @param int $zip
     * @param string $phone
     * @param string $dateOfBirth
     * @param bool $acceptTerms
     * @param string $address2
     * @param string $type Dwolla account type
     * @param string $organization
     * @param string $ein
     * @return array New user information
     */
    public function register($email, $password, $pin, $firstName, $lastName, $address, $city, $state, $zip, $phone, $dateOfBirth, $acceptTerms, $address2 = '', $type = 'Personal', $organization = '', $ein = ''
    )
    {
        $params = array(
            'client_id' => $this->apiKey,
            'client_secret' => $this->apiSecret,
            'email' => $email,
            'password' => $password,
            'pin' => $pin,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'address' => $address,
            'address2' => $address2,
            'city' => $city,
            'state' => $state,
            'zip' => $zip,
            'phone' => $phone,
            'dateOfBirth' => $dateOfBirth,
            'type' => $type,
            'organization' => $organization,
            'ein' => $ein,
            'acceptTerms' => $acceptTerms
        );
        $response = $this->post('register/', $params, false); // false = don't include oAuth token

        $user = $this->parse($response);

        return $user;
    }

    /**
     * Executes POST request against API
     *
     * @param string $request
     * @param array|bool $params
     * @param bool $includeToken Include oauth token in request?
     * @return array|null
     */
    protected function post($request, $params = false, $includeToken = true)
    {
        $url = $this->apiServerUrl . $this->OAUTH_TAIL . $request . ($includeToken ? "?oauth_token=" . urlencode($this->oauthToken) : "");

        if ($this->debugMode) {
            echo "Posting request to: {$url} :: With params: \n";
            print_r($params);
        }

        $rawData = $this->curl($url, 'POST', $params);

        if ($this->debugMode) {
            echo "Got response:";
            print_r($rawData);
            echo "\n";
        }

        return $rawData;
    }

    /**
     * Search contacts
     *
     * @param bool|string $search Search term(s)
     * @param array $types Types of contacts (Dwolla, Facebook . . .)
     * @param int $limit Number of contacts to retrieve between 1 and 200.
     * @return array
     */
    public function contacts($search = false, $types = array('Dwolla'), $limit = 10)
    {
        $params = array(
            'search' => $search,
            'types' => implode(',', $types),
            'limit' => $limit
        );
        $response = $this->get('contacts', $params);

        $contacts = $this->parse($response);

        return $contacts;
    }

    /**
     * Use this method to retrieve nearby Dwolla spots within the range of the
     * provided latitude and longitude.
     *
     * Half of the limit are returned as spots with closest proximity. The other
     * half of the spots are returned as random spots within the range.
     * This call can return nearby venues on Foursquare but not Dwolla, they will have an Id of "null"
     *
     * @param float $latitude
     * @param float $longitude
     * @param int $range Range to search in miles
     * @param int $limit Limit search to this number results
     * @return array Search results
     */
    public function nearbyContacts($latitude, $longitude, $range = 10, $limit = 10)
    {
        $params = array(
            'latitude' => $latitude,
            'longitude' => $longitude,
            'limit' => $limit,
            'range' => $range,
            'client_id' => $this->apiKey,
            'client_secret' => $this->apiSecret,
        );

        $response = $this->get('contacts/nearby', $params);
        $contacts = $this->parse($response);

        return $contacts;
    }

    /**
     * Retrieve a list of verified funding sources for the user associated
     * with the authorized access token.
     *
     * @return array Funding Sources
     */
    public function fundingSources()
    {
        $response = $this->get('fundingsources');
        return $this->parse($response);
    }

    /**
     * Retrieve a verified funding source by identifier for the user associated
     * with the authorized access token.
     *
     * @param string $fundingSourceId Funding Source ID
     * @return array Funding Source Details
     */
    public function fundingSource($fundingSourceId)
    {
        $response = $this->get("fundingsources/{$fundingSourceId}");
        return $this->parse($response);
    }

    /**
     * Add a funding source for the user associated
     * with the authorized access token.
     *
     * @param $accountNumber
     * @param $routingNumber
     * @param $accountType
     * @param $accountName
     * @return array Funding Sources
     */
    public function addFundingSource($accountNumber, $routingNumber, $accountType, $accountName)
    {
        // Verify required parameters
        if (!$accountNumber) {
            return $this->setError('Please enter a bank account number.');
        } else if (!$routingNumber) {
            return $this->setError('Please enter a bank routing number.');
        } else if (!$accountType) {
            return $this->setError('Please enter an account type.');
        } else if (!$accountName) {
            return $this->setError('Please enter an account name.');
        }

        // Build request, and send it to Dwolla
        $params = array(
            'account_number' => $accountNumber,
            'routing_number' => $routingNumber,
            'account_type' => $accountType,
            'name' => $accountName
        );

        $response = $this->post('fundingsources/', $params);
        return $this->parse($response);
    }

    /**
     * Verify a funding source for the user associated
     * with the authorized access token.
     *
     * @param $fundingSourceId
     * @param $deposit1
     * @param $deposit2
     * @return array Funding Sources
     */
    public function verifyFundingSource($fundingSourceId, $deposit1, $deposit2)
    {
        // Verify required parameters
        if (!$deposit1) {
            return $this->setError('Please enter an amount for deposit1.');
        } else if (!$deposit2) {
            return $this->setError('Please enter an amount for deposit2.');
        } else if (!$fundingSourceId) {
            return $this->setError('Please enter a funding source ID.');
        }

        // Build request, and send it to Dwolla
        $params = array(
            'deposit1' => $deposit1,
            'deposit2' => $deposit2
        );

        $response = $this->post("fundingsources/{$fundingSourceId}/verify", $params);
        return $this->parse($response);
    }

    /**
     * Verify a funding source for the user associated
     * with the authorized access token.
     *
     * @param $fundingSourceId
     * @param $pin
     * @param $amount
     * @return array Funding Sources
     */
    public function withdraw($fundingSourceId, $pin, $amount)
    {
        // Verify required parameters
        if (!$pin) {
            return $this->setError('Please enter a PIN.');
        } else if (!$fundingSourceId) {
            return $this->setError('Please enter a funding source ID.');
        } else if (!$amount) {
            return $this->setError('Please enter an amount.');
        }

        // Build request, and send it to Dwolla
        $params = array(
            'pin' => $pin,
            'amount' => $amount
        );

        $response = $this->post("fundingsources/{$fundingSourceId}/withdraw", $params);
        return $this->parse($response);
    }

    /**
     * Verify a funding source for the user associated
     * with the authorized access token.
     *
     * @param $fundingSourceId
     * @param $pin
     * @param $amount
     * @return array Funding Sources
     */
    public function deposit($fundingSourceId, $pin, $amount)
    {
        // Verify required parameters
        if (!$pin) {
            return $this->setError('Please enter a PIN.');
        } else if (!$fundingSourceId) {
            return $this->setError('Please enter a funding source ID.');
        } else if (!$amount) {
            return $this->setError('Please enter an amount.');
        }

        // Build request, and send it to Dwolla
        $params = array(
            'pin' => $pin,
            'amount' => $amount
        );

        $response = $this->post("fundingsources/{$fundingSourceId}/deposit", $params);
        return $this->parse($response);
    }

    /**
     * Retrieve the account balance for the user with the given authorized
     * access token.
     *
     * @return float Balance in USD
     */
    public function balance()
    {
        $response = $this->get('balance/');
        return $this->parse($response);
    }

    /**
     * Send funds to a destination user from the user associated with the
     * authorized access token.
     * @param string $destinationId Dwolla identifier, Facebook identifier, Twitter identifier, phone number, or email address
     * @param float $amount Amount of funds to transfer to the destination user.
     * @param string $firstName The source bank account holder's first name
     * @param string $lastName The source bank account holder's last name
     * @param string $email The source bank account holder's email address
     * @param int $routingNumber The source bank routing number
     * @param int $accountNumber The source bank account number
     * @param string $accountType "Checking" or "Savings"
     *
     * The following are NON-essential params: *
     * @param bool $assumeCosts defaults to false
     * @param string $destinationType Type of destination user. Can be Dwolla, Facebook, Twitter, Email, or Phone. Defaults to Dwolla.
     * @param string $notes Note to attach to the transaction. Limited to 250 characters.
     * @param bool|int $groupId ID specified by the client application, to be associated with the transaction. May be used to group transactions by the given ID. Note: Transactions can be polled by their given 'groupId' using the transactions/Listing method.
     * @param array|bool $additionalFees Array of Additional facilitator fees, array('destinationId'=Facilitator's Dwolla ID,  'amount'=Faciliator Amount)
     * @param float $facilitatorAmount facilitatorAmount    Amount of the facilitator fee to override. Only applicable if the facilitator fee feature is enabled. If set to 0, facilitator fee is disabled for transaction. Cannot exceed 50% of the 'amount'.
     * @param bool $assumeAdditionalFees assumeAdditionalFees     Set to true if the sending user will assume all Facilitator fees. Set to false if the destination user will assume all Facilitator fees. Does not affect the Dwolla fee.
     * @return string Transaction Id
     */

    public function guestSend($destinationId, $amount, $firstName, $lastName, $email, $routingNumber, $accountNumber, $accountType, $assumeCosts = false, $destinationType = 'Dwolla', $notes = '', $groupId = false, $additionalFees = false, $facilitatorAmount = NULL, $assumeAdditionalFees = false)
    {
        // Verify required parameters
        if (!$destinationId) {
            return $this->setError('Please enter a destination ID.');
        } else if (!$amount) {
            return $this->setError('Please enter a transaction amount.');
        } else if (!$firstName) {
            return $this->setError('Please enter a destination ID.');
        } else if (!$lastName) {
            return $this->setError('Please enter a transaction amount.');
        } else if (!$email) {
            return $this->setError('Please enter a destination ID.');
        } else if (!$routingNumber) {
            return $this->setError('Please enter a transaction amount.');
        } else if (!$accountNumber) {
            return $this->setError('Please enter a destination ID.');
        } else if (!$accountType) {
            return $this->setError('Please enter a transaction amount.');
        }

        // Build request, and send it to Dwolla
        $params = array(
            'destinationId' => $destinationId,
            'amount' => $amount,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'routingNumber' => $routingNumber,
            'accountNumber' => $accountNumber,
            'accountType' => $accountType,
            'assumeCosts' => $assumeCosts,
            'destinationType' => $destinationType,
            'notes' => $notes,
            'groupId' => $groupId,
            'additionalFees' => $additionalFees,
            'facilitatorAmount' => $facilitatorAmount,
            'assumeAdditionalFees' => $assumeAdditionalFees
        );
        $response = $this->post('transactions/guestsend', $params);

        // Parse Dwolla's response
        $transactionId = $this->parse($response);

        return $transactionId;
    }

    /**
     * Send funds to a destination user from the user associated with the
     * authorized access token.
     *
     * @param bool|int $pin
     * @param bool|string $destinationId Dwolla identifier, Facebook identifier, Twitter identifier, phone number, or email address
     * @param bool|float $amount
     * @param string $destinationType Type of destination user. Can be Dwolla, Facebook, Twitter, Email, or Phone. Defaults to Dwolla.
     * @param string $notes Note to attach to the transaction. Limited to 250 characters.
     * @param float $facilitatorAmount
     * @param bool $assumeCosts Will sending user assume the Dwolla fee?
     * @param string $fundsSource Funding source ID to use. Defaults to Dwolla balance.
     * @param array|bool $additionalFees JSON array of facilitator fee objects ({$destinationId, $amount}
     * ).
     * @param bool $assumeAdditionalFees Determines whether or not the sender assumes the facilitator fees (false by default)
     * @return string Transaction Id
     */
    public function send($pin = false, $destinationId = false, $amount = false, $destinationType = 'Dwolla', $notes = '', $facilitatorAmount = NULL, $assumeCosts = false, $fundsSource = 'balance', $additionalFees = FALSE, $assumeAdditionalFees = FALSE)
    {
        // Verify required parameters
        if (!$pin) {
            return $this->setError('Please enter a PIN.');
        } else if (!$destinationId) {
            return $this->setError('Please enter a destination ID.');
        } else if (!$amount) {
            return $this->setError('Please enter a transaction amount.');
        }

        // Build request, and send it to Dwolla
        $params = array(
            'pin' => $pin,
            'destinationId' => $destinationId,
            'destinationType' => $destinationType,
            'amount' => $amount,
            'facilitatorAmount' => $facilitatorAmount,
            'assumeCosts' => $assumeCosts,
            'notes' => $notes,
            'fundsSource' => $fundsSource,
        );
        if ($additionalFees) {
            $params['additionalFees'] = $additionalFees;
        }
        if ($assumeAdditionalFees) {
            $params['assumeAdditionalFees'] = $assumeAdditionalFees;
        }

        $response = $this->post('transactions/send', $params);

        // Parse Dwolla's response
        $transactionId = $this->parse($response);

        return $transactionId;
    }

    /**
     * Request funds from a source user, originating from the user associated
     * with the authorized access token.
     *
     * @param bool|string $sourceId
     * @param bool|float $amount
     * @param string $sourceType
     * @param string $notes
     * @param float $facilitatorAmount
     * @return int Request Id
     */
    public function request($sourceId = false, $amount = false, $sourceType = 'Dwolla', $notes = '', $facilitatorAmount = NULL)
    {
        // Verify required parameters
        if (!$sourceId) {
            return $this->setError('Please enter a source ID.');
        } else if (!$amount) {
            return $this->setError('Please enter a transaction amount.');
        }

        // Build request, and send it to Dwolla
        $params = array(
            'sourceId' => $sourceId,
            'sourceType' => $sourceType,
            'amount' => $amount,
            'facilitatorAmount' => $facilitatorAmount,
            'notes' => $notes
        );
        $response = $this->post('requests/', $params);

        // Parse Dwolla's response
        $transactionId = $this->parse($response);

        return $transactionId;
    }

    /**
     * Get a request by its ID
     * @param $requestId
     * @return array Request with the given ID
     */
    public function requestById($requestId)
    {
        // Verify required parameters
        if (!$requestId) {
            return $this->setError('Please enter a request ID.');
        }

        // Build request, and send it to Dwolla
        $response = $this->get("requests/{$requestId}");

        // Parse Dwolla's response
        $request = $this->parse($response);

        return $request;
    }

    /**
     * Fulfill (:send) a pending payment request
     * @param $requestId
     * @param $pin
     * @param bool $amount
     * @param bool $notes
     * @param bool $fundsSource
     * @param bool $assumeCosts
     * @return array Transaction information
     */
    public function fulfillRequest($requestId, $pin, $amount = false, $notes = false, $fundsSource = false, $assumeCosts = false)
    {
        // Verify required parameters
        if (!$pin) {
            return $this->setError('Please enter a PIN.');
        } else if (!$requestId) {
            return $this->setError('Please enter a request ID.');
        }

        // Build request, and send it to Dwolla
        $params = array(
            'pin' => $pin
        );
        if ($amount) {
            $params['amount'] = $amount;
        }
        if ($notes) {
            $params['notes'] = $notes;
        }
        if ($fundsSource) {
            $params['fundsSource'] = $fundsSource;
        }
        if ($assumeCosts) {
            $params['assumeCosts'] = $assumeCosts;
        }

        $response = $this->post("requests/{$requestId}/fulfill", $params);
        return $this->parse($response);
    }

    /**
     * Cancel (:reject) a pending payment request
     * @param $requestId
     * @return array Transaction information
     */
    public function cancelRequest($requestId)
    {
        // Verify required parameters
        if (!$requestId) {
            return $this->setError('Please enter a request ID.');
        }

        $response = $this->post("requests/{$requestId}/cancel", array());
        return $this->parse($response);
    }

    /**
     * Get a list of pending money requests
     * @return array Pending Requests
     */
    public function requests()
    {
        // Build request, and send it to Dwolla
        $response = $this->get("requests/");

        // Parse Dwolla's response
        $requests = $this->parse($response);

        return $requests;
    }

    /**
     * Grab information for the given transaction ID with
     * app credentials (instead of oauth token)
     *
     * @param int $transactionId Transaction ID to which information is pulled
     * @return array Transaction information
     */
    public function transaction($transactionId)
    {
        // Verify required parameters
        if (!$transactionId) {
            return $this->setError('Please enter a transaction ID.');
        }

        $params = array(
            'client_id' => $this->apiKey,
            'client_secret' => $this->apiSecret
        );

        // Build request, and send it to Dwolla
        $response = $this->get("transactions/{$transactionId}", $params);

        // Parse Dwolla's response
        $transaction = $this->parse($response);

        return $transaction;
    }

    /**
     * Verify a signature that came back
     * with an offsite gateway redirect
     *
     * @param {string} Proposed signature; (required)
     * @param {string} Dwolla's checkout ID; (required)
     * @param {string} Dwolla's reported total amount; (required)
     *
     * @return {boolean} Whether or not the signature is valid
     */

    /**
     * Retrieve a list of transactions for the user associated with the
     * authorized access token.
     *
     * @param bool|string $sinceDate Earliest date and time for which to retrieve transactions.
     *        Defaults to 7 days prior to current date and time in UTC. Format: DD-MM-YYYY
     * @param array|bool $types Types of transactions to retrieve.  Options are money_sent, money_received, deposit, withdrawal, and fee.
     * @param int $limit Number of transactions to retrieve between 1 and 200
     * @param int $skip Number of transactions to skip
     * @return array Transaction search results
     */
    public function listings($sinceDate = false, $types = false, $limit = 10, $skip = 0)
    {
        $params = array(
            'limit' => $limit,
            'skip' => $skip
        );

        if ($sinceDate) {
            $params['sinceDate'] = $sinceDate;
        }
        if ($types) {
            $params['types'] = implode(',', $types);
        }

        // Build request, and send it to Dwolla
        $response = $this->get("transactions", $params);

        // Parse Dwolla's response
        $listings = $this->parse($response);

        return $listings;
    }

    /**
     * Retrieve transactions stats for the user associated with the authorized
     * access token.
     *
     * @param array $types Options are 'TransactionsCount' and 'TransactionsTotal'
     * @param string $startDate Starting date and time to for which to process transactions stats. Defaults to 0300 of the current day in UTC.
     * @param string $endDate Ending date and time to for which to process transactions stats. Defaults to current date and time in UTC.
     * @return array Transaction stats search results
     */
    public function stats($types = array('TransactionsCount', 'TransactionsTotal'), $startDate = null, $endDate = null)
    {
        $params = array(
            'types' => implode(',', $types),
            'startDate' => $startDate,
            'endDate' => $endDate
        );

        // Build request, and send it to Dwolla
        $response = $this->get("transactions/stats", $params);

        // Parse Dwolla's response
        $stats = $this->parse($response);

        return $stats;
    }

    /**
     * Creates an empty Off-Site Gateway Order Items array
     */
    public function startGatewaySession()
    {
        $this->gatewaySession = array();
    }

    /**
     * Adds new order item to gateway session
     *
     * @param string $name
     * @param float $price Item price in USD
     * @param int $quantity Number of items
     * @param string $description Item description
     */
    public function addGatewayProduct($name, $price, $quantity = 1, $description = '')
    {
        $product = array(
            'Name' => $name,
            'Description' => $description,
            'Price' => $price,
            'Quantity' => $quantity
        );

        $this->gatewaySession[] = $product;
    }

    /**
     * Creates and executes Server-to-Server checkout request
     * @link http://developers.dwolla.com/dev/docs/gateway#server-to-server
     *
     * @param string $destinationId
     * @param string $orderId
     * @param float|int $discount
     * @param float|int $shipping
     * @param float|int $tax
     * @param string $notes
     * @param string $callback
     * @param boolean $allowFundingSources
     * @param boolean $allowGuestCheckout
     * @param string $additionalFundingSources
     * @return string Checkout URL
     */
    public function getGatewayURL($destinationId, $orderId = null, $discount = 0, $shipping = 0, $tax = 0, $notes = '', $callback = null, $allowFundingSources = TRUE, $allowGuestCheckout = TRUE, $additionalFundingSources = 'true')
    {
        // TODO add validation? Throw exception if malformed?
        $destinationId = $this->parseDwollaID($destinationId);

        // Normalize optional parameters
        if (!$shipping) {
            $shipping = 0;
        } else {
            $shipping = floatval($shipping);
        }
        if (!$tax) {
            $tax = 0;
        } else {
            $tax = floatval($tax);
        }
        if (!$discount) {
            $discount = 0;
        } else {
            $discount = abs(floatval($discount));
        }
        if (!$notes) {
            $notes = '';
        }

        // Calculate subtotal
        $subtotal = 0;

        foreach ($this->gatewaySession as $product) {
            $subtotal += floatval($product['Price']) * floatval($product['Quantity']);
        }

        // Calculate grand total
        $total = round($subtotal - $discount + $shipping + $tax, 2);

        // Create request body
        $request = array(
            'Key' => $this->apiKey,
            'Secret' => $this->apiSecret,
            'Test' => ($this->mode == 'test') ? 'true' : 'false',
            'AdditionalFundingSources' => $additionalFundingSources,
            'AllowGuestCheckout' => $allowGuestCheckout ? 'true' : 'false',
            'AllowFundingSources' => $allowFundingSources ? 'true' : 'false',
            'PurchaseOrder' => array(
                'DestinationId' => $destinationId,
                'OrderItems' => $this->gatewaySession,
                'Discount' => -$discount,
                'Shipping' => $shipping,
                'Tax' => $tax,
                'Total' => $total,
                'Notes' => $notes
            )
        );

        // Append optional parameters
        if ($this->redirectUri) {
            $request['Redirect'] = $this->redirectUri;
        }

        if ($callback) {
            $request['Callback'] = $callback;
        }

        if ($orderId) {
            $request['OrderId'] = $orderId;
        }

        // Send off the request
        $response = $this->curl(($this->apiServerUrl . 'payment/request/'), 'POST', $request);

        if ($response['Result'] != 'Success') {
            $this->setError($response['Message']);
        }

        return ($this->apiServerUrl . 'payment/checkout/' . $response['CheckoutId']);
    }

    /**
     * Returns properly formatted Dwolla Id
     *
     * @param string|int $id
     * @return string Properly formatted Dwolla Id
     */
    public function parseDwollaID($id)
    {
        $id = preg_replace("/[^0-9]/", "", $id);
        $id = preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1-$2-$3", $id);

        return $id;
    }

    /**
     * Verify the signature returned from Offsite-Gateway Redirect
     *
     * @param bool|string $signature
     * @param bool|string $checkoutId
     * @param bool|float $amount
     * @return bool Is signature valid?
     */
    public function verifyGatewaySignature($signature = false, $checkoutId = false, $amount = false)
    {
        // Verify required parameters
        if (!$signature) {
            return $this->setError('Please pass a proposed signature.');
        }
        if (!$checkoutId) {
            return $this->setError('Please pass a checkout ID.');
        }
        if (!$amount) {
            return $this->setError('Please pass a total transaction amount.');
        }
        $amount = number_format($amount, 2);
        // Calculate an HMAC-SHA1 hexadecimal hash
        // of the checkoutId and amount ampersand separated
        // using the consumer secret of the application
        // as the hash key.
        //
        // @doc: http://developers.dwolla.com/dev/docs/gateway
        $hash = hash_hmac("sha1", "{$checkoutId}&{$amount}", $this->apiSecret);

        if ($hash !== $signature) {
            return $this->setError('Dwolla signature verification failed.');
        }

        return TRUE;
    }

    /**
     * Verify the signature returned from Webhook notifications
     *
     * @return bool Is signature valid?
     */
    public function verifyWebhookSignature()
    {
        // 1. Get the request body
        $body = file_get_contents('php://input');

        // 2. Get Dwolla's signature
        $headers = getallheaders();
        $signature = $headers['X-Dwolla-Signature'];

        // 3. Calculate hash, and compare to the signature
        $hash = hash_hmac('sha1', $body, $this->apiSecret);
        $validated = ($hash == $signature);

        if (!$validated) {
            return $this->setError('Dwolla signature verification failed.');
        }

        return TRUE;
    }

    /**
     * Create a MassPay job
     *
     * @param string $pin User's account PIN
     * @param array $filedata Array of Item JSON objects
     * @param bool|string $fundsSource Funding source ID of the desired funcding source from which payments will be processed from (if no value is set, it defaults to 'Balance')
     * @param bool $assumeCosts Set to true if the sender should assume the $0.25 Dwolla fee rather than the recipients (default: false)
     * @param bool|string $user_job_id Optional user defined job ID.
     * @return array Information on submitted job (view full obj "Response" details here: https://developers.dwolla.com/dev/docs/masspay/create)
     */

    public function massPayCreate($pin, $filedata, $fundsSource = FALSE, $assumeCosts = FALSE, $user_job_id = FALSE)
    {
        if (!$pin) {
            return $this->setError('Please enter a PIN.');
        } else if (!$filedata) {
            return $this->setError('Please pass the MassPay bulk data.');
        }

        // Create request body
        $params = array(
            'oauth_token' => $this->oauthToken,
            'fundsSource' => ($fundsSource) ? $fundsSource : 'Balance',
            'pin' => $pin,
            'items' => $filedata,
            'assumeCosts' => $assumeCosts
        );
        if ($user_job_id) {
            $params['userJobId'] = $user_job_id;
        }

        // Send off the request
        $response = $this->curl($this->apiServerUrl . $this->OAUTH_TAIL . 'masspay/', 'POST', $params);

        $job = $this->parseMassPay($response);

        return $job;
    }

    /**
     * Parse MassPay API response
     *
     * @param array $response
     * @return array
     */
    protected function parseMassPay($response)
    {
        $success = isset($response['Success']) ? $response['Success'] : $response['success'];
        if (!$success) {
            $message = isset($response['Message']) ? $response['Message'] : $response['message'];
            return $this->setError($message);
        }
        return $response['Response'];
    }

    /**
     * massPayDetails
     *
     * @param string $job_id The job ID of the desired MassPay job. This can either be the generated MP job id or a user-specified one.
     * @return array $response Contains details on the job tied to the passed in ID.
     */

    public function massPayDetails($job_id)
    {
        if (!$job_id) {
            return $this->setError('Please pass either a MassPay job ID, or a user assigned job ID.');
        }

        // Create request body
        $params = array(
            'oauth_token' => $this->oauthToken,
            'id' => $job_id
        );

        // Send off the request
        $response = $this->curl($this->apiServerUrl . $this->OAUTH_TAIL . 'masspay/job/', 'POST', $params);

        if (!$response['Success']) {
            $this->errorMessage = $response['Message'];
        }
        return $response['Response'];
    }

    /**
     * @return string|bool Error message or false if error message does not exist
     */
    public function getError()
    {
        if (!$this->errorMessage) {
            return false;
        }

        $error = $this->errorMessage;
        $this->errorMessage = false;

        return $error;
    }

    /**
     * @param string $token oauth token
     */
    public function setToken($token)
    {
        $this->oauthToken = $token;
    }

    /**
     * @return string oauth token
     */
    public function getToken()
    {
        return $this->oauthToken;
    }

    /**
     * @return string Client mode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Sets client mode.  Appropriate values are 'live' and 'test'
     *
     * @param string $mode
     * @throws InvalidArgumentException
     * @return void
     */
    public function setMode($mode = 'live')
    {
        $mode = strtolower($mode);

        if ($mode != 'live' && $mode != 'test') {
            throw new InvalidArgumentException('Appropriate mode values are live or test');
        }

        $this->mode = $mode;
    }

    /**
     * Set debug mode
     *
     * @param $mode
     * @return boolean True
     */
    public function setDebug($mode)
    {
        $this->debugMode = $mode;

        return true;
    }

    /**
     * Set sandbox mode
     *
     * @param $mode
     * @return boolean True
     */
    public function setSandbox($mode)
    {
        $this->sandboxMode = $mode;
        $this->apiServerUrl = $this->sandboxMode ? self::SANDBOX_SERVER : self::API_SERVER;
        return true;
    }

}
