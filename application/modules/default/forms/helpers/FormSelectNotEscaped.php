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
// require_once APPLICATION_LIB . '/Zend/View/Helper/FormSelect.php';

class Default_Form_Helper_FormSelectNotEscaped extends Zend_View_Helper_FormSelect
{

    public function formSelectNotEscaped($name, $value = null, $attribs = null,
                                         $options = null, $listsep = "<br />\n")
    {
        return parent::formSelect($name, $value, $attribs, $options, $listsep);
    }

    /**
     * Builds the actual <option> tag
     *
     * @param string $value Options Value
     * @param string $label Options Label
     * @param array $selected The option value(s) to mark as 'selected'
     * @param array|bool $disable Whether the select is disabled, or individual options are
     * @param array $optionClasses The classes to associate with each option value
     * @return string Option Tag XHTML
     */
    protected function _build($value, $label, $selected, $disable, $optionClasses = array())
    {
        if (is_bool($disable)) {
            $disable = array();
        }

        $class = null;
        if (array_key_exists($value, $optionClasses)) {
            $class = $optionClasses[$value];
        }


        $opt = '<option'
            . ' value="' . $this->view->escape($value) . '"';

        if ($class) {
            $opt .= ' class="' . $class . '"';
        }
        // selected?
        if (in_array((string)$value, $selected)) {
            $opt .= ' selected="selected"';
        }

        // disabled?
        if (in_array($value, $disable)) {
            $opt .= ' disabled="disabled"';
        }

        $opt .= '>' . ($label) . "</option>";

        return $opt;
    }
} 
