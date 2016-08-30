<?php

/**
 * @see Zend_Service_ShortUrl_AbstractShortener
 */
require_once 'Zend/Service/ShortUrl/AbstractShortener.php';

/**
 * bitly.com API implementation
 *
 * @category   Zend
 * @package    Zend_Service_ShortUrl
 * @copyright  Copyright (c) 2011 Björn Schramke (http://www.schramke-online.de)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class CB_Service_ShortUrl_BitlyCom extends Zend_Service_ShortUrl_AbstractShortener
{
    /**
     * Base URI of the service
     *
     * @var string
     */
    protected $_baseUri = 'http://bit.ly';
    protected $_apiBaseUri = 'http://api.bitly.com';

    /**
     * The bitly.com login
     *
     * @var string
     */
    protected $_login = null;

    /**
     * The bitly.com API-Key
     *
     * @var string
     */
    protected $_apiKey = null;

    /**
     * The default response-format for bitly.com-calls
     *
     * @var string ( txt | xml | json )
     */
    protected $_format = 'txt';

    public function __construct($login,$apiKey,$format='txt') 
    {
        $this->setLogin($login);
        $this->setApiKey($apiKey);
        $this->setFormat($format);
    }
    
    /**
     * This function shortens long url
     *
     * @param string $url URL to Shorten
     * @throws Zend_Service_ShortUrl_Exception When URL is not valid
     * @return string New URL
     */
    public function shorten($url)
    {
        $this->_validateUri($url);

        $serviceUri = $this->_apiBaseUri.'/v3/shorten';
        $httpClient = $this->getHttpClient();

        $httpClient->setUri($serviceUri);
        $httpClient->setMethod('GET');
        $httpClient->setParameterGet('login', $this->_login);
        $httpClient->setParameterGet('apiKey', $this->_apiKey);
        $httpClient->setParameterGet('longUrl', $url);
        $httpClient->setParameterGet('format', $this->_format);
        
        $response = $httpClient->request();

        return $response->getBody();
    }

    /**
     * Reveals target for short URL
     *
     * @param string $shortenedUrl URL to reveal target of
     * @throws Zend_Service_ShortUrl_Exception When URL is not valid or is not shortened by this service
     * @return string
     */
    public function unshorten($shortenedUrl)
    {
        $this->_validateUri($shortenedUrl);
        $this->_verifyBaseUri($shortenedUrl);
        
        $serviceUri = $this->_apiBaseUri.'/v3/expand';
        $httpClient = $this->getHttpClient();

        $httpClient->setUri($serviceUri);
        $httpClient->setMethod('GET');
        $httpClient->setParameterGet('login', $this->_login);
        $httpClient->setParameterGet('apiKey', $this->_apiKey);
        $httpClient->setParameterGet('shortUrl', $shortenedUrl);
        $httpClient->setParameterGet('format', $this->_format);
        
        $response = $httpClient->request();

        return $response->getBody();
    }
    
    public function setLogin($login)
    {
        $this->_login = (string)$login;
        
        return $this;
    }

    public function getLogin()
    {
        return $this->_login;
    }
    
    public function setApiKey($apiKey)
    {
        $this->_apiKey= (string)$apiKey;
        
        return $this;
    }

    public function getApiKey()
    {
        return $this->_apiKey;
    }
    
    public function setFormat($format)
    {
        if( !is_string($format) )
            $this->_format = 'txt';
            return $this;
            
        switch($format)
        {
            case 'xml':
            case 'json':
            case 'txt':
                $this->_format = $format;
                break;
            default:
                $this->_format = 'txt';
                break;
        }
        
        return $this;
    }

    public function getFormat()
    {
        return $this->_format;
    }
    
}
