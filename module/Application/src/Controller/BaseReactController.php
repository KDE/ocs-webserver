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
use Application\Model\Entity\CurrentStore;
use Application\Model\Entity\CurrentUser;
use Laminas\Config\Config;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;
use Laminas\Log\Logger;
use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;

/**
 * Class BaseReactController
 *
 * @package Application\Controller
 * @method FlashMessenger flashMessenger(string|null $namespace = null)
 * @method CurrentUser currentUser($useCachedUser = true)
 * @method CurrentHttpHost currentHost()
 * @method array MemberSettingItem($setting_id, $use_cached_User = true)
 * @method Config configHelp($key)
 */
class BaseReactController extends AbstractActionController
{

    const METAHEADER_DEFAULT = 'meta_keywords';
    const METAHEADER_DEFAULT_TITLE = 'opendesktop.org';
    const METAHEADER_DEFAULT_DESCRIPTION = 'A community where developers and artists share applications, themes and other content';
    const METAHEADER_DEFAULT_KEYWORDS = 'opendesktop,linux,kde,gnome,themes,apps,desktops,applications,addons,artwork,wallpapers';

    /** @var CurrentStore */
    protected $ocsStore;
    /** @var CurrentUser */
    protected $ocsUser;
    /** @var Logger */
    protected $ocsLog;
    /** @var Config */
    protected $ocsConfig;
    /** @var Adapter */
    protected $db;
    /** @var CurrentUser */
    protected $_authMember;
    protected $view;

    protected $templateConfigData;
    public function __construct()
    {
        $this->ocsStore = $GLOBALS['ocs_store'];
        $this->ocsUser = $GLOBALS['ocs_user'];
        $this->ocsLog = $GLOBALS['ocs_log'];
        $this->ocsConfig = $GLOBALS['ocs_config'];
        $this->db = GlobalAdapterFeature::getStaticAdapter();
        $this->_authMember = $GLOBALS['ocs_user'];
        $this->init();
    }

    /**
     * @return bool
     */
    protected function isAdmin()
    {
        return $this->currentUser()->isAdmin();
    }

    /**
     * @return string
     */
    protected function getHeadTitle()
    {
        $headTitle = $GLOBALS['ocs_store']->template['head']['browser_title'];
        if ($headTitle == self::METAHEADER_DEFAULT) {
            $headTitle = self::METAHEADER_DEFAULT_TITLE;
        }

        return $headTitle;
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

    public function init()
    {
      
        $this->initTemplateData();
        $this->initView();
        $this->setLayout();
      
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

        //$this->templateConfigData = StoreTemplate::getStoreTemplate($this->getNameForStoreClient());
    }

    public function initView()
    {        
        $this->view = new ViewModel();
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

    // protected function setLayout()
    // {
    //     $layoutName = 'layout/pling-ui';
    //     $storeConfig = $GLOBALS['ocs_store'] ? $GLOBALS['ocs_store']->config : null;
    //     if ($storeConfig && $storeConfig->layout) {
    //         $this->layout()->setTemplate($storeConfig->layout);
    //     } else {
    //         $this->layout()->setTemplate($layoutName);
    //     }
    // }

    protected function setLayout()
    {
        $this->layout()->setTemplate('layout/pling-ui');      
    }

    /**
     * @param $inputCatId
     *
     * @return string|null
     */
    protected function getCategoryAbout($inputCatId)
    {        
        $include_path_cat = $this->ocsConfig->settings->static->include_path . '/category_about/' . $inputCatId . '.phtml';         
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
        $include_path_cat = $this->ocsConfig->settings->static->include_path . '/store_about/' . $storeId . '.phtml';
        if (file_exists($include_path_cat)) {
            return $include_path_cat;
        }
        return null;
    }
}