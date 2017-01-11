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
class Default_Form_Element_Html5 extends Zend_Form_Element_Text
{

    const DEFAULT_TYPE = 'text';
    const FIELD_EMAIL = 'email';
    const FIELD_EMAIL_ADDRESS = 'emailaddress';
    const FIELD_URL = 'url';
    const FIELD_NUMBER = 'number';
    const FIELD_RANGE = 'range';
    const FIELD_DATE = 'date';
    const FIELD_MONTH = 'month';
    const FIELD_WEEK = 'week';
    const FIELD_TIME = 'time';
    const FIELD_DATE_TIME = 'datetime';
    const FIELD_DATE_TIME_LOCAL = 'datetime-local';
    const FIELD_SEARCH = 'search';
    const FIELD_COLOR = 'color';

    protected static $_mapping = array(
        self::FIELD_EMAIL => 'email',
        self::FIELD_EMAIL_ADDRESS => 'email',
        self::FIELD_URL => 'url',
        self::FIELD_NUMBER => 'number',
        self::FIELD_RANGE => 'range',
        self::FIELD_DATE => 'date',
        self::FIELD_MONTH => 'month',
        self::FIELD_WEEK => 'week',
        self::FIELD_TIME => 'time',
        self::FIELD_DATE_TIME => 'datetime',
        self::FIELD_DATE_TIME_LOCAL => 'datetime-local',
        self::FIELD_SEARCH => 'search',
        self::FIELD_COLOR => 'color',
    );

    public $helper = 'html5';

    public function __construct($spec, $options = null)
    {
        if ($this->_isHtml5() && !isset($options['type'])) {
            $options['type'] = $this->_getType($spec);
        }
        parent::__construct($spec, $options);
    }

    private function _isHtml5()
    {
        return $this->getView()->getHelper('doctype')->isHtml5();
    }

    private function _getType($spec)
    {
        if (array_key_exists(strtolower($spec), self::$_mapping)) {
            return self::$_mapping[$spec];
        }
        return self::DEFAULT_TYPE;
    }

    public function init()
    {
        $view = $this->getView();
        $view->addHelperPath(APPLICATION_PATH . '/modules/default/views/helpers/forms/input', 'Default_View_Helper_Form_Input');
        parent::init();
    }

    public function setRequired($flag = true)
    {
        if ($flag === true && $this->_isHtml5()) {
            $this->setAttrib('required', 'required');
        }
        return parent::setRequired($flag);
    }

}