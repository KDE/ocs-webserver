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
 *
 *    Created: 22.09.2016
 **/
class Default_Model_MemberDeactivationLog extends Default_Model_DbTable_MemberDeactivationLog
{
    const CASE_INSENSITIVE = 1;
    /** @var string */
    protected $_dataTableName;
    /** @var  Default_Model_DbTable_MemberEmail */
    protected $_dataTable;

    /**
     * @inheritDoc
     */
    public function __construct($_dataTableName = 'Default_Model_DbTable_MemberDeactivationLog')
    {
        $this->_dataTableName = $_dataTableName;
        $this->_dataTable = new $this->_dataTableName;
    }

    

}