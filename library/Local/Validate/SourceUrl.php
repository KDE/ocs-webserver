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
class Local_Validate_SourceUrl extends Zend_Validate_Abstract
{

    const INVALID_URL = 'invalidUrl';

    protected $_messageTemplates = array(
        self::INVALID_URL => "'%value%' is not a valid URL. It is not possible to create products based on elements from unsplash.com, because this conflicts with their terms of service. (https://unsplash.com/terms)"
    );

    public function isValid($value)
    {
        $this->_setValue((string)$value);
        $search = "unsplash.com";

        $isValidURL = true;
        if(preg_match("/{$search}/i", $value)) {
            $isValidURL = false;
        }

        if (false == $isValidURL) {
            $this->_error(self::INVALID_URL);
            return false;
        }

        return true;
    }

}