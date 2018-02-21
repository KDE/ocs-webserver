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
class Backend_SearchController extends Local_Controller_Action_CliAbstract
{

    public function runAction()
    {
        $storeId = (int)$this->getParam('store_id');
        $indexId = $this->getParam('index_id');

        if (isset($storeId) AND isset($indexId)) {
            $this->createStoreIndex($storeId, $indexId);
        } else {
            Zend_Registry::get('logger')->warn(__METHOD__ . ' - storeId and/or indexId is missing.');
        }
    }

    protected function createStoreIndex($storeId, $indexId)
    {
        $queue = Local_Queue_Factory::getQueue('search');
        $command = new Backend_Commands_CreateStoreIndex($storeId, $indexId);
        $msg = $queue->send(serialize($command));
    }

    public function initAction()
    {
        $storeId = (int)$this->getParam('store_id');
        $indexId = $this->getParam('index_id');

        //$this->createCronJob($storeId, $indexId);

        $this->createStoreIndex($storeId, $indexId);
    }

    /**
     * @param int    $storeId
     * @param string $indexId
     *
     * @throws Zend_Exception
     */
    protected function createCronJob($storeId, $indexId)
    {
        try {
            $manager = new Crontab_Manager_CrontabManager();
            //           $manager->user = 'www-data';
            $newJob = $manager->newJob('*/3 * * * * php /var/www/projects/pling/httpdocs/cron.php -a /backend/search/run/store_id/'
                . $storeId . '/index_id/' . $indexId . '/ >> /var/www/projects/logs/search.log 2>&1', 'www-data');
            if (false == $manager->jobExists($newJob)) {
                $manager->add($newJob);
                $manager->save();
            }
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ' . print_r($e, true));
            exit();
        }
    }

}