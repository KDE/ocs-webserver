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

use Application\Model\Entity\MemberExternalId;
use Application\Model\Interfaces\MemberExternalIdInterface;
use Exception;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Sql;


class MemberExternalIdRepository extends BaseRepository implements MemberExternalIdInterface
{
    protected $_defaultValues = array(
        'external_id' => 0,
        'member_id'   => 0,
        'created_at'  => null,
        'is_deleted'  => null,
    );

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "member_external_id";
        $this->_key = "external_id";
        $this->_prototype = MemberExternalId::class;
    }

    /**
     * @param int $identifier
     *
     * @return int
     */
    public function setDelete($identifier)
    {
        $data = array();
        $data[$this->_key] = $identifier;
        $data['is_deleted'] = 1;

        return $this->update($data)->getAffectedRows();
    }

    /**
     * @param array|string $member_id
     *
     * @return int|void
     * @throws Exception
     */
    public function delete($member_id)
    {
        throw new Exception('Deleting of users is not allowed.');
    }

    public function createExternalId($member_id)
    {
        $data = array();
        $data['external_id'] = substr(sha1($member_id), 0, 20);
        $data['member_id'] = $member_id;

        $sql = new Sql($this->db);
        $insert = $sql->insert($this->_name)->values($data);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $insertResult = $statement->execute();

        $extId2 = $this->fetchRow('SELECT ' . $this->getKey() . ' as id FROM ' . $this->getName() . ' ORDER BY created_at desc LIMIT 1;');

        return $extId2['id'];
    }

    public function updateGitlabUserId($member_id, $gitlab_user_id)
    {
        $data = array();
        $data['gitlab_user_id'] = $gitlab_user_id;

        $sql = new Sql($this->db);
        $update = $sql->update($this->_name)->set($data)->where(array('member_id' => $member_id));
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
    }

}
