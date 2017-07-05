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

class Embedv1Controller extends Zend_Controller_Action
{

    protected $_authData = null;

    protected $_uriScheme = 'https';

    protected $_format = 'json';

    protected $_config
        = array(
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
        if (isset($_SERVER['HTTPS'])
            && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] === '1')
        ) {
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
                Zend_Registry::get('logger')->err(
                    __METHOD__ . ' - request method not supported - '
                    . $_SERVER['REQUEST_METHOD']
                );
                exit('request method not supported');
        }

        // Set format option
        if (isset($this->_params['format'])
            && strtolower($this->_params['format']) == 'json'
        ) {
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
                'location'   => $baseUri . '/embed/v1/',
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
        $clientConfigReader = new Backend_Model_ClientFileConfig(
            $this->_getNameForStoreClient()
        );
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
//            ->setHeader('Last-Modified', $modifiedTime, true)
            ->setHeader('Expires', $expires, true)
            ->setHeader('Pragma', 'cache', true)
            ->setHeader('Cache-Control', 'max-age=1800, public', true);
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

    protected function _sendResponse($response, $format = 'json', $xmlRootTag = 'ocs')
    {
        header('Pragma: public');
        header('Cache-Control: cache, must-revalidate');
        $duration = 1800; // in seconds
        $expires = gmdate("D, d M Y H:i:s", time() + $duration) . " GMT";
        header('Expires: ' . $expires);
        if ($format == 'json') {
           
           
            $callback = $this->getParam('callback');
            if ($callback != "")
            {
                header('Content-Type: text/javascript; charset=UTF-8');                
                // strip all non alphanumeric elements from callback
                $callback = preg_replace('/[^a-zA-Z0-9_]/', '', $callback);
                echo $callback. '('. json_encode($response). ')';   
            }else{
                header('Content-Type: application/json; charset=UTF-8');                
                echo json_encode($response);    
            }

            
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

    public function projectdetailAction(){
                 
         $product = $this->_getProject($this->getParam('projectid'));
            if ($this->_format == 'json') {
                $response = array(
                    'status'     => 'ok',
                    'statuscode' => 100,
                    'message'    => '',                   
                    'data'       => array()
                );
                if (!empty($product)) {
                    $response['data'] = $product;
                }
          
            } else {
                $response = array(
                    'meta' => array(
                        'status'     => array('@text' => 'ok'),
                        'statuscode' => array('@text' => 100),
                        'message'    => array('@text' => '')                       
                    ),
                    'data' => array()
                );
                if (!empty($product)) {
                    $response['data'] = array('project' => $product);
                }
            }

        $this->_sendResponse($response, $this->_format);
    }
    protected function _getProject($project_id){
        $modelProduct = new Default_Model_Project();
        $project = $modelProduct->fetchProductInfo($project_id);
        if ($project==null) {
            $this->_sendErrorResponse(101, 'content not found');
        }

        $result = array();
        if ($this->_format == 'json') {
            $result = array(
                'id'           => $project['project_id'],
                'title'           => $project['title'],
                'desc'           => $project['description'],
                'version'           => $project['version'],                        
                'cat_id'         =>$project['project_category_id'],
               
                'created'         =>$project['project_created_at'],
                'changed' => $project['project_changed_at'],
                'laplace_score' => $project['laplace_score'],
                'image_small'    =>  $project['image_small']                        
            );
        } else {
            $result= array(
                'id'           => array('@text' => $project['project_id']),
                'title'           => array('@text' => $project['title']), 
                'desc'           => array('@text' => $project['description']),
                'version'           =>array('@text' => $project['version']),                  
                'cat_id'         =>array('@text' => $project['project_category_id']),
             
                'created'         =>array('@text' => $project['project_created_at']),
                'changed' =>array('@text' => $project['project_changed_at']),
                'laplace_score' =>array('@text' => $project['laplace_score']),
                'image_small'    =>array('@text' => $project['image_small']),                       
            );
        }                       
       return $result;
    }

    public function memberprojectsAction()
    {        
        $user_id = $this->getParam('memberid');

        if(empty($user_id)){
            $response = array(
                'status'     => 'ok',
                'statuscode' => 100,
                'message'    => '',
                'totalitems' =>0,
                'data'       => array()
            );          
        }else{           
            $userProducts = $this->_getMemberProducts($user_id);
            if ($this->_format == 'json') {
                $response = array(
                    'status'     => 'ok',
                    'statuscode' => 100,
                    'message'    => '',
                    'totalitems' => count($userProducts),
                    'data'       => array()
                );
                if (!empty($userProducts)) {
                    $response['data'] = $userProducts;
                }
          
            } else {
                $response = array(
                    'meta' => array(
                        'status'     => array('@text' => 'ok'),
                        'statuscode' => array('@text' => 100),
                        'message'    => array('@text' => ''),
                        'totalitems' => array('@text' => count($userProducts))
                    ),
                    'data' => array()
                );
                if (!empty($userProducts)) {
                    $response['data'] = array('projects' => $userProducts);
                }
            }
        }
        $this->_sendResponse($response, $this->_format);
    }    

    protected function _getMemberProducts($user_id)
    {
        $modelProject = new Default_Model_Project();
        $userProjects = $modelProject->fetchAllProjectsForMember($user_id,null,null,true);
     
        $result = array();
        foreach ($userProjects as $project) {        
                
                if ($this->_format == 'json') {
                    $result[] = array(
                        'id'           => $project['project_id'],
                        'title'           => $project['title'],
                        'desc'           => $project['description'],
                        'version'           => $project['version'],                        
                        'cat_id'         =>$project['project_category_id'],
                        'cat_name' => $project['catTitle'],
                        'created'         =>$project['project_created_at'],
                        'changed' => $project['project_changed_at'],
                        'laplace_score' => $project['laplace_score'],
                        'image_small'    =>  $project['image_small']                        
                    );
                } else {
                    $result[] = array(
                        'id'           => array('@text' => $project['project_id']),
                        'title'           => array('@text' => $project['title']), 
                        'desc'           => array('@text' => $project['description']),
                        'version'           =>array('@text' => $project['version']),                  
                        'cat_id'         =>array('@text' => $project['project_category_id']),
                        'cat_name' =>array('@text' => $project['catTitle']),
                        'created'         =>array('@text' => $project['project_created_at']),
                        'changed' =>array('@text' => $project['project_changed_at']),
                        'laplace_score' =>array('@text' => $project['laplace_score']),
                        'image_small'    =>array('@text' => $project['image_small']),                       
                    );
                }                        
        }
        return $result;
    }

 

}
