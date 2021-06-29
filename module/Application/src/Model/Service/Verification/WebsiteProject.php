<?php
/**
 *   ocs-webserver
 *
 *   Copyright 2016 by pling GmbH.
 *
 *     This file is part of ocs-webserver.
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Application\Model\Service\Verification;

use Application\Model\Repository\ProjectRepository;
use Application\Model\Service\Ocs\ServerException;
use Exception;
use Laminas\Db\Sql\Expression;
use Laminas\Http\Client;
use Laminas\Uri\Uri;
use Laminas\Uri\UriFactory;
use Laminas\Uri\UriInterface;

/**
 * Class WebsiteProject
 *
 * @package Application\Model\Service\Verification
 */
class WebsiteProject
{

    const SALT_KEY = 'MakeItAndPlingIt';
    const FILE_PREFIX = 'pling';
    const FILE_POSTFIX = '.html';
    /**
     * Configuration for HTTP-Client
     *
     * @var array
     */
    protected $_config = array(
        'maxredirects' => 0,
        'timeout'      => 30,
    );

    private $ocsLog;
    private $projectRepository;

    public function __construct(ProjectRepository $projectRepository)
    {
        $this->ocsLog = $GLOBALS['ocs_log'];
        $this->projectRepository = $projectRepository;

    }

    /**
     * @param string $url
     * @param string $authCode
     *
     * @return bool
     * @throws ServerException
     */
    public function testForAuthCodeExist($url, $authCode)
    {
        if (true == empty($url)) {
            return false;
        }

        $httpClient = $this->getHttpClient();

        $uri = $this->generateUri($url);

        $httpClient->setUri($uri);
        $response = $this->retrieveBody($httpClient);

        if (false === $response) {
            $httpClient->setUri($url);
            $response = $this->retrieveBody($httpClient);
            if (false === $response) {
                $this->ocsLog->err(
                    __METHOD__ . " - Error while validate AuthCode for Website: " . $url . ".\n Server replay was: " . $httpClient->getLastRawResponse() . PHP_EOL
                );

                return false;
            }
        }

        return strpos($response, $authCode) !== false;
    }

    /**
     * @param $uri
     *
     * @return Client
     * @throws ServerException
     */
    protected function getHttpClient($uri = null)
    {
        try {
            if (empty($uri)) {
                return new Client(null, array('keepalive' => true, 'strictredirects' => true));
            }

            return new Client($uri, array('keepalive' => true, 'strictredirects' => true));
        } catch (Exception $e) {
            throw new ServerException('Can not create http client for uri: ' . $uri, 0, $e);
        }
    }

    /**
     * @param $url
     *
     * @return Uri|UriInterface
     */
    public function generateUri($url)
    {
        $uri = UriFactory::factory($url);

        return $uri;
    }

    /**
     * @param Client $httpClient
     *
     * @return bool
     */
    public function retrieveBody($httpClient)
    {
        $response = $httpClient->send();

        if ($response->getStatusCode() < 200 or $response->getStatusCode() >= 400) {
            return false;
        } else {
            return $response->getBody();
        }
    }

    /**
     * @param string $domain
     *
     * @return string
     */
    public function getAuthFileUri($domain)
    {
        return $domain . '/' . $this->getAuthFileName($domain);
    }

    /**
     * @param string $domain
     *
     * @return string
     */
    public function getAuthFileName($domain)
    {
        return self::FILE_PREFIX . $this->generateAuthCode($domain) . self::FILE_POSTFIX;
    }

    /**
     * @param string $domain
     *
     * @return null|string
     */
    public function generateAuthCode($domain)
    {
        if (empty($domain)) {
            return null;
        }

        return md5($domain . self::SALT_KEY);
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * @param $config
     */
    public function setConfig($config)
    {
        $this->_config = $config;
    }

    /**
     * @param $project_id
     * @param $verificationResult
     *
     */
    public function updateData($project_id, $verificationResult)
    {
        $modelProject = $this->projectRepository;
        $modelProject->update(
            [
                'project_id'   => $project_id,
                'validated_at' => new Expression('now()'),
                'validated'    => $verificationResult,
            ]
        );
    }

    /**
     * @param $url
     *
     * @return mixed
     * @throws Exception
     */
    protected function _parseDomain($url)
    {
        $matches = array();
        $count = preg_match_all("/^(?:(?:http|https):\/\/)?([\da-zA-ZäüöÄÖÜ\.-]+\.[a-z\.]{2,6})[\/\w \.-]*\/?$/", $url, $matches);
        if ($count > 0) {
            return current($matches[1]);
        } else {
            throw new Exception(__FILE__ . '(' . __LINE__ . '): ' . 'Error while parsing url= ' . $url);
        }
    }
}