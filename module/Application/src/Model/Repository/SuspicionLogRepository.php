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

namespace Application\Model\Repository;

use Application\Model\Entity\SuspicionLog;
use Application\Model\Interfaces\SuspicionLogInterface;
use Laminas\Db\Adapter\AdapterInterface;

class SuspicionLogRepository extends BaseRepository implements SuspicionLogInterface
{

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "suspicion_log";
        $this->_key = "suspicion_id";
        $this->_prototype = SuspicionLog::class;
    }

    //TODO logProject => to Service
    public static function logProject($newProject, $authMember, $getRequest)
    {
        /*
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
        */
    }

}