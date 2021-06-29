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

use Application\Model\Entity\LoginHistory;
use Application\Model\Interfaces\LoginHistoryInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;

class LoginHistoryRepository extends BaseRepository implements LoginHistoryInterface
{
    private $cache;

    public function __construct(
        AdapterInterface $db,
        StorageInterface $storage = null
    ) {
        parent::__construct($db);
        $this->_name = "login_history";
        $this->_key = "id";
        $this->_prototype = LoginHistory::class;
        $this->cache = $storage;
    }

    /**
     * @param int $memberId
     *
     * @return array
     */
    public function fetchLastLoginData($memberId)
    {
        $resultSet = array();
        $sql = '
            SELECT id, member_id,login_at,ip,ipv4,ipv6,browser,os,architecture,fingerprint,user_agent
            FROM login_history AS node
            WHERE node.member_id = ' . $this->fpn('id') . '
            ORDER BY node.id DESC
            LIMIT 1
            ';

        $statement = $this->db->query($sql);
        $data = $statement->execute(['id' => $memberId]);
        if ($data->isQueryResult()) {
            $resultSet = $data->current();
        }

        return $resultSet;
    }

    /**
     * @param int $member_id
     *
     * @return array
     */
    public function fetchFirstLoginData($member_id)
    {
        $resultSet = array();
        $sql = "
            SELECT id, member_id,login_at,ip,ipv4,ipv6,browser,os,architecture,fingerprint,user_agent
            FROM login_history AS node
            WHERE node.member_id = " . $this->fpn('id') . "
            ORDER BY node.id 
            LIMIT 1
            ";

        $statement = $this->db->query($sql);
        $data = $statement->execute(['id' => $member_id]);
        if ($data->isQueryResult()) {
            $resultSet = $data->current();
        }

        return $resultSet;
    }

}
