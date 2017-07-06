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
            'location'   => 'https://www.opendesktop.org/embed/v1/',
            'name'       => 'opendesktop.org',
            'icon'       => '',
            'termsofuse' => 'https://www.opendesktop.org/terms',
            'register'   => 'https://www.opendesktop.org/register',
            'version'    => '1.0',
            'website'    => 'www.opendesktop.org',
            'host'       => 'www.opendesktop.org',
            'contact'    => 'contact@opendesktop.org',
            'ssl'        => true,
            'baseurllocal'   => 'http://pling.local/',
            'baseurl'   => 'http://pling.cc/',
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
        $page = $this->getParam('page');
        $nopage = $this->getParam('nopage');   // with param nopage will only show prudusts list otherwise show member+pager+productlist
        $pageLimit = $this->getParam('pagelimit');
        
        if(empty($pageLimit)){
            $pageLimit = 5;
        }        

        if(empty($page)){
            $page = 1;
        }
        if(empty($user_id)){
            $response = array(
                'status'     => 'ok',
                'statuscode' => 100,
                'message'    => '',
                'totalitems' =>0,
                'data'       => array()
            );          
        }else{           

            $userProducts = $this->_getMemberProducts($user_id, $pageLimit, $page);
            if ($this->_format == 'json') {
                $response = array(
                    'status'     => 'ok',
                    'statuscode' => 100,
                    'message'    => '',
                    'totalitems' => count($userProducts),
                    'data'       => array()
                );
                if (!empty($userProducts)) {
                    //$response['data'] = $userProducts;
                    // create html     
                    if(empty($nopage)) {                 
                        //  init with member & pager & products       
                        $html = $this->_getHTMLMember($user_id)
                                    .$this->_getHTMLPager($user_id)
                                    .'<div id="opendesktopwidget-main-container">'
                                    .$this->_getHTMLProducts($userProducts)
                                    .'</div>';    
                    }else{
                        // for only ajax paging content
                        $html =$this->_getHTMLProducts($userProducts);
                    }                                
                    $response['html'] =$html;
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

    protected function _getHTMLMember($user_id)    
    {
        $html = '';
        $modelMember = new Default_Model_Member();
        $m = $modelMember->fetchMemberData($user_id);
        $helperImage = new Default_View_Helper_Image();  
        $html = $html.'<div class="opendesktopwidgetheader">';
        $html = $html.'<a href="https://www.opendesktop.org" target="_blank"><img class="opendesktoplogo" src="https://www.opendesktop.org/images_sys/store_opendesktop/logo.png" /></a>'; 
        $html = $html.'<img class="profile_image" src="'.$helperImage->Image($m['profile_image_url'], array('width' => 110, 'height' => 110)).'" />';                 
        $html = $html.'</div> <!--end of header-->';
        return $html;

    }

    protected function _getHTMLProducts($userProducts)
    {
        $helperImage = new Default_View_Helper_Image();
        $helperBuildProductUrl = new Default_View_Helper_BuildProductUrl();
        $printRating= new Default_View_Helper_PrintRatingWidgetSimple();
        $helperPrintDate = new Default_View_Helper_PrintDate();
        $html = '';  
        foreach ($userProducts as $p) {        
                $html = $html.'<div class="opendesktopwidgetrow" id="opendesktopwidgetrow_'.$p['id'].'">';                                    
                //$html = $html.'<a href="'.$this->_config['baseurl'].'p/'.$p['id'].'" target="_blank">';                          
                $html = $html.'<img class="image_small" src="'.$helperImage->Image($p['image_small'], array('width' => 167, 'height' => 167)).'" />'; 
                $html = $html.'<div class="description">';      
                $html = $html.'<span class="title">'.$p['title'].'</span>';      
                $html = $html.'<span class="version">'.$p['version'].'</span>';      
                $html = $html.'<span class="cat_name">'.$p['cat_name'].'</span>';      
                if($p['count_comments']>0){
                    $html = $html.'<span class="count_comments">'.$p['count_comments'].' comment' .($p['count_comments']>1?'s':'').'</span>';          
                }                                                        
                $html = $html.'</div><!--end of description-->';                           
                $html = $html.'<div class="rating">';
                $html = $html.$printRating->printRatingWidgetSimple($p['laplace_score'],$p['count_likes'],$p['count_dislikes']);
                $html = $html.'<span class="updatetime">'. $helperPrintDate->printDate($p['changed']).'</span>';
                $html = $html.'</div>';

                //$html = $html.'</a><!--end of a-->';                  
                $html = $html.'</div> <!-- end of opendesktopwidgetrow -->';

                // hidden detail row
                $html = $html.'<div class="opendesktopwidgetrowdetail " id="opendesktopwidgetrowdetail_'.$p['id'].'">';  
                $html = $html.'<span class="title">Description</span>';
                $html = $html.$p['desc'];

                // ppload files
                $html = $html.'<div class="opendesktopwidgetpploadfiles" data-ppload-collection-id="'.$p['ppload_collection_id'].'"></div>';
                
                $html = $html.'</div> <!-- end of opendesktopwidgetrowdetail -->';                
        }                              
        return $html;
    }

    protected function _getHTMLPager($user_id,$pageLimit=5,$page=1)
    {        
        $modelProject = new Default_Model_Project();
        $total_records = $modelProject->countAllProjectsForMember($user_id,true);
        $total_pages = ceil($total_records / $pageLimit);         
        $html = '<div class="opendesktopwidgetpager"><ul class="opendesktopwidgetpager">';
        for ($i=1; $i<=$total_pages; $i++) { 
            if($i==$page){
                $html = $html.'<li class="active"><span>'.$i.'</span></li>';                
            }else{
                $html = $html.'<li><span>'.$i.'</span></li>';                
            }
            
        };   
        $html = $html.'</ul></div>';         
        return $html;   
    }



    protected function _getMemberProducts($user_id,$pageLimit=5,$page=1)
    {
        $modelProject = new Default_Model_Project();
        $userProjects = $modelProject->fetchAllProjectsForMember($user_id, $pageLimit,($page - 1) * $pageLimit, true);
        
        $result = array();
        foreach ($userProjects as $project) {                       

                if ($this->_format == 'json') {
                    $result[] = array(
                        'id'           => $project['project_id'],
                        'title'           => Default_Model_HtmlPurify::purify($project['title']),
                        'desc'           => Default_Model_HtmlPurify::purify($project['description']),
                        'version'           =>Default_Model_HtmlPurify::purify($project['version']),                        
                        'cat_id'         =>$project['project_category_id'],
                        'cat_name' => $project['catTitle'],
                        'created'         =>$project['project_created_at'],
                        'changed' => $project['project_changed_at'],
                        'laplace_score' => $project['laplace_score'],
                        'image_small'    =>  $project['image_small'] ,
                        'count_dislikes' => $project['count_dislikes'],
                        'count_likes' => $project['count_likes'],
                        'count_comments'    =>  $project['count_comments'] ,
                        'ppload_collection_id' =>  $project['ppload_collection_id']                 
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
                        'count_dislikes' => array('@text' => $project['count_dislikes']),  
                        'count_likes' => array('@text' => $project['count_likes']),           
                        'count_comments'    =>array('@text' => $project['count_comments']) ,
                        'ppload_collection_id'    =>array('@text' => $project['ppload_collection_id'])      
                    );
                }                        
        }
        return $result;
    }

    protected function _getPploadFiles($ppload_collection_id)
    {
        $result = array();
         $pploadApi = new Ppload_Api(array(
             'apiUri'   => PPLOAD_API_URI,
             'clientId' => PPLOAD_CLIENT_ID,
             'secret'   => PPLOAD_SECRET
         ));
        if ($ppload_collection_id)
        {
             $filesRequest = array(
                 'collection_id' => ltrim($ppload_collection_id, '!'),
                  'perpage'       => 100            
             );
             $filesResponse = $pploadApi->getFiles($filesRequest);

             if (isset($filesResponse->status)  && $filesResponse->status == 'success') {
                 $i=0;
                 foreach ($filesResponse->files as $file) {                                         
                     $downloadLink = PPLOAD_API_URI . 'files/download/'. 'id/' . $file->id . '/' . $file->name;
                     $tags = $this->_parseFileTags($file->tags);
                     $p_type = $this->_getPackagetypeText($tags['packagetypeid']);
                     $p_lice = $this->_getLicenceText($tags['licensetype']);
                     $result[] = array( 
                                                        'id' =>$file->id,                        
                                                        'downloadlink'=>$downloadLink,
                                                        'name'=> $file->name,
                                                        'version'=> $file->version,
                                                        'description'=> $file->description,
                                                        'type'=> $file->type,                                                        
                                                        'downloaded_count'  => $file->downloaded_count,
                                                        'size'                         => round($file->size / (1024*1024),2),      
                                                        'license'                    => $p_lice,
                                                        'package_type'          => $p_type,                                                     
                                                        'package_arch'          => $tags['packagearch'],
                                                        'ghns'                        =>$tags['ghns'],
                                                        'created'                    =>$file->created_timestamp ,
                                                        'updated'                   =>$file->updated_timestamp 
                        );
                 }
             }
         }
        return $result;
    }
    
    protected function _getHTMLFiles($files)
    {
        if(count($files)==0) return '';
        $helperPrintDate = new Default_View_Helper_PrintDate();
         $html = '<div class="opendesktoppploadfiles">';
         $html = $html.'<table class="opendesktoppploadfilestable">';         
         $html = $html.'<thead><tr><th>File</th><th>Version</th><th>Description</th><th>Filetype</th><th>Packagetype</th><th>License</th>';         
         $html = $html.'<th>Downloads</th><th>Date</th><th>Filesize</th></tr></thead>';
         $html = $html.'<tbody>';
         foreach ($files as $file) {                   
               $html = $html.'<tr>';
               $html = $html.'<td><a href="'.$file['downloadlink'].'" >'.$file['name'].'</a></td>';
               $html = $html.'<td>'.$file['version'].'</td>';
               $html = $html.'<td>'.$file['description'].'</td>';
               $html = $html.'<td>'.$file['type'].'</td>';
               $html = $html.'<td>'.$file['package_type'].'</td>';
               $html = $html.'<td>'.$file['license'].'</td>';
               $html = $html.'<td class="opendesktoppploadfilestabletdright">'.$file['downloaded_count'].'</td>';
               $html = $html.'<td>'.$file['created'].'</td>';
               $html = $html.'<td class="opendesktoppploadfilestabletdright">'.$file['size'].'MB</td>';
               $html = $html.'</tr>';
         }
         $html = $html.'</tbody></table> </div>';         
         return $html;
    }


    public function pploadAction()
    {
        $downloadItems = array();
        
        $ppload_collection_id = $this->getParam('ppload_collection_id');

        $count_downloads_hive = $this->getParam('count_downloads_hive');
        if(empty($count_downloads_hive)){
            $downloads = 0;
        }else{
            $downloads = $count_downloads_hive;    
        }

        $files = $this->_getPploadFiles($ppload_collection_id);        
        $html='';
        $html = $this->_getHTMLFiles($files);
        
        if ($this->_format == 'json') {
            $response = array(
                'status'     => 'ok',
                'statuscode' => 100,
                'message'    => '',
                'totalitems' => count($files),
                'html'       => $html
            );
            
            $this->_sendResponse($response, $this->_format);              
        } else {
                // xml TODO
        }
    }

    protected function _getLicenceText($id)
    {

        $typetext = '';
        switch ($id) {
            case 0:
                $typetext =  'Other';
                break;
            case 1:
                $typetext =  'GPLv2 or later';
                break;
            case 2:
                $typetext =  'LGPL';
                break;
            case 3:
                $typetext =  'Artistic 2.0';
                break;   
            case 4:
                $typetext =  'X11';
                break;   
            case 5:
                $typetext =  'QPL';
                break;   
            case 6:
                $typetext =  'BSD';
                break;   
            case 7:
                $typetext =  'Proprietary License';
                break;   
            case 8:
                $typetext =  'GFDL';
                break;     
            case 9:
                $typetext =  'CPL 1.0';
                break;    
            case 10:
                $typetext =  'Creative Commons by';
                break;     
            case 11:
                $typetext =  'Creative Commons by-sa';
            case 12:
                $typetext =  'Creative Commons by-nd';
                break;
            case 13:
                $typetext =  'Creative Commons by-nc';
                break;
            case 14:
                $typetext =  'Creative Commons by-nc-sa';
                break;   
            case 15:
                $typetext =  'Creative Commons by-nc-nd';
                break;   
            case 16:
                $typetext =  'AGPL';
                break;   
            case 18:
                $typetext =  'GPLv2 only';
                break;   
            case 19:
                $typetext =  'GPLv3';
                break;               
        }
        return $typetext;

    }

    protected function _getPackagetypeText($typid)
    {
        $typetext = '';
        switch ($typid) {
            case 1:
                $typetext =  'AppImage';
                break;
            case 2:
                $typetext =  'Android (APK)';
                break;
            case 3:
                $typetext =  'OS X compatible';
                break;   
            case 4:
                $typetext =  'Windows executable';
                break;   
            case 5:
                $typetext =  'Debian';
                break;   
            case 6:
                $typetext =  'Snappy';
                break;   
            case 7:
                $typetext =  'Flatpak';
                break;   
            case 8:
                $typetext =  'Electron-Webapp';
                break;     
            case 9:
                $typetext =  'Arch';
                break;    
            case 10:
                $typetext =  'open/Suse';
                break;     
            case 11:
                $typetext =  'Redhat';
                break;        
        }
        return $typetext;
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
            'packagearch'   => '',
            'ghns'          => ''
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
                        } else {
                            if (strpos($tag, 'ghns-') === 0) {
                                $parsedTags['ghns'] = str_replace('ghns-', '', $tag);
                            }
                        }
                    }
                }
            }
        }
        return $parsedTags;
    }
}
