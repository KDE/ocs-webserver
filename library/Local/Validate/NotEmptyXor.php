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

class Local_Validate_NotEmptyXor extends Zend_Validate_Abstract
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
     * Validation failure message when sibling field is present and all fields has no value
     */
    const FIELDS_EMPTY = 'FieldEmpty';


    protected $_messageTemplates = array(
        self::KEY_NOT_FOUND => "Key not found in context.",
        self::KEY_IS_EMPTY => "Key is empty.",
        self::FIELDS_EMPTY => "At least one of the fields must have a value."
    );

    /**
     * @var string
     */
    private $contextKey;

    /**
     * @param array $contextKey
     */
    function __construct($contextKey)
    {
        if (!is_array($contextKey)) {
            $contextKey = array($contextKey);
        }

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
        if ((null === $context) || !is_array($context) || !$this->testKeysExistInContext($this->contextKey, $context)) {
            $this->_error(self::KEY_NOT_FOUND);
            return false;
        }

        $oneOfSiblingFieldsFilled = false;
        foreach ($this->contextKey as $key) {
            $siblingField = $context[$key];

            if (false === empty($siblingField)) {
                $oneOfSiblingFieldsFilled = ($oneOfSiblingFieldsFilled OR true);
            }
        }

        if (false === $oneOfSiblingFieldsFilled) {
            $this->_error(self::FIELDS_EMPTY);
            return false;
        }

        return true;
    }

    protected function testKeysExistInContext($keys, $context)
    {
        foreach ($keys as $key) {
            if (false === isset($context[$key])) {
                return false;
            }
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