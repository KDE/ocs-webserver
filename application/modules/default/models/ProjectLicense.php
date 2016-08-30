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
class Default_Model_ProjectLicense
{

    /** @var string */
    protected $_dataTableName;
    /** @var  Local_Model_Table */
    protected $_dataTable;

    /**
     * PHP 5 allows developers to declare constructor methods for classes.
     * Classes which have a constructor method call this method on each newly-created object,
     * so it is suitable for any initialization that the object may need before it is used.
     *
     * Note: Parent constructors are not called implicitly if the child class defines a constructor.
     * In order to run a parent constructor, a call to parent::__construct() within the child constructor is required.
     *
     * param [ mixed $args [, $... ]]
     * @link http://php.net/manual/en/language.oop5.decon.php
     * @param string $_dataTableName
     */
    function __construct($_dataTableName = 'Default_Model_DbTable_ProjectCcLicense')
    {
        $this->_dataTableName = $_dataTableName;
        $this->_dataTable = new $this->_dataTableName;
    }

    /**
     * @param int $project_id
     * @return bool
     */
    public function hasLicense($project_id)
    {
        $resultRow = $this->_dataTable->fetchRow(array('project_id = ?' => $project_id));
        if (empty($resultRow)) {
            return false;
        }
        return true;
    }

    /**
     * @param int $_projectId
     * @param array $values
     */
    public function saveLicenseData($_projectId, $values)
    {
        $row = $this->findOneProject($_projectId);
        $row->setFromArray($values);
        $row->project_id = $_projectId;
        $row->save();
    }

    /**
     * @param $project_id
     * @return Default_Model_DbRow_ProjectCcLicense
     */
    public function findOneProject($project_id)
    {
        $resultRow = $this->_dataTable->fetchRow(array('project_id = ?' => $project_id));
        if (empty($resultRow)) {
            $resultRow = $this->_dataTable->createRow(array(), Default_Model_DbTable_ProjectCcLicense::DEFAULT_CLASS);
        }
        return $resultRow;
    }

    /**
     * @param int $_projectId
     * @throws Zend_Db_Table_Row_Exception
     */
    public function deleteLicenseData($_projectId)
    {
        $row = $this->findOneProject($_projectId);
        if ($row->isStoredLicense()) {
            $row->delete();
        }
    }

}