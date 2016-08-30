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

class Local_Model_Table extends Zend_Db_Table
{

    protected $_key = null;
    protected $_keyColumnsForRow = array();


    /**
     * @param $data
     * @throws Exception
     * @return Zend_Db_Table_Row_Abstract
     */
    public function save($data)
    {
        if (empty($this->_keyColumnsForRow)) {
            throw new Exception('no _keyColumnsForRow were set');
        }
        $rowSet = $this->findForKeyColumns($data, $this->_keyColumnsForRow);

        if (null === $rowSet) {
            $rowSet = $this->createRow($data);
        } else {
            $rowSet->setFromArray($data);
        }

        $rowSet->save();

        return $rowSet;
    }

    /**
     * @param array $data
     * @param string|array $keyColumns
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function findForKeyColumns($data, $keyColumns)
    {
        if (false === is_array($keyColumns)) {
            $keyColumns = array($keyColumns);
        }
        if (0 < count(array_diff($keyColumns, array_keys($data)))) {
            // if data doesn't contain ay key column we can stop here
            return null;
        }
        $statement = $this->select()->setIntegrityCheck(false)->from($this->_name);
        foreach ($keyColumns as $identifier) {
            if (null === $data[$identifier]) {
                $statement->where($this->_db->quoteIdentifier($identifier) . ' IS NULL');
            } else {
                $statement->where($this->_db->quoteIdentifier($identifier) . ' = ?', $data[$identifier]);
            }
        }

        return $this->fetchRow($statement);
    }

    /**
     * @param $data
     * @throws Exception
     * @return mixed
     */
    public function saveByKey($data)
    {
        if (empty($this->_key)) {
            throw new Exception('no _key was set');
        }
        $rowSet = $this->find($data[$this->_key])->current();

        if (null === $rowSet) {
            $rowSet = $this->createRow($data);
        } else {
            $rowSet->setFromArray($data);
        }

        return $rowSet->save();
    }

    public function findSingleRow()
    {
        $rowset = $this->find($args = func_get_args());
        if (0 < $rowset->count()) {
            return $rowset->current();
        } else {
            return $this->createRow(array(), self::DEFAULT_CLASS);
        }
    }

} 