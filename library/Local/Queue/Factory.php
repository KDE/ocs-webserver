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
class Local_Queue_Factory
{

    /**
     * @param $queueName
     *
     * @return Zend_Queue
     * @throws Zend_Exception
     * @deprecated
     */
    public static function createQueue($queueName)
    {
        $config = Zend_Registry::get('config');
        $queueConfig = $config->queue->$queueName;

        return self::_initQueue($queueConfig);
    }

    /**
     * @param $config
     *
     * @return Zend_Queue
     * @throws Zend_Exception
     * @throws Zend_Queue_Exception
     * @deprecated
     */
    protected static function _initQueue($config)
    {
        $queueName = $config->name;
        $dbAdapter = $config->dbAdapter ? $config->dbAdapter : 'Db';
        $configAll = Zend_Registry::get('config');
        $configDb = $configAll->resources->db->params;
        $queueAdapter = new $dbAdapter(array('driverOptions' => $configDb->toArray()));

        return new Zend_Queue($queueAdapter, array('name' => $queueName, 'driverOptions' => $configDb->toArray()));
    }

    /**
     * @param string $identifier
     *
     * @return Zend_Queue
     * @throws Zend_Exception
     */
    public static function getQueue($identifier = null)
    {
        /** @var Zend_Config $configAll */
        $configAll = Zend_Registry::get('config');
        $configDb = $configAll->resources->db->params->toArray();
        $nameQueue = isset($identifier) ? $identifier : Zend_Registry::get('config')->settings->queue->general->name;
        $queueAdapter = new Local_Queue_Adapter_Db(array('driverOptions' => $configDb));

        return new Zend_Queue($queueAdapter, array(Zend_Queue::NAME => $nameQueue, 'driverOptions' => $configDb));
    }

}