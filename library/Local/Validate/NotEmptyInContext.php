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

class Local_Validate_NotEmptyInContext extends Zend_Validate_Abstract
{

    /**
     * Validation failure message key for when the value of the parent field is an empty string
     */
    const KEY_NOT_FOUND = 'KeyNotFound';

    /**
     * Validation failure message key for when the value is an empty string
     */
    const KEY_IS_EMPTY = 'KeyIdEmpty';

    /**
     * Validation failure message when parent field is present and the current field has no value
     */
    const PARENT_NOT_EMPTY = 'FieldEmpty';


    protected $_messageTemplates = array(
        self::KEY_NOT_FOUND => "Key not found in context.",
        self::KEY_IS_EMPTY => "Key is empty.",
        self::PARENT_NOT_EMPTY => "Field must have a value when parent field is filled out."
    );

    /**
     * @var string
     */
    private $contextKey;

    function __construct($contextKey)
    {
        $this->contextKey = $contextKey;
    }


    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param mixed $value
     * @param mixed $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        if ((null === $context) || !is_array($context) || !array_key_exists($this->contextKey, $context)) {
            $this->_error(self::KEY_NOT_FOUND);
            return false;
        }

        $parentField = $context[$this->contextKey];

        if (false === empty($parentField) AND empty($value)) {
            $this->_error(self::PARENT_NOT_EMPTY);
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getContextKey()
    {
        return $this->contextKey;
    }

    /**
     * @param string $contextKey
     */
    public function setContextKey($contextKey)
    {
        $this->contextKey = $contextKey;
    }

}