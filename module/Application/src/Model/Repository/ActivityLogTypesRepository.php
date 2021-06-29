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

use Application\Model\Entity\ActivityLogTypes;
use Application\Model\Interfaces\ActivityLogTypesInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Sql;
use RuntimeException;

class ActivityLogTypesRepository extends BaseRepository implements ActivityLogTypesInterface
{

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "activity_log_types";
        $this->_key = "activity_log_type_id";
        $this->_prototype = ActivityLogTypes::class;
    }

    // update or insert
    public function save(ActivityLogTypes $obj)
    {
        $data = [
            'activity_log_type_id' => $obj->activity_log_type_id,
            'type_text'            => $obj->type_text,
        ];

        $id = (int)$obj->activity_log_type_id;
        if ($id === 0) {
            //$this->db->insert($data);

            $sql = new Sql($this->db);
            $insert = $sql->insert($this->_name)->values($data);
            $statement = $sql->prepareStatementForSqlObject($insert);
            $insertResult = $statement->execute();
            $id = $insertResult->getGeneratedValue();

            return $this->findById($id);
        }

        try {
            $this->findById($id);
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                sprintf(
                    'Cannot update ' . $this->_name . ' with identifier %d; does not exist', $id
                )
            );
        }
        //$this->db->update($data, [$this->_key => $id]);
        $sql = new Sql($this->db);
        $update = $sql->update($this->_name)->set($data);
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
        $result = $this->findById($obj->activity_log_type_id);

        return $obj;
    }

}
