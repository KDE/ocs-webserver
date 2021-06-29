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

use Application\Model\Entity\BrowseListTypes;
use Application\Model\Interfaces\BrowseListTypesInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;

class BrowseListTypesRepository extends BaseRepository implements BrowseListTypesInterface
{
    const CACHE_STORES_CATEGORIES = 'browse_list_types';
    const CACHE_STORES_CONFIGS = 'browse_list_type_list';
    const CACHE_STORES_CONFIGS_BY_ID = 'browse_list_type_id_list';

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "browse_list_types";
        $this->_key = "browse_list_type_id";
        $this->_prototype = BrowseListTypes::class;
    }

    /**
     * @param null $id
     *
     * @return array
     */
    public function fetchNamesForJTable($id = null)
    {
        $select = new Select();
        $select->from($this->_name)->columns(array('name'))->group('name');


        $resultRows = $this->fetchAllSelect($select);

        $resultForSelect = array();
        foreach ($resultRows as $row) {
            $resultForSelect[] = array('DisplayText' => $row->name, 'Value' => $row->browse_list_type_id);
        }

        return $resultForSelect;
    }

    /*
    public function deleteId($dataId)
    {
        $sql = "DELETE FROM $this->_name WHERE {$this->_key} = ?";
        $this->tableGateway->getAdapter()->query($sql, $dataId)->execute();
//        return $this->delete(array('store_id = ?' => (int)$dataId));
    }
    */

    public function delete($where)
    {
        $this->update(array('is_active' => '0', 'deleted_at' => new Expression("NOW()")), $where);
    }

    public function activate($where)
    {
        $this->update(array('is_active' => '1', 'deleted_at' => null), $where);

    }

    /**
     * @param int $nodeId
     *
     * @return \ArrayObject|object|null
     */
    public function findBrowseListType($nodeId)
    {
        $result = $this->findById($nodeId);
        if ($result) {
            return $result;
        }

        return null;
    }

}
