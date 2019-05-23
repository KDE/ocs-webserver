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
class Statistics_Model_DbTable_StatDaily extends Zend_Db_Table_Abstract
{

    /**
     * The primary key column or columns.
     * A compound key should be declared as an array.
     * You may declare a single-column primary key
     * as a string.
     *
     * @var mixed
     */
    protected $_primary = 'daily_id';

    /**
     * The table name.
     *
     * @var string
     */
    protected $_name = 'stat_daily';

    protected $_keyColumnsForRow = array('project_id', 'project_category_id', 'project_type_id', 'year', 'month', 'day');


    /**
     * @param $data
     */
    public function save($data)
    {
        $rowSet = $this->findForColumns($data, $this->_keyColumnsForRow);

        if (null === $rowSet) {
            $rowSet = $this->createRow($data);
        } else {
            $rowSet->setFromArray($data);
        }

        $rowSet->save();
    }

    /**
     * @param $data
     * @param $columns
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function findForColumns($data, $columns)
    {

        $statement = $this->select()->setIntegrityCheck(false)->from($this->_name);
        foreach ($columns as $identifier) {
            $statement->where($this->_db->quoteIdentifier($identifier) . ' = ?', $data[$identifier]);
        }

        return $this->fetchRow($statement);
    }

}
