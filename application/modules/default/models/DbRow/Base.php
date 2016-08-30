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
class Default_Model_DbRow_Base extends Zend_Db_Table_Row_Abstract
{

    protected $_data = array();

    function __construct($data = null)
    {
        foreach ($data as $key => $value) {
            $this->__set($key, $value);
        }
    }

    public function __get($name)
    {
        $retValue = null;
        $lcName = strtolower($name);
        $method_name = 'get' . ucfirst($name);

        if (method_exists($this, $method_name)) {
            $retValue = $this->$method_name();
        } elseif (array_key_exists($name, $this->_data)) {
            $retValue = $this->_data[$name];
        } elseif (array_key_exists($lcName, $this->_data)) {
            $retValue = $this->_data[$lcName];
        } else {
            throw new Zend_Exception('Undefined property: ' . $name);
        }

        return $retValue;
    }

    public function __set($name, $value)
    {
        $lcName = strtolower($name);
        $method_name = 'set' . ucfirst($name);

        if (method_exists($this, $method_name)) {
            $this->$method_name($value);
        } elseif (array_key_exists($name, $this->_data)) {
            $this->_data[$name] = $value;
        } elseif (array_key_exists($lcName, $this->_data)) {
            $this->_data[$lcName] = $value;
        } else {
            throw new Zend_Exception('You cannot set new properties on this object');
        }

        return $this;
    }

    public function toArray()
    {
        return $this->_data;
    }


}
