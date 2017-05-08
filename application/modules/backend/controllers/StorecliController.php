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
 *
 * Created: 08.05.2017
 */

class Backend_StorecliController extends Local_Controller_Action_CliAbstract
{

    protected $_model;

    public function runAction()
    {
        $this->_model = new Default_Model_DbTable_ConfigStore();

        $this->initGlobalApplicationVars();
        $allStoresConfig = $this->_model->fetchAllStoresConfigArray(true);

        $modelPCat = new Default_Model_ProjectCategory();
        foreach ($allStoresConfig as $config) {
            $modelPCat->fetchCategoryTreeForStore($config['store_id'], true);
            $this->_model->fetchConfigForStore($config['store_id'], true);
        }
    }

    private function initGlobalApplicationVars()
    {
        $modelDomainConfig = new Default_Model_DbTable_ConfigStore();
        Zend_Registry::set('application_store_category_list', $modelDomainConfig->fetchAllStoresAndCategories());
        Zend_Registry::set('application_store_config_list', $modelDomainConfig->fetchAllStoresConfigArray());
    }

}