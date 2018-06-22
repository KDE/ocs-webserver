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
             ->setHeader('Expires', $expires, true)
             ->setHeader('Pragma', 'cache', true)
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
            //echo json_encode($response);
            echo $response;
        } else {
            header('Content-Type: application/xml; charset=UTF-8');
            //echo $this->_convertXmlDom($response, $xmlRootTag)->saveXML();
            echo $response;
        }

        exit;
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
        $uri = $this->view->url();
        
        $params = $this->getRequest()->getParams();
        $params['domain_store_id'] = $this->_getNameForStoreClient();
        
        $result = $this->_request('GET', $uri, $params);
        $this->_sendResponse($result, $this->_format);
    }

    public function personselfAction()
    {
        $uri = $this->view->url();
        
        $params = $this->getRequest()->getParams();
        $params['domain_store_id'] = $this->_getNameForStoreClient();
        
        $result = $this->_request('GET', $uri, $params);
        $this->_sendResponse($result, $this->_format);
    }

    public function contentcategoriesAction()
    {
        
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $storeName = $this->_getNameForStoreClient();
        
        $storeNameParam = $this->getParam('domain_store_id') ? $this->getParam('domain_store_id') : "";
        
        $cacheName = 'api_content_categories'.md5($storeName);
        //$cacheName = 'api_content_categories';
        
        $debugMode = (int)$this->getParam('debug') ? (int)$this->getParam('debug') : false;
        
        

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
        
        if($debugMode) {
            $response['meta']['debug']['store_client_name'] = $this->_getNameForStoreClient();
            $response['meta']['debug']['parameter_store_client_name'] = $storeNameParam;
            $response['meta']['debug']['parameter'] = $this->getRequest()->getParams();
        }

        $this->_sendResponse($response, $this->_format);
        
    }
    
    /**
     * @return array
     */
    protected function _buildCategories()
    {
        $modelCategoryTree = new Application_Model_ProjectCategory();
        $tree = $modelCategoryTree->fetchCategoryTreeCurrentStore();

        return $this->buildResponseTree($tree);
    }

    /**
     * @param array $tree
     *
     * @return array
     */
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
                    'name'         => array(
                        '@text' => (false === empty($element['name_legacy'])) ? $element['name_legacy'] : $element['title']
                    ),
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
        
        $uri = $this->view->url();
        
        $params = $this->getRequest()->getParams();
        $params['domain_store_id'] = $this->_getNameForStoreClient();
        
        $result = $this->_request('GET', $uri, $params);
        $this->_sendResponse($result, $this->_format);
        
    }

    public function contentdownloadAction()
    {
        
        $uri = $this->view->url();
        
        $params = $this->getRequest()->getParams();
        $params['domain_store_id'] = $this->_getNameForStoreClient();
        
        $result = $this->_request('GET', $uri, $params);
        $this->_sendResponse($result, $this->_format);
        
    }

    public function contentpreviewpicAction()
    {
        
        $uri = $this->view->url();
        
        $params = $this->getRequest()->getParams();
        $params['domain_store_id'] = $this->_getNameForStoreClient();
        
        $result = $this->_request('GET', $uri, $params);
        $this->_sendResponse($result, $this->_format);
        
    }


    public function commentsAction()
    {
        $uri = $this->view->url();
        
        $params = $this->getRequest()->getParams();
        $params['domain_store_id'] = $this->_getNameForStoreClient();
        
        $result = $this->_request('GET', $uri, $params);
        $this->_sendResponse($result, $this->_format);

    }
    
    protected function _request($method, $uri = '', array $params = null)
    {
        $ocsServer = "http://api.pling.cc"; //$this->_config['apiUri']
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
        }
        else {
            $postFields = http_build_query($postFields, '', '&');
        }
        
        //var_dump($ocsServer . $uri . '?' . $postFields);
        
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $ocsServer . $uri,
            CURLOPT_HEADER => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            return $response;
        }
        return false;
    }

}
