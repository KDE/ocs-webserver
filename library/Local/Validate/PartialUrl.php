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

class Local_Validate_PartialUrl extends Zend_Validate_Abstract
{

    const INVALID_URL = 'invalidUrl';

    protected $_messageTemplates = array(
        self::INVALID_URL => "'%value%' is not a valid URL. Please check your input."
    );

    public function isValid($value)
    {
        $valueString = ( string )$value;
        $this->_setValue($valueString);

        Zend_Uri::setConfig(array('allow_unwise' => true));

        // Check if the url contain the words http:// or https://
        if (strpos($value, 'http://') !== false || strpos($value, 'https://') !== false) {
            $isValidURL = Zend_Uri::check($value);
        } else {
            $isValidURL = Zend_Uri::check('http://' . $value);
        }

        if (false == $isValidURL) {
            $this->_error(self::INVALID_URL);
            return false;
        }

        return true;
    }

}