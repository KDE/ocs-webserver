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

use Application\Model\Entity\MemberToken;
use Application\Model\Interfaces\MemberTokenInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Sql;


class MemberTokenRepository extends BaseRepository implements MemberTokenInterface
{
    protected $_defaultValues = array(
        'token_member_id'         => null,
        'token_provider_name'     => null,
        'token_value'             => 0,
        'token_provider_username' => null,
        'token_fingerprint'       => 0,
        'token_created'           => null,
        'token_changed'           => null,
        'token_deleted'           => null,
    );

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "member_token";
        $this->_key = "token_id";
        $this->_prototype = MemberToken::class;
    }

    /**
     * @param int $identifer
     *
     * @return int
     */
    public function setDeleted($identifer)
    {
        return $this->delete($identifer);
    }

    /**
     * @param int $id
     *
     * @return int
     */
    public function delete($id)
    {
        $values = array();
        $values['token_deleted'] = new Expression("NOW()");

        $where = array();
        $where[$this->_key] = $id;

        //$savedRow = $this->db->update($values, 'collection_id = '.$collection_id . ' AND project_id = ' . $project_id);
        $sql = new Sql($this->db);
        $update = $sql->update($this->_name)->set($values)->where($where);
        $statement = $sql->prepareStatementForSqlObject($update);

        $result = $statement->execute();

        return $result->count();
    }

}
