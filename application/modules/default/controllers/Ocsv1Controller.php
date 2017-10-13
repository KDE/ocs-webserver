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

/**
 * What changes from official OCS v1 spec
 *
 * OCS specification:
 * http://www.freedesktop.org/wiki/Specifications/open-collaboration-services/
 *
 * ----
 *
 * Allow delimiter ',' of value of parameter 'categories'
 *
 * Example:
 * /content/data?categories=1,2,3
 * /content/data?categories=1x2x3
 *
 * ----
 *
 * Additional URL queries to '/content/data'
 *
 * xdg_types
 * package_types
 *
 * Example:
 * /content/data?xdg_types=icons,themes,wallpapers
 * /content/data?package_types=1,2,3
 *
 * package_types:
 * 1 = AppImage
 * 2 = Android (apk)
 * 3 = OS X compatible
 * 4 = Windows executable
 * 5 = Debian
 * 6 = Snappy
 * 7 = Flatpak
 * 8 = Electron-Webapp
 * 9 = Arch
 * 10 = open/Suse
 * 11 = Redhat
 *
 * ----
 *
 * Additional data field of '/content/categories'
 *
 * display_name
 * parent_id
 * xdg_type
 *
 * ----
 *
 * Additional data field of '/content/data'
 *
 * xdg_type
 * download_package_type{n}
 * download_package_arch{n}
 *
 * ----
 *
 * Additional data field of '/content/download'
 *
 * download_package_type
 * download_package_arch
 *
 * ----
 *
 * Additional API method for preview picture
 *
 * /content/previewpic/{contentid}
 *
 * Example:
 * /content/previewpic/123456789
 * /content/previewpic/123456789?size=medium
 */
class Ocsv1Controller extends Zend_Controller_Action
{

    const COMMENT_TYPE_CONTENT = 1;
    const COMMENT_TYPE_FORUM = 4;
    const COMMENT_TYPE_KNOWLEDGE = 7;
    const COMMENT_TYPE_EVENT = 8;

    protected $_authData = null;

    protected $_uriScheme = 'https';

    protected $_format = 'xml';

    protected $_config = array(
        'id'         => 'opendesktop.org',
        'location'   => 'https://www.opendesktop.org/ocs/v1/',
        'name'       => 'opendesktop.org',
        'icon'       => '',
        'termsofuse' => 'https://www.opendesktop.org/terms',
        'register'   => 'https://www.opendesktop.org/register',
        'version'    => '1.7',
        'website'    => 'www.opendesktop.org',
        'host'       => 'www.opendesktop.org',
        'contact'    => 'contact@opendesktop.org',
        'ssl'        => true,
        'user_host'  => 'pling.me'
    );

    protected $_params = array();

    public function init()
    {
        parent::init();
        $this->initView();
        $this->_initUriScheme();
        $this->_initRequestParamsAndFormat();
        $this->_initConfig();
        $this->_initResponseHeader();
    }

    public function initView()
    {
        // Disable render view
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    protected function _initUriScheme()
    {
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] === '1')) {
            $this->_uriScheme = 'https';
        } else {
            $this->_uriScheme = 'http';
        }
    }

    /**
     * @throws Zend_Exception
     */
    protected function _initRequestParamsAndFormat()
    {
        // Set request parameters
        switch (strtoupper($_SERVER['REQUEST_METHOD'])) {
            case 'GET':
                $this->_params = $_GET;
                break;
            case 'PUT':
                parse_str(file_get_contents('php://input'), $_PUT);
                $this->_params = $_PUT;
                break;
            case 'POST':
                $this->_params = $_POST;
                break;
            default:
                Zend_Registry::get('logger')->err(__METHOD__ . ' - request method not supported - ' . $_SERVER['REQUEST_METHOD']);
                exit('request method not supported');
        }

        // Set format option
        if (isset($this->_params['format']) && strtolower($this->_params['format']) == 'json') {
            $this->_format = 'json';
        }
    }

    protected function _initConfig()
    {
        $clientConfig = $this->_loadClientConfig();

        $credentials = '';
        if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
            $credentials = $_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW'] . '@';
        }

        $baseUri = $this->_uriScheme . '://' . $credentials . $_SERVER['SERVER_NAME'];

        $webSite = $_SERVER['SERVER_NAME'];

        //Mask api.kde-look.org to store.kde.org
        if (strpos($_SERVER['SERVER_NAME'], 'api.kde-look.org') !== false) {
            $webSite = 'store.kde.org';
        }

        $this->_config = array(
                'id'         => $_SERVER['SERVER_NAME'],
                'location'   => $baseUri . '/ocs/v1/',
                'name'       => $clientConfig['head']['browser_title'],
                'icon'       => $baseUri . $clientConfig['logo'],
                'termsofuse' => $baseUri . '/content/terms',
                'register'   => $baseUri . '/register',
                'website'    => $webSite,
                'host'       => $_SERVER['SERVER_NAME']
            ) + $this->_config;
    }

    /**
     * @return array|null
     */
    protected function _loadClientConfig()
    {
        $clientConfigReader = new Backend_Model_ClientFileConfig($this->_getNameForStoreClient());
        $clientConfigReader->loadClientConfig();

        return $clientConfigReader->getConfig();
    }

    /**
     * Returns the name for the store client.
     * If no name were found, the name for the standard store client will be returned.
     *
     * @return string
     */
    protected function _getNameForStoreClient()
    {
        $clientName = Zend_Registry::get('config')->settings->client->default->name; // default client
        if (Zend_Registry::isRegistered('store_config_name')) {
            $clientName = Zend_Registry::get('store_config_name');
        }

        return $clientName;
    }

    protected function _initResponseHeader()
    {
        $duration = 1800; // in seconds
        $expires = gmdate("D, d M Y H:i:s", time() + $duration) . " GMT";

        $this->getResponse()
             ->setHeader('X-FRAME-OPTIONS', 'SAMEORIGIN', true)
//             ->setHeader('Last-Modified', $modifiedTime, true)
             ->setHeader('Expires', $expires, true)->setHeader('Pragma', 'cache', true)
             ->setHeader('Cache-Control', 'max-age=1800, public', true)
        ;
    }

    public function indexAction()
    {
        $this->_sendErrorResponse(999, 'unknown request');
    }

    protected function _sendErrorResponse($statuscode, $message = '')
    {
        if ($this->_format == 'json') {
            $response = array(
                'status'     => 'failed',
                'statuscode' => $statuscode,
                'message'    => $message
            );
        } else {
            $response = array(
                'meta' => array(
                    'status'     => array('@text' => 'failed'),
                    'statuscode' => array('@text' => $statuscode),
                    'message'    => array('@text' => $message)
                )
            );
        }

        $this->_sendResponse($response, $this->_format);
    }

    protected function _sendResponse($response, $format = 'xml', $xmlRootTag = 'ocs')
    {
        header('Pragma: public');
        header('Cache-Control: cache, must-revalidate');
        $duration = 1800; // in seconds
        $expires = gmdate("D, d M Y H:i:s", time() + $duration) . " GMT";
        header('Expires: ' . $expires);
        if ($format == 'json') {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode($response);
        } else {
            header('Content-Type: application/xml; charset=UTF-8');
            echo $this->_convertXmlDom($response, $xmlRootTag)->saveXML();
        }

        exit;
    }

    protected function _convertXmlDom($values, $tagName = 'data', DOMNode &$dom = null, DOMElement &$element = null)
    {
        if (!$dom) {
            $dom = new DomDocument('1.0', 'UTF-8');
        }
        if (!$element) {
            $element = $dom->appendChild($dom->createElement($tagName));
        }
        if (is_array($values) || is_object($values)) {
            foreach ($values as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $isHash = false;
                    foreach ($value as $_key => $_value) {
                        if (ctype_digit((string)$_key)) {
                            $isHash = true;
                        }
                        break;
                    }
                    if ($isHash) {
                        $this->_convertXmlDom($value, $key, $dom, $element);
                        continue;
                    }
                    if (ctype_digit((string)$key)) {
                        $key = $tagName;
                    }
                    $childElement = $element->appendChild($dom->createElement($key));
                    $this->_convertXmlDom($value, $key, $dom, $childElement);
                } else {
                    if ($key == '@text') {
                        if (is_bool($value)) {
                            $value = var_export($value, true);
                        }
                        $element->appendChild($dom->createTextNode($value));
                    } else if ($key == '@cdata') {
                        if (is_bool($value)) {
                            $value = var_export($value, true);
                        }
                        $element->appendChild($dom->createCDATASection($value));
                    } else {
                        if (is_bool($value)) {
                            $value = var_export($value, true);
                        }
                        $element->setAttribute($key, $value);
                    }
                }
            }
        }

        return $dom;
    }

    public function providersAction()
    {
        // As providers.xml
        $response = array(
            'provider' => array(
                'id'         => array('@text' => $this->_config['id']),
                'location'   => array('@text' => $this->_config['location']),
                'name'       => array('@text' => $this->_config['name']),
                'icon'       => array('@text' => $this->_config['icon']),
                'termsofuse' => array('@text' => $this->_config['termsofuse']),
                'register'   => array('@text' => $this->_config['register']),
                'services'   => array(
                    'person'  => array('ocsversion' => $this->_config['version']),
                    'content' => array('ocsversion' => $this->_config['version'])
                )
            )
        );

        $this->_sendResponse($response, 'xml', 'providers');
    }

    public function configAction()
    {
        if ($this->_format == 'json') {
            $response = array(
                'status'     => 'ok',
                'statuscode' => 100,
                'message'    => '',
                'data'       => array(
                    'version' => $this->_config['version'],
                    'website' => $this->_config['website'],
                    'host'    => $this->_config['host'],
                    'contact' => $this->_config['contact'],
                    'ssl'     => $this->_config['ssl']
                )
            );
        } else {
            $response = array(
                'meta' => array(
                    'status'     => array('@text' => 'ok'),
                    'statuscode' => array('@text' => 100),
                    'message'    => array('@text' => '')
                ),
                'data' => array(
                    'version' => array('@text' => $this->_config['version']),
                    'website' => array('@text' => $this->_config['website']),
                    'host'    => array('@text' => $this->_config['host']),
                    'contact' => array('@text' => $this->_config['contact']),
                    'ssl'     => array('@text' => $this->_config['ssl'])
                )
            );
        }

        $this->_sendResponse($response, $this->_format);
    }

    public function personcheckAction()
    {
        $identity = null;
        $credential = null;
        if (!empty($this->_params['login'])) {
            $identity = $this->_params['login'];
        }
        if (!empty($this->_params['password'])) {
            $credential = $this->_params['password'];
        }

        if (!$identity) {
            $this->_sendErrorResponse(101, 'please specify all mandatory fields');
        } else {
            if (!$this->_authenticateUser($identity, $credential)) {
                $this->_sendErrorResponse(102, 'login not valid');
            }
        }

        if ($this->_format == 'json') {
            $response = array(
                'status'     => 'ok',
                'statuscode' => 100,
                'message'    => '',
                'data'       => array(
                    array(
                        'details'  => 'check',
                        'personid' => $this->_authData->username
                    )
                )
            );
        } else {
            $response = array(
                'meta' => array(
                    'status'     => array('@text' => 'ok'),
                    'statuscode' => array('@text' => 100),
                    'message'    => array('@text' => '')
                ),
                'data' => array(
                    'person' => array(
                        'details'  => 'check',
                        'personid' => array('@text' => $this->_authData->username)
                    )
                )
            );
        }

        $this->_sendResponse($response, $this->_format);
    }

    protected function _authenticateUser($identity = null, $credential = null, $force = false)
    {
        ////////////////////////////////////////////////////////
        // BasicAuth enabled testing site workaround
        if (strrpos($_SERVER['SERVER_NAME'], 'pling.ws') !== false
            || strrpos($_SERVER['SERVER_NAME'], 'pling.cc') !== false
            || strrpos($_SERVER['SERVER_NAME'], 'pling.to') !== false
            || strrpos($_SERVER['SERVER_NAME'], 'ocs-store.com') !== false) {
            $authData = new stdClass;
            $authData->username = 'dummy';
            $this->_authData = $authData;

            return true;
        }
        ////////////////////////////////////////////////////////

        if (!$identity && !empty($_SERVER['PHP_AUTH_USER'])) {
            // Will set user identity or API-Key
            $identity = $_SERVER['PHP_AUTH_USER'];
        }
        if (!$credential && !empty($_SERVER['PHP_AUTH_PW'])) {
            $credential = $_SERVER['PHP_AUTH_PW'];
        }

        $loginMethod = null;
        //if ($identity && !$credential) {
        //    $loginMethod = 'api-key';
        //}

        if ($identity && ($credential || $loginMethod == 'api-key')) {
            $authModel = new Default_Model_Authorization();
            $authData = $authModel->getAuthDataFromApi($identity, $credential, $loginMethod);
            if ($authData) {
                $this->_authData = $authData;

                return true;
            }
        }

        if ($force) {
            //header('WWW-Authenticate: Basic realm="Your valid user account or api key"');
            header('WWW-Authenticate: Basic realm="Your valid user account"');
            header('HTTP/1.0 401 Unauthorized');
            exit;
        }

        return false;
    }

    public function personselfAction()
    {
        $this->persondataAction(true);
    }

    public function persondataAction($self = false)
    {
        if (!$this->_authenticateUser()) {
            $this->_sendErrorResponse(101, 'person not found');
        }

        $tableMember = new Default_Model_Member();

        // Self data or specific person data
        if ($self || $this->getParam('personid')) {
            if ($self) {
                $username = $this->_authData->username;
            } else {
                if ($this->getParam('personid')) {
                    $username = $this->getParam('personid');
                }
            }

            $member = $tableMember->findActiveMemberByIdentity($username);

            if (!$member) {
                $this->_sendErrorResponse(101, 'person not found');
            }

            $profilePage = $this->_uriScheme . '://' . $this->_config['user_host'] . '/member/' . $member->member_id;

            if ($this->_format == 'json') {
                $response = array(
                    'status'     => 'ok',
                    'statuscode' => 100,
                    'message'    => '',
                    'data'       => array(
                        array(
                            'details'              => 'full',
                            'personid'             => $member->username,
                            'privacy'              => 0,
                            'privacytext'          => 'public',
                            'firstname'            => $member->firstname,
                            'lastname'             => $member->lastname,
                            'gender'               => '',
                            'communityrole'        => '',
                            'homepage'             => $member->link_website,
                            'company'              => '',
                            'avatarpic'            => $member->profile_image_url,
                            'avatarpicfound'       => true,
                            'bigavatarpic'         => $member->profile_image_url,
                            'bigavatarpicfound'    => true,
                            'birthday'             => '',
                            'jobstatus'            => '',
                            'city'                 => $member->city,
                            'country'              => $member->country,
                            'latitude'             => '',
                            'longitude'            => '',
                            'ircnick'              => '',
                            'ircchannels'          => '',
                            'irclink'              => '',
                            'likes'                => '',
                            'dontlikes'            => '',
                            'interests'            => '',
                            'languages'            => '',
                            'programminglanguages' => '',
                            'favouritequote'       => '',
                            'favouritemusic'       => '',
                            'favouritetvshows'     => '',
                            'favouritemovies'      => '',
                            'favouritebooks'       => '',
                            'favouritegames'       => '',
                            'description'          => $member->biography,
                            'profilepage'          => $profilePage
                        )
                    )
                );
            } else {
                $response = array(
                    'meta' => array(
                        'status'     => array('@text' => 'ok'),
                        'statuscode' => array('@text' => 100),
                        'message'    => array('@text' => '')
                    ),
                    'data' => array(
                        'person' => array(
                            'details'              => 'full',
                            'personid'             => array('@text' => $member->username),
                            'privacy'              => array('@text' => 0),
                            'privacytext'          => array('@text' => 'public'),
                            'firstname'            => array('@text' => $member->firstname),
                            'lastname'             => array('@text' => $member->lastname),
                            'gender'               => array('@text' => ''),
                            'communityrole'        => array('@text' => ''),
                            'homepage'             => array('@text' => $member->link_website),
                            'company'              => array('@text' => ''),
                            'avatarpic'            => array('@text' => $member->profile_image_url),
                            'avatarpicfound'       => array('@text' => true),
                            'bigavatarpic'         => array('@text' => $member->profile_image_url),
                            'bigavatarpicfound'    => array('@text' => true),
                            'birthday'             => array('@text' => ''),
                            'jobstatus'            => array('@text' => ''),
                            'city'                 => array('@text' => $member->city),
                            'country'              => array('@text' => $member->country),
                            'latitude'             => array('@text' => ''),
                            'longitude'            => array('@text' => ''),
                            'ircnick'              => array('@text' => ''),
                            'ircchannels'          => array('@text' => ''),
                            'irclink'              => array('@text' => ''),
                            'likes'                => array('@text' => ''),
                            'dontlikes'            => array('@text' => ''),
                            'interests'            => array('@text' => ''),
                            'languages'            => array('@text' => ''),
                            'programminglanguages' => array('@text' => ''),
                            'favouritequote'       => array('@text' => ''),
                            'favouritemusic'       => array('@text' => ''),
                            'favouritetvshows'     => array('@text' => ''),
                            'favouritemovies'      => array('@text' => ''),
                            'favouritebooks'       => array('@text' => ''),
                            'favouritegames'       => array('@text' => ''),
                            'description'          => array('@text' => $member->biography),
                            'profilepage'          => array('@text' => $profilePage)
                        )
                    )
                );
            }

            $this->_sendResponse($response, $this->_format);
        } // Find a specific list of persons
        else {
            $limit = 10; // 1 - 100
            $offset = 0;

            $tableMemberSelect = $tableMember->select()->where('is_active = ?', 1)->where('is_deleted = ?', 0);

            if (!empty($this->_params['name'])) {
                $isSearchable = false;
                foreach (explode(' ', $this->_params['name']) as $keyword) {
                    if ($keyword && strlen($keyword) > 2) {
                        $tableMemberSelect->where('username LIKE ?' . ' OR firstname LIKE ?' . ' OR lastname LIKE ?', "%$keyword%");
                        $isSearchable = true;
                    }
                }
                if (!$isSearchable) {
                    $tableMemberSelect->where('username LIKE ?' . ' OR firstname LIKE ?' . ' OR lastname LIKE ?',
                        "%{$this->_params['name']}%");
                }
            }
            if (!empty($this->_params['country'])) {
                $tableMemberSelect->where('country = ?', $this->_params['country']);
            }
            if (!empty($this->_params['city'])) {
                $tableMemberSelect->where('city = ?', $this->_params['city']);
            }
            if (!empty($this->_params['description'])) {
                $isSearchable = false;
                foreach (explode(' ', $this->_params['name']) as $keyword) {
                    if ($keyword && strlen($keyword) > 2) {
                        $tableMemberSelect->where('biography LIKE ?', "%$keyword%");
                        $isSearchable = true;
                    }
                }
                if (!$isSearchable) {
                    $tableMemberSelect->where('biography LIKE ?', "%$this->_params['description']}%");
                }
            }
            if (!empty($this->_params['pc'])) {
            }
            if (!empty($this->_params['software'])) {
            }
            if (!empty($this->_params['longitude'])) {
            }
            if (!empty($this->_params['latitude'])) {
            }
            if (!empty($this->_params['distance'])) {
            }
            if (!empty($this->_params['attributeapp'])) {
            }
            if (!empty($this->_params['attributekey'])) {
            }
            if (!empty($this->_params['attributevalue'])) {
            }
            if (isset($this->_params['pagesize'])
                && ctype_digit((string)$this->_params['pagesize'])
                && $this->_params['pagesize'] > 0
                && $this->_params['pagesize'] < 101) {
                $limit = $this->_params['pagesize'];
            }
            if (isset($this->_params['page'])
                && ctype_digit((string)$this->_params['page'])) {
                // page parameter: the first page is 0
                $offset = $limit * $this->_params['page'];
            }

            $members = $tableMember->fetchAll($tableMemberSelect->limit($limit, $offset));

            $tableMemberSelect->reset('columns');
            $tableMemberSelect->reset('limitcount');
            $tableMemberSelect->reset('limitoffset');

            $count = $tableMember->fetchRow($tableMemberSelect->columns(array('count' => 'COUNT(*)')));

            if ($count['count'] > 1000) {
                $this->_sendErrorResponse(102, 'more than 1000 people found.' . ' it is not allowed to fetch such a big resultset.'
                    . ' please specify more search conditions');
            }

            if ($this->_format == 'json') {
                $response = array(
                    'status'       => 'ok',
                    'statuscode'   => 100,
                    'message'      => '',
                    'totalitems'   => $count['count'],
                    'itemsperpage' => $limit,
                    'data'         => array()
                );
            } else {
                $response = array(
                    'meta' => array(
                        'status'       => array('@text' => 'ok'),
                        'statuscode'   => array('@text' => 100),
                        'message'      => array('@text' => ''),
                        'totalitems'   => array('@text' => $count['count']),
                        'itemsperpage' => array('@text' => $limit)
                    ),
                    'data' => array()
                );
            }

            if (!count($members)) {
                $this->_sendResponse($response, $this->_format);
            }

            $personsList = array();
            foreach ($members as $member) {
                if ($this->_format == 'json') {
                    $personsList[] = array(
                        'details'       => 'summary',
                        'personid'      => $member->username,
                        'privacy'       => 0,
                        'privacytext'   => 'public',
                        'firstname'     => $member->firstname,
                        'lastname'      => $member->lastname,
                        'gender'        => '',
                        'communityrole' => '',
                        'company'       => '',
                        'city'          => $member->city,
                        'country'       => $member->country
                    );
                } else {
                    $personsList[] = array(
                        'details'       => 'summary',
                        'personid'      => array('@text' => $member->username),
                        'privacy'       => array('@text' => 0),
                        'privacytext'   => array('@text' => 'public'),
                        'firstname'     => array('@text' => $member->firstname),
                        'lastname'      => array('@text' => $member->lastname),
                        'gender'        => array('@text' => ''),
                        'communityrole' => array('@text' => ''),
                        'company'       => array('@text' => ''),
                        'city'          => array('@text' => $member->city),
                        'country'       => array('@text' => $member->country)
                    );
                }
            }

            if ($this->_format == 'json') {
                $response['data'] = $personsList;
            } else {
                $response['data'] = array('person' => $personsList);
            }

            $this->_sendResponse($response, $this->_format);
        }
    }

    public function contentcategoriesAction()
    {
        if (!$this->_authenticateUser()) {
            //    $this->_sendErrorResponse(999, '');
        }

        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = 'api_content_categories';

        if (false == ($categoriesList = $cache->load($cacheName))) {
            $categoriesList = $this->_buildCategories();
            $cache->save($categoriesList, $cacheName, array(), 1800);
        }

        if ($this->_format == 'json') {
            $response = array(
                'status'     => 'ok',
                'statuscode' => 100,
                'message'    => '',
                'totalitems' => count($categoriesList),
                'data'       => array()
            );
            if (!empty($categoriesList)) {
                $response['data'] = $categoriesList;
            }
        } else {
            $response = array(
                'meta' => array(
                    'status'     => array('@text' => 'ok'),
                    'statuscode' => array('@text' => 100),
                    'message'    => array('@text' => ''),
                    'totalitems' => array('@text' => count($categoriesList))
                ),
                'data' => array()
            );
            if (!empty($categoriesList)) {
                $response['data'] = array('category' => $categoriesList);
            }
        }

        $this->_sendResponse($response, $this->_format);
    }

    protected function _buildCategories()
    {
        $modelCategoryTree = new Default_Model_ProjectCategory();
        $tree = $modelCategoryTree->fetchCategoryTreeCurrentStore();

        return $this->buildResponseTree($tree);
    }

    protected function buildResponseTree($tree)
    {
        $result = array();
        foreach ($tree as $element) {
            if ($this->_format == 'json') {
                $result[] = array(
                    'id'           => $element['id'],
                    'name'         => (false === empty($element['name_legacy'])) ? $element['name_legacy'] : $element['title'],
                    'display_name' => $element['title'],
                    'parent_id'    => (false === empty($element['parent_id'])) ? $element['parent_id'] : '',
                    'xdg_type'     => (false === empty($element['xdg_type'])) ? $element['xdg_type'] : ''
                );
            } else {
                $result[] = array(
                    'id'           => array('@text' => $element['id']),
                    'name'         => array('@text' => (false === empty($element['name_legacy'])) ? $element['name_legacy'] : $element['title']),
                    'display_name' => array('@text' => $element['title']),
                    'parent_id'    => array('@text' => (false === empty($element['parent_id'])) ? $element['parent_id'] : ''),
                    'xdg_type'     => array('@text' => (false === empty($element['xdg_type'])) ? $element['xdg_type'] : '')
                );
            }
            if ($element['has_children']) {
                $sub_tree = $this->buildResponseTree($element['children']);
                $result = array_merge($result, $sub_tree);
            }
        }

        return $result;
    }

    public function contentdataAction()
    {
        if (!$this->_authenticateUser()) {
            //    $this->_sendErrorResponse(999, '');
        }

        $pploadApi = new Ppload_Api(array(
            'apiUri'   => PPLOAD_API_URI,
            'clientId' => PPLOAD_CLIENT_ID,
            'secret'   => PPLOAD_SECRET
        ));
        $previewPicSize = array(
            'width'  => 770,
            'height' => 540
        );
        $smallPreviewPicSize = array(
            'width'  => 100,
            'height' => 100
        );

        // Specific content data
        $requestedId = (int)$this->getParam('contentid') ? (int)$this->getParam('contentid') : null;
        if ($requestedId) {
            $response = $this->fetchContent($requestedId, $previewPicSize, $smallPreviewPicSize, $pploadApi);

            $this->_sendResponse($response, $this->_format);
        } // Gets a list of a specific set of contents
        else {
            $response = $this->fetchCategoryContent($previewPicSize, $smallPreviewPicSize, $pploadApi);

            $this->_sendResponse($response, $this->_format);
        }
    }

    /**
     * @param int        $contentId
     * @param array      $previewPicSize
     * @param array      $smallPreviewPicSize
     * @param Ppload_Api $pploadApi
     *
     * @return array
     */
    protected function fetchContent(
        $contentId,
        $previewPicSize,
        $smallPreviewPicSize,
        $pploadApi
    ) {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = 'api_fetch_content_by_id_' . $contentId;

        if (($response = $cache->load($cacheName))) {
            return $response;
        }

        $tableProject = new Default_Model_Project();
        $tableProjectSelect = $this->_buildProjectSelect($tableProject);

        $project = $tableProject->fetchRow($tableProjectSelect->where('project.project_id = ?', $contentId));

        if (!$project) {
            $this->_sendErrorResponse(101, 'content not found');
        }

        $project->title = Default_Model_HtmlPurify::purify($project->title);
        $project->description = Default_Model_BBCode::renderHtml(Default_Model_HtmlPurify::purify($project->description));
        $project->version = Default_Model_HtmlPurify::purify($project->version);

        $categoryXdgType = '';
        if (!empty($project->cat_xdg_type)) {
            $categoryXdgType = $project->cat_xdg_type;
        }

        $created = date('c', strtotime($project->created_at));
        $changed = date('c', strtotime($project->changed_at));

        $previewPage = $this->_uriScheme . '://' . $this->_config['website'] . '/p/' . $project->project_id;

        $donationPage = $previewPage;
        if (empty($project->paypal_mail) && empty($project->dwolla_id)) {
            $donationPage = '';
        }

        list($previewPics, $smallPreviewPics) = $this->getGalleryPictures($project, $previewPicSize, $smallPreviewPicSize);

        $downloads = $project->count_downloads_hive;
        list($downloadItems, $downloads) = $this->getPPLoadInfo($project, $pploadApi, $downloads);

        if ($this->_format == 'json') {
            $response = array(
                'status'     => 'ok',
                'statuscode' => 100,
                'message'    => '',
                'data'       => array(
                    array(
                        'details'              => 'full',
                        'id'                   => $project->project_id,
                        'name'                 => $project->title,
                        'version'              => $project->version,
                        'typeid'               => $project->project_category_id,
                        'typename'             => $project->cat_title,
                        'xdg_type'             => $categoryXdgType,
                        'language'             => '',
                        'personid'             => $project->username,
                        'created'              => $created,
                        'changed'              => $changed,
                        'downloads'            => $downloads,
                        'score'                => $project->laplace_score,
                        'summary'              => '',
                        'description'          => $project->description,
                        'changelog'            => '',
                        'feedbackurl'          => $previewPage,
                        'homepage'             => $previewPage,
                        'homepagetype'         => '',
                        'donationpage'         => $donationPage,
                        'comments'             => $project->count_comments,
                        'commentspage'         => $previewPage,
                        'fans'                 => null,
                        'fanspage'             => '',
                        'knowledgebaseentries' => null,
                        'knowledgebasepage'    => '',
                        'depend'               => '',
                        'preview1'             => $previewPage,
                        'icon'                 => '',
                        'video'                => '',
                        'detailpage'           => $previewPage,
                        'tags'                 => $project->tags
                    ) + $previewPics + $smallPreviewPics + $downloadItems
                )
            );
        } else {
            foreach ($previewPics as $key => $value) {
                $previewPics[$key] = array('@text' => $value);
            }
            foreach ($smallPreviewPics as $key => $value) {
                $smallPreviewPics[$key] = array('@text' => $value);
            }
            if ($downloadItems) {
                foreach ($downloadItems as $key => $value) {
                    $downloadItems[$key] = array('@text' => $value);
                }
            }
            $response = array(
                'meta' => array(
                    'status'     => array('@text' => 'ok'),
                    'statuscode' => array('@text' => 100),
                    'message'    => array('@text' => '')
                ),
                'data' => array(
                    'content' => array(
                            'details'              => 'full',
                            'id'                   => array('@text' => $project->project_id),
                            'name'                 => array('@text' => $project->title),
                            'version'              => array('@text' => $project->version),
                            'typeid'               => array('@text' => $project->project_category_id),
                            'typename'             => array('@text' => $project->cat_title),
                            'xdg_type'             => array('@text' => $categoryXdgType),
                            'language'             => array('@text' => ''),
                            'personid'             => array('@text' => $project->username),
                            'created'              => array('@text' => $created),
                            'changed'              => array('@text' => $changed),
                            'downloads'            => array('@text' => $downloads),
                            'score'                => array('@text' => $project->laplace_score),
                            'summary'              => array('@text' => ''),
                            'description'          => array('@cdata' => $project->description),
                            'changelog'            => array('@text' => ''),
                            'feedbackurl'          => array('@text' => $previewPage),
                            'homepage'             => array('@text' => $previewPage),
                            'homepagetype'         => array('@text' => ''),
                            'donationpage'         => array('@text' => $donationPage),
                            'comments'             => array('@text' => $project->count_comments),
                            'commentspage'         => array('@text' => $previewPage),
                            'fans'                 => array('@text' => null),
                            'fanspage'             => array('@text' => ''),
                            'knowledgebaseentries' => array('@text' => null),
                            'knowledgebasepage'    => array('@text' => ''),
                            'depend'               => array('@text' => ''),
                            'preview1'             => array('@text' => $previewPage),
                            'icon'                 => array('@text' => ''),
                            'video'                => array('@text' => ''),
                            'detailpage'           => array('@text' => $previewPage),
                            'tags'                 => array('@text' => $project->tags)
                        ) + $previewPics + $smallPreviewPics + $downloadItems
                )
            );
        }

        $cache->save($response, $cacheName);

        return $response;
    }

    /**
     * @param Zend_Db_Table $tableProject
     *
     * @param bool          $withSqlCalcFoundRows
     *
     * @return Zend_Db_Table_Select
     */
    protected function _buildProjectSelect($tableProject, $withSqlCalcFoundRows = false)
    {
        $tableProjectSelect = $tableProject->select();
        if ($withSqlCalcFoundRows) {
            $tableProjectSelect->from(array('project' => 'stat_projects'), array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS *')));
        } else {
            $tableProjectSelect->from(array('project' => 'stat_projects'));
        }
        $tableProjectSelect->setIntegrityCheck(false)->columns(array(
            '*',
            'member_username' => 'username',
            'category_title'  => 'cat_title',
            'xdg_type'        => 'cat_xdg_type',
            'name_legacy'     => 'cat_name_legacy'
        ))->where('project.status = ?', Default_Model_Project::PROJECT_ACTIVE)
          ->where('project.ppload_collection_id IS NOT NULL')
        ;

        return $tableProjectSelect;
    }

    /**
     * @param Zend_Db_Table_Row_Abstract $project
     * @param array                      $previewPicSize
     * @param array                      $smallPreviewPicSize
     *
     * @return array
     */
    protected function getGalleryPictures($project, $previewPicSize, $smallPreviewPicSize)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = 'api_fetch_gallery_pics_' . $project->project_id;

        if (($previews = $cache->load($cacheName))) {
            return $previews;
        }

        $viewHelperImage = new Default_View_Helper_Image();
        $previewPics = array(
            'previewpic1' => $viewHelperImage->Image($project->image_small, $previewPicSize)
        );
        $smallPreviewPics = array(
            'smallpreviewpic1' => $viewHelperImage->Image($project->image_small, $smallPreviewPicSize)
        );

        $tableProject = new Default_Model_Project();
        $galleryPics = $tableProject->getGalleryPictureSources($project->project_id);
        if ($galleryPics) {
            $i = 2;
            foreach ($galleryPics as $galleryPic) {
                $previewPics['previewpic' . $i] = $viewHelperImage->Image($galleryPic, $previewPicSize);
                $smallPreviewPics['smallpreviewpic' . $i] = $viewHelperImage->Image($galleryPic, $smallPreviewPicSize);
                $i++;
            }
        }

        $cache->save(array($previewPics, $smallPreviewPics), $cacheName);

        return array($previewPics, $smallPreviewPics);
    }

    /**
     * @param Zend_Db_Table_Row_Abstract $project
     * @param Ppload_Api                 $pploadApi
     * @param int                        $downloads
     *
     * @return array
     */
    protected function getPPLoadInfo($project, $pploadApi, $downloads)
    {
        $downloadItems = array();

        if (empty($project->ppload_collection_id)) {
            return array($downloadItems, $downloads);
        }

        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = 'api_ppload_collection_by_id_' . $project->ppload_collection_id;

        if (($pploadInfo = $cache->load($cacheName))) {
            return $pploadInfo;
        }

        $filesRequest = array(
            'collection_id'     => ltrim($project->ppload_collection_id, '!'),
            'ocs_compatibility' => 'compatible',
            'perpage'           => 100
        );

        $filesResponse = $pploadApi->getFiles($filesRequest);
        if (isset($filesResponse->status) && $filesResponse->status == 'success') {
            $i = 1;
            foreach ($filesResponse->files as $file) {
                $downloads += (int)$file->downloaded_count;
                $tags = $this->_parseFileTags($file->tags);
                $downloadLink = PPLOAD_API_URI . 'files/download/' . 'id/' . $file->id . '/' . $file->name;
                $downloadItems['downloadway' . $i] = 1;
                $downloadItems['downloadtype' . $i] = '';
                $downloadItems['downloadprice' . $i] = '0';
                $downloadItems['downloadlink' . $i] = $downloadLink;
                $downloadItems['downloadname' . $i] = $file->name;
                $downloadItems['downloadsize' . $i] = round($file->size / 1024);
                $downloadItems['downloadgpgfingerprint' . $i] = '';
                $downloadItems['downloadgpgsignature' . $i] = '';
                $downloadItems['downloadpackagename' . $i] = '';
                $downloadItems['downloadrepository' . $i] = '';
                $downloadItems['download_package_type' . $i] = $tags['packagetypeid'];
                $downloadItems['download_package_arch' . $i] = $tags['packagearch'];
                $i++;
            }
        }

        $cache->save(array($downloadItems, $downloads), $cacheName);

        return array($downloadItems, $downloads);
    }

    /**
     * @param string $fileTags
     *
     * @return array
     */
    protected function _parseFileTags($fileTags)
    {
        $tags = explode(',', $fileTags);
        $parsedTags = array(
            'link'          => '',
            'licensetype'   => '',
            'packagetypeid' => '',
            'packagearch'   => ''
        );
        foreach ($tags as $tag) {
            $tag = trim($tag);
            if (strpos($tag, 'link##') === 0) {
                $parsedTags['link'] = urldecode(str_replace('link##', '', $tag));
            } else {
                if (strpos($tag, 'licensetype-') === 0) {
                    $parsedTags['licensetype'] = str_replace('licensetype-', '', $tag);
                } else {
                    if (strpos($tag, 'packagetypeid-') === 0) {
                        $parsedTags['packagetypeid'] = str_replace('packagetypeid-', '', $tag);
                    } else {
                        if (strpos($tag, 'packagearch-') === 0) {
                            $parsedTags['packagearch'] = str_replace('packagearch-', '', $tag);
                        }
                    }
                }
            }
        }

        return $parsedTags;
    }

    /**
     * @param array      $previewPicSize
     * @param array      $smallPreviewPicSize
     * @param Ppload_Api $pploadApi
     *
     * @return array
     */
    protected function fetchCategoryContent(
        $previewPicSize,
        $smallPreviewPicSize,
        $pploadApi
    ) {
        $limit = 10; // 1 - 100
        $offset = 0;

        $tableProject = new Default_Model_Project();
        $tableProjectSelect = $this->_buildProjectSelect($tableProject, true);

        if (!empty($this->_params['categories'])) {
            // categories parameter: values separated by ","
            // legacy OCS API compatible: values separated by "x"
            if (strpos($this->_params['categories'], ',') !== false) {
                $catList = explode(',', $this->_params['categories']);
            } else {
                $catList = explode('x', $this->_params['categories']);
            }

            $modelProjectCategories = new Default_Model_DbTable_ProjectCategory();
            $allCategories = array_merge($catList, $modelProjectCategories->fetchChildIds($catList));
            $tableProjectSelect->where('project.project_category_id IN (?)', $allCategories);
        }

        if (!empty($this->_params['xdg_types'])) {
            // xdg_types parameter: values separated by ","
            $xdgTypeList = explode(',', $this->_params['xdg_types']);
            $tableProjectSelect->where('category.xdg_type IN (?)', $xdgTypeList);
        }

        if (!empty($this->_params['package_types'])) {
            // package_types parameter: values separated by ","
            $packageTypeList = explode(',', $this->_params['package_types']);

            $storeConfig = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;
            $storePackageTypeIds = null;
            if ($storeConfig) {
                $storePackageTypeIds = $storeConfig['package_type'];
            }

            if ($storePackageTypeIds) {
                $tableProjectSelect->join(array(
                    'package_type' => new Zend_Db_Expr('(SELECT DISTINCT project_id FROM project_package_type WHERE '
                        . $tableProject->getAdapter()->quoteInto('package_type_id IN (?)', $packageTypeList) . ')')
                ), 'project.project_id = package_type.project_id', array());
            }
        }

        $hasSearchPart = false;
        if (!empty($this->_params['search'])) {
            foreach (explode(' ', $this->_params['search']) as $keyword) {
                if ($keyword && strlen($keyword) > 2) {
                    $tableProjectSelect->where('project.title LIKE ? OR project.description LIKE ?', "%$keyword%");
                    $hasSearchPart = true;
                }
            }
        }

        if (!empty($this->_params['user'])) {
            $tableProjectSelect->where('member.username = ?', $this->_params['user']);
        }

        if (!empty($this->_params['external'])) {
        }

        if (!empty($this->_params['distribution'])) {
            // distribution parameter: comma separated list of ids
        }

        if (!empty($this->_params['license'])) {
            // license parameter: comma separated list of ids
        }

        if (!empty($this->_params['sortmode'])) {
            // sortmode parameter: new|alpha|high|down
            switch (strtolower($this->_params['sortmode'])) {
                case 'new':
                    $tableProjectSelect->order('project.created_at DESC');
                    break;
                case 'alpha':
                    $tableProjectSelect->order('project.title ASC');
                    break;
                case 'high':
                    $tableProjectSelect->order(new Zend_Db_Expr('laplace_score(project.count_likes,project.count_dislikes) DESC'));
                    break;
                case 'down':
                    $tableProjectSelect->joinLeft(array('stat_downloads_quarter_year' => 'stat_downloads_quarter_year'),
                        'project.project_id = stat_downloads_quarter_year.project_id', array());
                    $tableProjectSelect->order('stat_downloads_quarter_year.amount DESC');
                    break;
                default:
                    break;
            }
        }

        if (isset($this->_params['pagesize'])
            && ctype_digit((string)$this->_params['pagesize'])
            && $this->_params['pagesize'] > 0
            && $this->_params['pagesize'] < 101) {
            $limit = $this->_params['pagesize'];
        }

        if (isset($this->_params['page'])
            && ctype_digit((string)$this->_params['page'])) {
            // page parameter: the first page is 0
            $offset = $limit * $this->_params['page'];
        }

        $tableProjectSelect->limit($limit, $offset);

        $projects = $tableProject->fetchAll($tableProjectSelect);
        $count = $tableProject->getAdapter()->fetchRow('select FOUND_ROWS() AS counter');

        if ($this->_format == 'json') {
            $response = array(
                'status'       => 'ok',
                'statuscode'   => 100,
                'message'      => '',
                'totalitems'   => $count['counter'],
                'itemsperpage' => $limit,
                'data'         => array()
            );
        } else {
            $response = array(
                'meta' => array(
                    'status'       => array('@text' => 'ok'),
                    'statuscode'   => array('@text' => 100),
                    'message'      => array('@text' => ''),
                    'totalitems'   => array('@text' => $count['counter']),
                    'itemsperpage' => array('@text' => $limit)
                ),
                'data' => array()
            );
        }

        if (!count($projects)) {
            return $response;
        }

        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = 'api_fetch_category_' . md5($tableProjectSelect->__toString());
        $contentsList = false;

        if (false === $hasSearchPart) {
            $contentsList = $cache->load($cacheName);
        }

        if (false == $contentsList) {
            $contentsList = $this->_buildContentList($previewPicSize, $smallPreviewPicSize, $pploadApi, $projects);
            if (false === $hasSearchPart) {
                $cache->save($contentsList, $cacheName, array(), 1800);
            }
        }

        if ($this->_format == 'json') {
            $response['data'] = $contentsList;
        } else {
            $response['data'] = array('content' => $contentsList);
        }

        return $response;
    }

    /**
     * @param $previewPicSize
     * @param $smallPreviewPicSize
     * @param $pploadApi
     * @param $projects
     *
     * @return array
     */
    protected function _buildContentList($previewPicSize, $smallPreviewPicSize, $pploadApi, $projects)
    {
        $contentsList = array();
        $helperTruncate = new Default_View_Helper_Truncate();
        foreach ($projects as $project) {
            $project->description = $helperTruncate->truncate(Default_Model_BBCode::renderHtml(Default_Model_HtmlPurify::purify($project->description)),300);

            $categoryXdgType = '';
            if (!empty($project->xdg_type)) {
                $categoryXdgType = $project->xdg_type;
            }

            $created = date('c', strtotime($project->created_at));
            $changed = date('c', strtotime($project->changed_at));

            $previewPage = $this->_uriScheme . '://' . $this->_config['website'] . '/p/' . $project->project_id;

            list($previewPics, $smallPreviewPics) = $this->getGalleryPictures($project, $previewPicSize, $smallPreviewPicSize);

            $downloads = $project->count_downloads_hive;
            list($downloadItems, $downloads) = $this->getPPLoadInfo($project, $pploadApi, $downloads);

            if ($this->_format == 'json') {
                $contentsList[] = array(
                        'details'     => 'summary',
                        'id'          => $project->project_id,
                        'name'        => $project->title,
                        'version'     => $project->version,
                        'typeid'      => $project->project_category_id,
                        'typename'    => $project->cat_title,
                        'xdg_type'    => $categoryXdgType,
                        'language'    => '',
                        'personid'    => $project->member_username,
                        'created'     => $created,
                        'changed'     => $changed,
                        'downloads'   => $downloads,
                        'score'       => $project->laplace_score,
                        'summary'     => '',
                        'description' => $project->description,
                        'comments'    => $project->count_comments,
                        'preview1'    => $previewPage,
                        'detailpage'  => $previewPage,
                        'tags'        => $project->tags
                    ) + $previewPics + $smallPreviewPics + $downloadItems;
            } else {
                foreach ($previewPics as $key => $value) {
                    $previewPics[$key] = array('@text' => $value);
                }
                foreach ($smallPreviewPics as $key => $value) {
                    $smallPreviewPics[$key] = array('@text' => $value);
                }
                if ($downloadItems) {
                    foreach ($downloadItems as $key => $value) {
                        $downloadItems[$key] = array('@text' => $value);
                    }
                }
                $contentsList[] = array(
                        'details'     => 'summary',
                        'id'          => array('@text' => $project->project_id),
                        'name'        => array('@text' => $project->title),
                        'version'     => array('@text' => $project->version),
                        'typeid'      => array('@text' => $project->project_category_id),
                        'typename'    => array('@text' => $project->cat_title),
                        'xdg_type'    => array('@text' => $categoryXdgType),
                        'language'    => array('@text' => ''),
                        'personid'    => array('@text' => $project->member_username),
                        'created'     => array('@text' => $created),
                        'changed'     => array('@text' => $changed),
                        'downloads'   => array('@text' => $downloads),
                        'score'       => array('@text' => $project->laplace_score),
                        'summary'     => array('@text' => ''),
                        'description' => array('@cdata' => $project->description),
                        'comments'    => array('@text' => $project->count_comments),
                        'preview1'    => array('@text' => $previewPage),
                        'detailpage'  => array('@text' => $previewPage),
                        'tags'        => array('@text' => $project->tags)
                    ) + $previewPics + $smallPreviewPics + $downloadItems;
            }
        }

        return $contentsList;
    }

    public function contentdownloadAction()
    {
        if (!$this->_authenticateUser()) {
            //$this->_sendErrorResponse(999, '');
        }

        $pploadApi = new Ppload_Api(array(
            'apiUri'   => PPLOAD_API_URI,
            'clientId' => PPLOAD_CLIENT_ID,
            'secret'   => PPLOAD_SECRET
        ));

        $project = null;
        $file = null;

        if ($this->getParam('contentid')) {
            $tableProject = new Default_Model_Project();
            $project = $tableProject->fetchRow(
                $tableProject->select()
                             ->where('project_id = ?', $this->getParam('contentid'))
                             ->where('status = ?', Default_Model_Project::PROJECT_ACTIVE));
        }

        if (!$project) {
            $this->_sendErrorResponse(101, 'content not found');
        }

        if ($project->ppload_collection_id
            && $this->getParam('itemid')
            && ctype_digit((string)$this->getParam('itemid'))) {
            $filesRequest = array(
                'collection_id'     => ltrim($project->ppload_collection_id, '!'),
                'ocs_compatibility' => 'compatible',
                'perpage'           => 1,
                'page'              => $this->getParam('itemid')
            );
            $filesResponse = $pploadApi->getFiles($filesRequest);
            if (isset($filesResponse->status)
                && $filesResponse->status == 'success') {
                $i = 0;
                $file = $filesResponse->files->$i;
            }
        }

        if (!$file) {
            $this->_sendErrorResponse(103, 'content item not found');
        }

        $tags = $this->_parseFileTags($file->tags);
        $downloadLink = PPLOAD_API_URI . 'files/download/' . 'id/' . $file->id . '/' . $file->name;

        if ($this->_format == 'json') {
            $response = array(
                'status'     => 'ok',
                'statuscode' => 100,
                'message'    => '',
                'data'       => array(
                    array(
                        'details'               => 'download',
                        'downloadway'           => 1,
                        'downloadlink'          => $downloadLink,
                        'mimetype'              => $file->type,
                        'gpgfingerprint'        => '',
                        'gpgsignature'          => '',
                        'packagename'           => '',
                        'repository'            => '',
                        'download_package_type' => $tags['packagetypeid'],
                        'download_package_arch' => $tags['packagearch']
                    )
                )
            );
        } else {
            $response = array(
                'meta' => array(
                    'status'     => array('@text' => 'ok'),
                    'statuscode' => array('@text' => 100),
                    'message'    => array('@text' => '')
                ),
                'data' => array(
                    'content' => array(
                        'details'               => 'download',
                        'downloadway'           => array('@text' => 1),
                        'downloadlink'          => array('@text' => $downloadLink),
                        'mimetype'              => array('@text' => $file->type),
                        'gpgfingerprint'        => array('@text' => ''),
                        'gpgsignature'          => array('@text' => ''),
                        'packagename'           => array('@text' => ''),
                        'repository'            => array('@text' => ''),
                        'download_package_type' => array('@text' => $tags['packagetypeid']),
                        'download_package_arch' => array('@text' => $tags['packagearch'])
                    )
                )
            );
        }

        $this->_sendResponse($response, $this->_format);
    }

    public function contentpreviewpicAction()
    {
        if (!$this->_authenticateUser()) {
            //$this->_sendErrorResponse(999, '');
        }

        $project = null;

        if ($this->getParam('contentid')) {
            $tableProject = new Default_Model_Project();
            $project = $tableProject->fetchRow($tableProject->select()
                                                            ->where('project_id = ?', $this->getParam('contentid'))
                                                            ->where('status = ?', Default_Model_Project::PROJECT_ACTIVE));
        }

        if (!$project) {
            //$this->_sendErrorResponse(101, 'content not found');
            header('Location: ' . $this->_config['icon']);
            exit;
        }

        $viewHelperImage = new Default_View_Helper_Image();
        $previewPicSize = array(
            'width'  => 100,
            'height' => 100
        );

        if (!empty($this->_params['size'])
            && strtolower($this->_params['size']) == 'medium') {
            $previewPicSize = array(
                'width'  => 770,
                'height' => 540
            );
        }

        $previewPicUri = $viewHelperImage->Image($project->image_small, $previewPicSize);

        header('Location: ' . $previewPicUri);
        exit;
    }

    /**
     * @param null  $tableCategories
     * @param null  $parentCategoryId
     * @param array $categoriesList
     *
     * @return array
     * @deprecated
     */
    protected function _buildCategoriesList($tableCategories = null, $parentCategoryId = null, $categoriesList = array())
    {
        $categories = null;

        // Top-level categories
        if (!$tableCategories) {
            $tableCategories = new Default_Model_DbTable_ProjectCategory();
            if (Zend_Registry::isRegistered('store_category_list')) {
                $categories = $tableCategories->fetchActive(Zend_Registry::get('store_category_list'));
            } else {
                $categories = $tableCategories->fetchAllActive();
            }
        } // Sub-categories
        else {
            if ($parentCategoryId) {
                $categories = $tableCategories->fetchImmediateChildren($parentCategoryId);
            }
        }

        // Build categories list
        if (!empty($categories)) {
            foreach ($categories as $category) {
                if (is_array($category)) {
                    $category = (object)$category;
                }

                $categoryName = $category->title;
                $categoryDisplayName = $category->title;
                if (!empty($category->name_legacy)) {
                    $categoryName = $category->name_legacy;
                }
                $categoryParentId = '';
                if (!empty($parentCategoryId)) {
                    $categoryParentId = $parentCategoryId;
                }
                $categoryXdgType = '';
                if (!empty($category->xdg_type)) {
                    $categoryXdgType = $category->xdg_type;
                }

                if ($this->_format == 'json') {
                    $categoriesList[] = array(
                        'id'           => $category->project_category_id,
                        'name'         => $categoryName,
                        'display_name' => $categoryDisplayName,
                        'parent_id'    => $categoryParentId,
                        'xdg_type'     => $categoryXdgType
                    );
                } else {
                    $categoriesList[] = array(
                        'id'           => array('@text' => $category->project_category_id),
                        'name'         => array('@text' => $categoryName),
                        'display_name' => array('@text' => $categoryDisplayName),
                        'parent_id'    => array('@text' => $categoryParentId),
                        'xdg_type'     => array('@text' => $categoryXdgType)
                    );
                }

                // Update the list recursive
                $categoriesList = $this->_buildCategoriesList($tableCategories, $category->project_category_id, $categoriesList);
            }
        }

        return $categoriesList;
    }

    public function commentsAction()
    {
        if ($this->_format == 'json') {
            $response = array(
                'status'     => 'ok',
                'statuscode' => 100,
                'message'    => '',
                'data'       => array()
            );
        } else {
            $response = array(
                'meta' => array(
                    'status'     => array('@text' => 'ok'),
                    'statuscode' => array('@text' => 100),
                    'message'    => array('@text' => ''),
                ),
                'data' => array()
            );
        }

        $commentType = (int)$this->getParam('comment_type', -1);
        if ($commentType != self::COMMENT_TYPE_CONTENT) {
            $this->_sendResponse($response, $this->_format);
        }

        $contentId = (int)$this->getParam('content_id', null);
        if (empty($contentId)) {
            $this->_sendResponse($response, $this->_format);
        }

        $page = (int)$this->getParam('page', 0) + 1;
        $pagesize = (int)$this->getParam('pagesize', 10);

        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = 'api_fetch_comments_' . md5("{$commentType}, {$contentId}, {$page}, {$pagesize}");

        if (($cachedResponse = $cache->load($cacheName))) {
            $this->_sendResponse($cachedResponse, $this->_format);
        }

        $modelComments = new Default_Model_ProjectComments();
        $comments = $modelComments->getCommentsHierarchic($contentId);

        if ($comments->count() == 0) {
            $this->_sendResponse($response, $this->_format);
        }

        $comments->setCurrentPageNumber($page);
        $comments->setItemCountPerPage($pagesize);

        $response['data'] = array('comment' => $this->_buildCommentList($comments->getCurrentItems()));
        $cache->save($response, $cacheName, array(), 1800);

        $this->_sendResponse($response, $this->_format);
    }

    /**
     * @param Traversable $currentItems
     *
     * @return array
     */
    protected function _buildCommentList($currentItems)
    {
        $commentList = array();
        foreach ($currentItems as $current_item) {
            if ($this->_format == 'json') {
                $comment = array(
                    'id'         => $current_item['comment_id'],
                    'subject'    => '',
                    'text'       => Default_Model_HtmlPurify::purify($current_item['comment_text']),
                    'childcount' => $current_item['childcount'],
                    'user'       => $current_item['username'],
                    'date'       => date('c', strtotime($current_item['comment_created_at'])),
                    'score'      => 0
                );
                if ($current_item['childcount'] > 0) {
                    $comment['children'] = $this->_buildCommentList($current_item['children']);
                }
            } else {
                $comment = array(
                    'id'         => array('@text' => $current_item['comment_id']),
                    'subject'    => array('@text' => ''),
                    'text'       => array('@text' => Default_Model_HtmlPurify::purify($current_item['comment_text'])),
                    'childcount' => array('@text' => $current_item['childcount']),
                    'user'       => array('@text' => $current_item['username']),
                    'date'       => array('@text' => date('c', strtotime($current_item['comment_created_at']))),
                    'score'      => array('@text' => 0)
                );
                if ($current_item['childcount'] > 0) {
                    $comment['children'] = $this->_buildCommentList($current_item['children']);
                }
            }
            $commentList[] = $comment;
        }

        return $commentList;
    }

}
