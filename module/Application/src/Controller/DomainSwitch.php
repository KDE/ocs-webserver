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

namespace Application\Controller;

use Application\Controller\Plugin\CurrentHttpHost;
use Application\Model\Entity\CurrentUser;
use Application\Model\Service\Util;
use Exception;
use Laminas\Config\Config;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\ViewModel;
use Library\IpAddress;

/**
 * Class DomainSwitch
 *
 * @package Application\Controller
 * @method FlashMessenger flashMessenger(string|null $namespace = null)
 * @method CurrentUser currentUser($useCachedUser = true)
 * @method CurrentHttpHost currentHost()
 * @method array MemberSettingItem($setting_id, $use_cached_User = true)
 * @method Config configHelp($key)
 */
class DomainSwitch extends AbstractActionController
{

    const METAHEADER_DEFAULT = 'meta_keywords';
    const METAHEADER_DEFAULT_TITLE = 'opendesktop.org';
    const METAHEADER_DEFAULT_DESCRIPTION = 'A community where developers and artists share applications, themes and other content';
    const METAHEADER_DEFAULT_KEYWORDS = 'opendesktop,linux,kde,gnome,themes,apps,desktops,applications,addons,artwork,wallpapers';

    protected $_request = null;
    /** @var CurrentUser */
    protected $_authMember;
    protected $templateConfigData;
    protected $defaultConfigName;

    protected $db;
    protected $config;
    protected $cache;
    protected $view;

    /**
     * DomainSwitch constructor.
     *
     * @param AdapterInterface $db
     * @param array            $config
     * @param Request          $request
     */
    public function __construct(AdapterInterface $db, array $config, Request $request)
    {
        $this->db = $db;
        $this->config = Util::arrayToObject($config['ocs_config']);
        $this->_request = $request;
        $this->cache = $GLOBALS['ocs_cache'];
        $this->view = new ViewModel(array());
        $this->view->setVariable('ocs_user', $GLOBALS['ocs_user']);
        $this->view->setVariable('db', $db);
        $this->view->setVariable('request', $request);
        $this->view->setVariable('config', $this->config);
        $this->view->setVariable('ocs_store', $GLOBALS['ocs_store']);

        $this->init();

    }

    public function init()
    {
        $this->initDefaultConfigName();
        $this->initAuth();
        $this->initTemplateData();
        $this->initView();
        $this->setLayout();
        $this->_initResponseHeader();
    }

    protected function initDefaultConfigName()
    {
        $this->defaultConfigName = $this->config->settings->client->default->name;
    }

    protected function initAuth()
    {
       $this->_authMember = $GLOBALS['ocs_user'];
    }

    /**
     * @throws Exception
     */
    private function initTemplateData()
    {
        if ($GLOBALS['ocs_store']) {
            $this->templateConfigData = $GLOBALS['ocs_store']->template;

            return;
        }       
    }

    public function initView()
    {
        if (!$GLOBALS['headMetaSet']) {
            $headTitle = $this->getHeadTitle();
            $headDesc = $this->templateConfigData['head']['meta_description'];
            $headKeywords = $this->templateConfigData['head']['meta_keywords'];
            //set default site-title

            //$this->view->headTitle()->append($headTitle);
            $this->view->setVariable('headTitle', $headTitle);
            if ($headDesc == $this::METAHEADER_DEFAULT) {
                $headDesc = $this::METAHEADER_DEFAULT_DESCRIPTION;
            }
            if ($headKeywords == $this::METAHEADER_DEFAULT) {
                $headKeywords = $this::METAHEADER_DEFAULT_KEYWORDS;
            }


            $this->getResponse()
                 ->setMetadata('author', $this->templateConfigData['head']['meta_author'])
                 ->setMetadata(
                     'robots', 'all'
                 )
                 ->setMetadata('robots', 'index')
                 ->setMetadata('robots', 'follow')
                 ->setMetadata(
                     'revisit-after', '3 days'
                 )
                 ->setMetadata('title', $headTitle)
                 ->setMetadata('description', $headDesc, array('lang' => 'en-US'))
                 ->setMetadata('keywords', $headKeywords, array('lang' => 'en-US'));

            $this->view->setVariable('headMeta', $this->getResponse()->getMetadata());

            $headMetaSet = true;
        }

        //$this->view->template = $this->templateConfigData;
        $this->view->setVariable('template', $this->templateConfigData);
    }

    public function getHeadTitle()
    {
        $headTitle = $this->templateConfigData['head']['browser_title'];
        if ($headTitle == $this::METAHEADER_DEFAULT) {
            $headTitle = $this::METAHEADER_DEFAULT_TITLE;
        }

        return $headTitle;
    }

    protected function setLayout()
    {
        $layoutName = 'layout/flat-ui';
        $storeConfig = $GLOBALS['ocs_store'] ? $GLOBALS['ocs_store']->config : null;
        if ($storeConfig && $storeConfig->layout) {
            $this->layout()->setTemplate($storeConfig->layout);
        } else {
            $this->layout()->setTemplate($layoutName);
        }
    }

    protected function _initResponseHeader()
    {
        $duration = 1800; // in seconds
        $expires = gmdate("D, d M Y H:i:s", time() + $duration) . " GMT";

        /*
        $this->getResponse()
             ->setHeader('X-FRAME-OPTIONS', 'ALLOWALL', true)
//            ->setHeader('Last-Modified', $modifiedTime, true)
             ->setHeader('Expires', $expires, true)
             ->setHeader('Pragma', 'no-cache', true)
             ->setHeader('Cache-Control', 'private, no-store, no-cache, must-revalidate, post-check=0, pre-check=0',true);
         *
         */
    }

    /**
     * @return string
     */
    public static function get_ip_address()
    {
        return IpAddress::get_ip_address();
    }

    /**
     * Returns the name for the client. If no name were found, the name for the standard client will be returned.
     *
     * @return string
     */
    public function getNameForStoreClient()
    {
        $clientName = $this->defaultConfigName; //set to default

        if ($GLOBALS['ocs_store']) {
            $clientName = $GLOBALS['ocs_store']->config->config_id_name;
        }

        return $clientName;
    }

    /**
     * @param $inputCatId
     *
     * @return string|null
     */
    protected function getCategoryAbout($inputCatId)
    {        
        $include_path_cat = $this->config->settings->static->include_path . '/category_about/' . $inputCatId . '.phtml';

        if (file_exists($include_path_cat)) {
            return $include_path_cat;
        }
        return null;
    }

    /**
     *
     * @param int $storeId
     *
     * @return string|null
     */
    protected function getStoreAbout($storeId)
    {           
        $include_path_cat = $this->config->settings->static->include_path . '/store_about/' . $storeId . '.phtml';
        if (file_exists($include_path_cat)) {
            return $include_path_cat;
        }
        return null;
    }

    public function getParam($string, $default = null)
    {
        $val = $this->params()->fromQuery($string);
        if (null == $val) {
            $val = $this->params()->fromRoute($string, null);
        }
        if (null == $val) {
            $val = $this->params()->fromPost($string, null);
        }
        if (null == $val) {
            $val = $default;
        }

        return $val;
    }

    
}