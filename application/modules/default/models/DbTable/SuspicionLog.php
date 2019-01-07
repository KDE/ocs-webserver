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
 * Created: 04.01.2019
 */
class Default_Model_DbTable_SuspicionLog extends Zend_Db_Table_Abstract
{

    protected $_name = "suspicion_log";

    /**
     * @param Zend_Db_Table_Row_Abstract   $newProject
     * @param stdClass                     $authMember
     * @param Zend_Controller_Request_Http $getRequest
     *
     * @return int
     * @throws Zend_Db_Adapter_Exception
     */
    public static function logProject($newProject, $authMember, $getRequest)
    {
        $suspicious = Default_Model_Spam::hasSpamMarkers($newProject->toArray());
        $data = array(
            'project_id'   => $newProject->project_id,
            'member_id'    => $authMember->member_id,
            'http_referer' => $getRequest->getServer('HTTP_REFERER'),
            'http_origin'  => $getRequest->getServer('HTTP_ORIGIN'),
            'client_ip'    => $getRequest->getClientIp($checkProxy = true),
            'user_agent'   => $getRequest->getServer('HTTP_USER_AGENT'),
            'suspicious'   => $suspicious ? 1 : 0
        );

        return Zend_Db_Table::getDefaultAdapter()->insert('suspicion_log', $data);
    }

}