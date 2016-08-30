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
class Default_Model_DbRow_ProjectCcLicense extends Zend_Db_Table_Row_Abstract
{

    protected $_data = array(
        'license_id' => null,
        'project_id' => null,
        'by' => 1,
        'nc' => 0,
        'nd' => 0,
        'sa' => 0
    );

    /** @var bool */
    protected $_storedLicense = false;

    /**
     * @return boolean
     */
    public function isStoredLicense()
    {
        return $this->_storedLicense;
    }

    /**
     * Constructor.
     *
     * Supported params for $config are:-
     * - table       = class name or object of type Zend_Db_Table_Abstract
     * - data        = values of columns in this row.
     *
     * @param  array $config OPTIONAL Array of user-specified config options.
     * @return void
     * @throws Zend_Db_Table_Row_Exception
     */
    public function __construct(array $config)
    {
        if (isset($config['stored']) && $config['stored'] === true) {
            $this->_storedLicense = true;
        }

        parent::__construct($config);
    }

}