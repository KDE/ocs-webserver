<?php
/**
 *  ocs-webserver
 *
 *  Copyright 2016 by pling GmbH.
 *
 *    This file is part of ocs-webserver.
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **/

namespace Application\Model\Service;

use Application\Model\Interfaces\MemberInterface;
use Application\Model\Service\Interfaces\WebsiteOwnerServiceInterface;
use Exception;
use Laminas\Db\Sql\Expression;
use Laminas\Http\Client;

class WebsiteOwnerService extends BaseService implements WebsiteOwnerServiceInterface
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
    private $memberRepository;

    public function __construct(
        MemberInterface $memberRepository
    ) {
        $this->ocsLog = $GLOBALS['ocs_log'];
        $this->memberRepository = $memberRepository;
    }

    /**
     * @param string $url
     * @param string $authCode
     *
     * @return bool
     */
    public function testForAuthCodeExist($url, $authCode)
    {
        if (true == empty($url)) {
            return false;
        }

        $url = $this->addDefaultScheme($url);

        $httpClient = $this->getHttpClient();

        $uri = $this->getAuthFileUri($url);

        $httpClient->setUri($uri);
        $response = $this->retrieveBody($httpClient);

        if (false === $response) {
            $httpClient->setUri($url);
            $response = $this->retrieveBody($httpClient);
            if (false === $response) {
                //@formatter:off
                $this->ocsLog->err(
                    __METHOD__
                    . " - Error while validate AuthCode for Website: " . $url . PHP_EOL
                    . ". Server replay was: " . $httpClient->getResponse()->getStatusCode()
                    . ". " . $httpClient->getResponse()->getBody()
                    . PHP_EOL
                );
                //@formatter:on
                return false;
            }
        }

        return strpos($response, $authCode) !== false;
    }

    /**
     * @param string $url
     * @param string $scheme
     *
     * @return string
     */
    public function addDefaultScheme($url, $scheme = 'http://')
    {
        if (false == preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = $scheme . $url;
        }

        return $url;
    }

    /**
     */
    public function getHttpClient()
    {
        $httpClient = new Client();
        $httpClient->setOptions($this->_config);

        return $httpClient;
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

        return md5($this->_parseDomain($domain) . self::SALT_KEY);
    }

    /**
     * @param $domain
     *
     * @return mixed|string
     */
    protected function _parseDomain($domain)
    {
        $count = preg_match_all("/^(?:(?:http|https):\/\/)?([\da-zA-ZäüöÄÖÜ\.-]+\.[a-z\.]{2,6})[\/\w \.-]*\/?$/", $domain, $matches);
        if ($count > 0) {
            return current($matches[1]);
        } else {
            $this->ocsLog->err(__METHOD__ . ' - Error while parsing the domain = ' . $domain);

            return '';
        }
    }

    /**
     * @param Client $httpClient
     *
     * @return bool
     */
    public function retrieveBody($httpClient)
    {
        // $response = $httpClient->request();
        $response = $httpClient->send();
        if (!$response->isSuccess()) {
            return false;
        } else {
            return $response->getBody();
        }
    }

    /**
     * @param string $domain
     *
     * @return mixed|string
     */
    public function parseDomain($domain)
    {
        return $this->_parseDomain($domain);
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
     * @param $memberId
     * @param $verificationResult
     *
     */
    public function updateData($memberId, $verificationResult)
    {
        try {
            $modelMember = $this->memberRepository->fetchById($memberId);
            $data = [
                'member_id'    => $memberId,
                'validated_at' => new Expression('NOW()'),
                'validated'    => (int)$verificationResult,
            ];
            $this->memberRepository->update($data);
        } catch (Exception $e) {
            // not found
            return;
        }
    }

}