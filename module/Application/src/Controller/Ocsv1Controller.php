<?php /** @noinspection PhpUnused */

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
 * */

namespace Application\Controller;

use Application\Model\Repository\MemberRepository;
use Application\Model\Repository\ProjectCategoryRepository;
use Application\Model\Service\MemberService;
use Application\Model\Service\ProjectCategoryService;
use DOMDocument;
use DOMElement;
use DOMNode;
use Laminas\Cache\Storage\Adapter\AbstractAdapter;
use Laminas\Mvc\Controller\AbstractActionController;

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
 * 12 = Source Code
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
class Ocsv1Controller extends AbstractActionController
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
        'user_host'  => 'pling.me',
    );
    protected $_params = array();
    protected $memberService;
    protected $memberRepository;
    protected $projectCategoryService;
    protected $projectCategoryRepository;

    public function __construct(
        MemberService $memberService,
        MemberRepository $memberRepository,
        ProjectCategoryService $projectCategoryService,
        ProjectCategoryRepository $projectCategoryRepository
    ) {
        $this->init();
        $this->memberService = $memberService;
        $this->memberRepository = $memberRepository;
        $this->projectCategoryService = $projectCategoryService;
        $this->projectCategoryRepository = $projectCategoryRepository;
    }

    public function init()
    {
        $this->initView();
        $this->_initUriScheme();
        $this->_initRequestParamsAndFormat();
        $this->_initConfig();
        $this->_initResponseHeader();
    }

    public function initView()
    {
        $this->layout()->setTemplate(null);
        //$this->setTerminal(true);
        // Disable render view
        //$this->_helper->layout->disableLayout();
        //$this->_helper->viewRenderer->setNoRender(true);
    }

    protected function _initUriScheme()
    {
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] === '1')) {
            $this->_uriScheme = 'https';
        } else {
            $this->_uriScheme = 'http';
        }
    }

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
                $GLOBALS['ocs_log']->err(
                    __METHOD__ . ' - request method not supported - ' . $_SERVER['REQUEST_METHOD']
                );
                exit('request method not supported');
        }

        // Set format option
        if (isset($this->_params['format']) && strtolower($this->_params['format']) == 'json') {
            $this->_format = 'json';
        }
    }

    protected function _initConfig()
    {
        $clientConfig = $GLOBALS['ocs_store']->template;

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
                             'host'       => $_SERVER['SERVER_NAME'],
                         ) + $this->_config;
    }

    protected function _initResponseHeader()
    {
        $duration = 1800; // in seconds
        $expires = gmdate("D, d M Y H:i:s", time() + $duration) . " GMT";

        $this->getResponse()->setMetadata('X-FRAME-OPTIONS', 'SAMEORIGIN', true)
//             ->setHeader('Last-Modified', $modifiedTime, true)
             ->setMetadata('Expires', $expires, true)->setMetadata('Pragma', 'cache', true)->setMetadata(
                'Cache-Control', 'max-age=1800, public', true
            );
    }

    public function indexAction()
    {
        $this->_sendErrorResponse(999, 'unknown request');
    }

    protected function _sendErrorResponse($statuscode, $message = '', $local = false)
    {
        if ($this->_format == 'json') {
            $response = array(
                'status'     => 'failed',
                'statuscode' => $statuscode,
                'message'    => $message,
            );
        } else {
            $response = array(
                'meta' => array(
                    'status'     => array('@text' => 'failed'),
                    'statuscode' => array('@text' => $statuscode),
                    'message'    => array('@text' => $message),
                ),
            );
        }

        $this->_sendResponse($response, $this->_format, $xmlRootTag = 'ocs', $local);
    }

    protected function _sendResponse($response, $format = 'xml', $xmlRootTag = 'ocs', $local = false)
    {
        header('Pragma: public');
        header('Cache-Control: cache, must-revalidate');
        $duration = 1800; // in seconds
        $expires = gmdate("D, d M Y H:i:s", time() + $duration) . " GMT";
        header('Expires: ' . $expires);
        if ($format == 'json') {
            header('Content-Type: application/json; charset=UTF-8');
            if ($local) {
                echo json_encode($response);
            } else {
                echo $response;
            }
        } else {
            header('Content-Type: application/xml; charset=UTF-8');
            if ($local) {
                echo $this->_convertXmlDom($response, $xmlRootTag)->saveXML();
            } else {
                echo $response;
            }
        }

        exit;
    }

    protected function _convertXmlDom($values, $tagName = 'data', DOMNode &$dom = null, DOMElement &$element = null)
    {
        if (!$dom) {
            $dom = new DOMDocument('1.0', 'UTF-8');
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
                    } else {
                        if ($key == '@cdata') {
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
                    'content' => array('ocsversion' => $this->_config['version']),
                ),
            ),
        );

        $this->_sendResponse($response, 'xml', 'providers', true);
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
                    'ssl'     => $this->_config['ssl'],
                ),
            );
        } else {
            $response = array(
                'meta' => array(
                    'status'     => array('@text' => 'ok'),
                    'statuscode' => array('@text' => 100),
                    'message'    => array('@text' => ''),
                ),
                'data' => array(
                    'version' => array('@text' => $this->_config['version']),
                    'website' => array('@text' => $this->_config['website']),
                    'host'    => array('@text' => $this->_config['host']),
                    'contact' => array('@text' => $this->_config['contact']),
                    'ssl'     => array('@text' => $this->_config['ssl']),
                ),
            );
        }

        $this->_sendResponse($response, $this->_format, 'ocs', true);
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
                        'personid' => $this->_authData->username,
                        'ptype'    => $this->_authData->password_type,
                    ),
                ),
            );
        } else {
            $response = array(
                'meta' => array(
                    'status'     => array('@text' => 'ok'),
                    'statuscode' => array('@text' => 100),
                    'message'    => array('@text' => ''),
                ),
                'data' => array(
                    'person' => array(
                        'details'  => 'check',
                        'personid' => array('@text' => $this->_authData->username),
                        'ptype'    => array('@text' => $this->_authData->password_type),
                    ),
                ),
            );
        }

        $this->_sendResponse($response, $this->_format, 'ocs', true);
    }

    protected function _authenticateUser($identity = null, $credential = null, $force = false)
    {
        return false;

        /*
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
          $authModel = new Authorization();
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
         *
         */
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

        $tableMember = $this->memberService;

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
                            'profilepage'          => $profilePage,
                        ),
                    ),
                );
            } else {
                $response = array(
                    'meta' => array(
                        'status'     => array('@text' => 'ok'),
                        'statuscode' => array('@text' => 100),
                        'message'    => array('@text' => ''),
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
                            'profilepage'          => array('@text' => $profilePage),
                        ),
                    ),
                );
            }

            $this->_sendResponse($response, $this->_format, 'ocs', true);
        } // Find a specific list of persons
        else {
            $limit = 10; // 1 - 100
            $offset = 0;

            $tableMember = $this->memberRepository;

            $tableMemberSelectWhere = 'is_active = 1 AND is_deleted = 0';

            if (!empty($this->_params['name'])) {
                $isSearchable = false;
                foreach (explode(' ', $this->_params['name']) as $keyword) {
                    if ($keyword && strlen($keyword) > 2) {
                        $tableMemberSelectWhere .= "username LIKE %$keyword% OR firstname LIKE %{$keyword}% OR lastname LIKE %{$keyword}%";
                        $isSearchable = true;
                    }
                }
                if (!$isSearchable) {
                    $tableMemberSelectWhere .= "username LIKE %{$this->_params['name']}% OR firstname LIKE %{$this->_params['name']}% OR lastname LIKE %{$this->_params['name']}%";
                }
            }
            if (!empty($this->_params['country'])) {
                $tableMemberSelectWhere .= "country = {$this->_params['country']}";
            }
            if (!empty($this->_params['city'])) {
                $tableMemberSelectWhere .= "city = {$this->_params['city']}";
            }
            if (!empty($this->_params['description'])) {
                $isSearchable = false;
                foreach (explode(' ', $this->_params['name']) as $keyword) {
                    if ($keyword && strlen($keyword) > 2) {
                        $tableMemberSelectWhere .= 'biography LIKE ' . "%$keyword%";
                        $isSearchable = true;
                    }
                }
                if (!$isSearchable) {
                    $tableMemberSelectWhere .= "biography LIKE %{$this->_params['description']}%";
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
            if (isset($this->_params['pagesize']) && ctype_digit(
                    (string)$this->_params['pagesize']
                ) && $this->_params['pagesize'] > 0 && $this->_params['pagesize'] < 101) {
                $limit = $this->_params['pagesize'];
            }
            if (isset($this->_params['page']) && ctype_digit((string)$this->_params['page'])) {
                // page parameter: the first page is 0
                $offset = $limit * $this->_params['page'];
            }

            $members = $tableMember->fetchAllRows($tableMemberSelectWhere, null, $limit, $offset);

            $count = count($members);

            if ($count > 1000) {
                $this->_sendErrorResponse(
                    102,
                    'more than 1000 people found.' . ' it is not allowed to fetch such a big resultset.' . ' please specify more search conditions'
                );
            }

            if ($this->_format == 'json') {
                $response = array(
                    'status'       => 'ok',
                    'statuscode'   => 100,
                    'message'      => '',
                    'totalitems'   => $count,
                    'itemsperpage' => $limit,
                    'data'         => array(),
                );
            } else {
                $response = array(
                    'meta' => array(
                        'status'       => array('@text' => 'ok'),
                        'statuscode'   => array('@text' => 100),
                        'message'      => array('@text' => ''),
                        'totalitems'   => array('@text' => $count),
                        'itemsperpage' => array('@text' => $limit),
                    ),
                    'data' => array(),
                );
            }

            if (!count($members)) {
                $this->_sendResponse($response, $this->_format, 'ocs', true);
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
                        'country'       => $member->country,
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
                        'country'       => array('@text' => $member->country),
                    );
                }
            }

            if ($this->_format == 'json') {
                $response['data'] = $personsList;
            } else {
                $response['data'] = array('person' => $personsList);
            }

            $this->_sendResponse($response, $this->_format, 'ocs', true);
        }
    }

    public function getParam($string)
    {
        $val = $this->params()->fromQuery($string);
        if (null == $val) {
            $val = $this->params()->fromRoute($string, null);
        }
        if (null == $val) {
            $val = $this->params()->fromPost($string, null);
        }

        return $val;
    }

    public function contentcategoriesAction()
    {

        if (!$this->_authenticateUser()) {
            //    $this->_sendErrorResponse(999, '');
        }

        /** @var AbstractAdapter $cache */
        $cache = $GLOBALS['ocs_cache'];
        $storeName = $this->_getNameForStoreClient();
        $cacheName = 'api_content_categories' . md5($storeName);
        $categoriesList = $cache->getItem($cacheName);
        if (!$categoriesList || null == $categoriesList) {
            $categoriesList = $this->_buildCategories();
            $cache->setItem($cacheName, $categoriesList);
        }

        if ($this->_format == 'json') {
            $response = array(
                'status'     => 'ok',
                'statuscode' => 100,
                'message'    => '',
                'totalitems' => count($categoriesList),
                'data'       => array(),
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
                    'totalitems' => array('@text' => count($categoriesList)),
                ),
                'data' => array(),
            );
            if (!empty($categoriesList)) {
                $response['data'] = array('category' => $categoriesList);
            }
        }

        $this->_sendResponse($response, $this->_format, 'ocs', true);
    }

    /**
     * Returns the name for the store client.
     * If no name were found, the name for the standard store client will be returned.
     *
     * @return string
     */
    protected function _getNameForStoreClient()
    {
        return $GLOBALS['ocs_store'] ? $GLOBALS['ocs_store']->config->name : $GLOBALS['ocs_store']->settings->client->default->name;
    }

    protected function _buildCategories()
    {
        $modelCategoryTree = $this->projectCategoryService;
        $tree = $modelCategoryTree->fetchCategoryTreeCurrentStore();

        return $this->buildResponseTree($tree);
    }

    protected function buildResponseTree($tree)
    {
        $modelCategory = $this->projectCategoryRepository;
        $result = array();
        foreach ($tree as $element) {
            if ($this->_format == 'json') {
                $name = (false === empty($element['name_legacy'])) ? $element['name_legacy'] : $element['title'];
                //set parent name to name, if needed
                if (isset($element['parent_id'])) {
                    $parent = $modelCategory->findById($element['parent_id'])->getArrayCopy();
                    if ($parent) {
                        $name = $parent['title'] . "/ " . $name;
                    }
                }
                $result[] = array(
                    'id'           => $element['id'],
                    'name'         => $name,
                    'display_name' => $element['title'],
                    'parent_id'    => (false === empty($element['parent_id'])) ? $element['parent_id'] : '',
                    'xdg_type'     => (false === empty($element['xdg_type'])) ? $element['xdg_type'] : '',
                );
            } else {
                $name = (false === empty($element['name_legacy'])) ? $element['name_legacy'] : $element['title'];

                //set parent name to name, if needed
                if (isset($element['parent_id'])) {
                    $parent = $modelCategory->findById($element['parent_id'])->getArrayCopy();
                    if ($parent) {
                        $name = $parent['title'] . "/ " . $name;
                    }
                }

                $result[] = array(
                    'id'           => array('@text' => $element['id']),
                    'name'         => array('@text' => $name),
                    'display_name' => array('@text' => $element['title']),
                    'parent_id'    => array('@text' => (false === empty($element['parent_id'])) ? $element['parent_id'] : ''),
                    'xdg_type'     => array('@text' => (false === empty($element['xdg_type'])) ? $element['xdg_type'] : ''),
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

        $uri = "/ocs/v1/content/data";

        $contentId = (int)$this->params('contentid', null);
        if (null == $contentId) {
            $contentId = (int)$this->params()->fromRoute('content_id', null);
        }
        if (null == $contentId) {
            $contentId = (int)$this->params()->fromRoute('contentid', null);
        }

        $params = $this->_params;
        $params['content_id'] = $contentId;
        $params['domain_store_id'] = $this->_getNameForStoreClient();

        $result = $this->_request('GET', $uri, $params);

        $this->_sendResponse($result, $this->_format);
    }

    protected function _request($method, $uri = '', array $params = null)
    {

        $config = $GLOBALS['ocs_config'];
        //TODO check path
        $static_config = $config->settings->ocs_server;

        $ocsServer = $static_config->apiUri;

        $timeout = 60;
        $postFields = array();
        if ($params) {
            $postFields = $postFields + $params;
        }
        if (isset($postFields['file'])) {
            $timeout = 600;
            if ($postFields['file'][0] != '@') {
                $postFields['file'] = $this->_getCurlValue($postFields['file']);
            }
        } else {
            $postFields = http_build_query($postFields, '', '&');
        }

        //TODO
        // todo what????
        $uri = str_replace('/application', '', $uri);

        //var_dump($ocsServer . $uri);
        //var_dump($params);
        //die();

        $curl = curl_init();
        curl_setopt_array(
            $curl, array(
            CURLOPT_URL            => $ocsServer . $uri,
            CURLOPT_HEADER         => false,
            CURLOPT_POST           => false,
            CURLOPT_POSTFIELDS     => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
        )
        );

        $response = curl_exec($curl);
        curl_close($curl);

        //var_dump($response);
        //die();

        if ($response) {
            return $response;
        }

        return false;
    }

    public function contentdownloadAction()
    {

        $uri = "/ocs/v1/content/download/";

        $contentId = (int)$this->params('contentid', null);
        if (null == $contentId) {
            $contentId = (int)$this->params()->fromRoute('content_id', null);
        }
        if (null == $contentId) {
            $contentId = (int)$this->params()->fromRoute('contentid', null);
        }
        $itemId = (int)$this->params('itemid', null);
        if (null == $itemId) {
            $itemId = (int)$this->params()->fromRoute('itemid', null);
        }

        $uri .= $contentId . '/' . $itemId;

        $params = $this->_params;
        $params['contentid'] = $contentId;
        $params['itemid'] = $itemId;
        $params['domain_store_id'] = $this->_getNameForStoreClient();

        $result = $this->_request('GET', $uri, $params);
        $this->_sendResponse($result, $this->_format);
    }

    public function contentpreviewpicAction()
    {

        $uri = "/ocs/v1/content/previewpic/";

        $contentId = (int)$this->params('contentid', null);
        if (null == $contentId) {
            $contentId = (int)$this->params()->fromRoute('content_id', null);
        }
        if (null == $contentId) {
            $contentId = (int)$this->params()->fromRoute('contentid', null);
        }

        $uri .= $contentId;

        $config = $GLOBALS['ocs_config'];
        //TODO check path
        $static_config = $config->settings->ocs_server;

        $ocsServer = $static_config->apiUri;

        $uri = $ocsServer . $uri;

        $this->redirect()->toUrl($uri);

        return;
    }

    public function commentsAction()
    {
        $uri = "/ocs/v1/comments/data/1/1314549/";

        $config = $GLOBALS['ocs_config'];
        $static_config = $config->settings->ocs_server;

        $ocsServer = $static_config->apiUri;

        $params = $this->_params;
        $params['domain_store_id'] = $this->_getNameForStoreClient();

        //var_dump($ocsServer.$uri);

        $tuCurl = curl_init();
        curl_setopt($tuCurl, CURLOPT_URL, $ocsServer . $uri);
        curl_setopt($tuCurl, CURLOPT_PORT, 80);
        curl_setopt($tuCurl, CURLOPT_VERBOSE, 0);
        curl_setopt($tuCurl, CURLOPT_HEADER, 0);
        //curl_setopt($tuCurl, CURLOPT_SSLVERSION, 3);
        curl_setopt($tuCurl, CURLOPT_POST, 0);
        //curl_setopt($tuCurl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($tuCurl);

        $this->_sendResponse($response, $this->_format);
    }

    public function voteAction()
    {
        $this->_sendErrorResponse(405, 'method not allowed', true);
    }

}
