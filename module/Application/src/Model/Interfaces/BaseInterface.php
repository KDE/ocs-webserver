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

namespace Application\Model\Interfaces;

use ArrayObject;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Select;
use stdClass;

interface BaseInterface
{
    public function getName();

    public function getKey();

    public function getAdapter();

    public function findOneBy(array $params);

    /**
     * return model object if prototype class exist,else resultset current
     *
     * @param int $id
     *
     * @return mixed
     */
    public function findById($id);

    /**
     * @param $id
     *
     * @return ArrayObject|null
     */
    public function fetchById($id);

    /**
     *  fetchAll from table with sql.  could be any table with join or so on.
     *
     * @param null $sql
     * @param null $params
     * @param bool $returnArray if true return array otherwise return resultset object
     *
     * @return array|ResultSet
     */
    public function fetchAll($sql=null, $params = null, $returnArray = true);

    /**
     * @fetchRow from table with sql.  could be any table with join or so on.
     *
     * @param string $sql
     * @param null   $params
     * @param bool   $returnArray
     *
     * @return array|ArrayObject|null
     */
    public function fetchRow($sql, $params = null, $returnArray = true);

    /**
     * insert $data to Entity
     *
     * @return int
     */
    public function insert($data);

    /**
     * @insertOrUpdate $data if key existing update otherwise insert
     * @return int
     */
    public function insertOrUpdate($data);

    public function insertTable($table, $data);

    public function update($data, $where = null);

    public function updateTable($table, $data, $where);

    public function setIsDeleted($id, $setDeletedAt = true);

    public function select();

    /**
     * return ResultSet;
     */
    public function fetchAllSelect(Select $select);

    /**
     * This is only from Object table without sql mix join like Zend_Db_Table_Abstract
     *
     * @where  array()
     * @order  string
     * @limit  int
     * @offset int
     *
     * @param array  $where
     * @param string $order
     * @param int    $limit
     * @param int    $offset
     *
     * @return ResultSet from entity table
     */
    public function fetchAllRows($where = null, $order = null, $limit = null, $offset = null);

    public function fetchAllRowsCount($where = null);

    public function query($sql, $params = null);

    /**
     * array['id'] to object->id
     *
     * @param $array
     *
     * @return stdClass|null
     */
    public function arrayToObject($array);

}