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

namespace Application\Model\Repository;

use Application\Model\Entity\Video;
use Application\Model\Interfaces\VideoInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Http\Client;
use Laminas\Uri\Uri;
use Laminas\Uri\UriFactory;
use Laminas\Uri\UriInterface;

/**
 * Class VideoRepository
 *
 * @package Application\Model\Repository
 */
class VideoRepository extends BaseRepository implements VideoInterface
{
    public static $VIDE_FILE_TYPES = array(
        'video/3gpp',
        'video/3gpp2',
        'video/mpeg',
        'video/quicktime',
        'video/x-flv',
        'video/webm',
        'application/ogg',
        'video/x-ms-asf',
        'video/x-matroska',
    );
    /**
     * Configuration for HTTP-Client
     *
     * @var array
     */
    protected $_config = array(
        'maxredirects' => 0,
        'timeout'      => 21600,
    );
    protected $_allowed = array(
        'video/3gpp'       => '.3gp',
        'video/3gpp2'      => '.3g2',
        'video/mpeg'       => '.mpeg',
        'video/quicktime'  => '.mov',
        'video/x-flv'      => '.flv',
        'video/webm'       => '.webm',
        'application/ogg'  => '.ogv',
        'video/x-matroska' => '.mkv',
        'video/mp4'        => '.mp4',
    );
    protected $_allowedFileExtension = array(
        '3gp',
        '3g2',
        'mpeg',
        'mov',
        'flv',
        'webm',
        'ogv',
        'mkv',
        'mp4',
    );
    protected $_errorMsg = null;
    private $ocsLog;
    private $ocsConfig;

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "ppload.ppload_file_preview";
        $this->_key = "id";
        $this->_prototype = Video::class;
        $this->ocsLog = $GLOBALS['ocs_log'];
        $this->ocsConfig = $GLOBALS['ocs_config'];
    }

    /**
     * @param        $collectionId
     * @param        $fileType
     * @param string $url
     *
     * @return bool
     */
    public function storeExternalVideo($collectionId, $fileType, $url)
    {

        if (true == empty($url)) {
            return false;
        }

        $httpClient = $this->getHttpClient();

        $config = $this->ocsConfig;
        $skipConvert = false;
        if ($fileType == 'video/mp4') {
            $skipConvert = true;
        }
        $videourl = $config->settings->server->videos->media->upload . "?url=" . urlencode($url) . "&collection_id=" . $collectionId . "&skip_convert=" . $skipConvert;

        $this->ocsLog->debug(__METHOD__ . " - VideoUrl: " . $videourl . PHP_EOL);

        $uri = $this->generateUri($videourl);

        $httpClient->setUri($uri);
        $response = $this->retrieveBody($httpClient);
        if (false === $response) {

            $this->ocsLog->err(
                __METHOD__ . " - Error while converting Video: " . $uri . ".\n Server reply was: " . $httpClient->getLastRawResponse() . PHP_EOL
            );

            return false;
        }

        $this->ocsLog->debug(__METHOD__ . ' Result: ' . print_r($response, true));

        return $response;

    }

    /**
     * @return Client
     */
    public function getHttpClient()
    {

        return new Client(null, $this->_config);
    }

    /**
     * @param $url
     *
     * @return Uri|UriInterface
     */
    public function generateUri($url)
    {
        return UriFactory::factory($url);
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
     * Inserts a new video
     *
     * @param array $data
     */
    public function insertNewVideo($data)
    {
        $sql = "INSERT INTO " . $this->_name;

        $keyStr = implode(", ", array_keys($data));
        $valStr = implode(", ", array_values($data));

        $sql .= "(" . $keyStr . ") VALUES (" . $valStr . ")";

        $stmt = $this->db->query($sql);
        $stmt->execute();
    }

    /**
     * Inserts a new video
     *
     * @param $url_preview
     * @param $url_thumb
     * @param $where
     */
    public function updateVideo($url_preview, $url_thumb, $where)
    {
        $sql = "UPDATE " . $this->_name . " SET url_preview = '" . $url_preview . "' ,url_thumb = '" . $url_thumb . "' WHERE " . $where;
        $this->ocsLog->debug(__METHOD__ . ' Sql: ' . print_r($sql, true));
        $stmt = $this->db->query($sql);
        $stmt->execute();
    }

    /**
     * @return mixed
     */
    public function getNewId()
    {
        $result = $this->fetchRow('SELECT UUID_SHORT()');

        return $result['UUID_SHORT()'];
    }

}