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
class Default_View_Helper_Form_Input_Html5 extends Zend_View_Helper_FormText
{

    protected $_allowedTypes = array('text', 'email', 'url', 'number', 'range', 'date',
        'month', 'week', 'time', 'datetime', 'datetime-local', 'search', 'color');

    public function html5($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable

        // build the element
        $disabled = '';
        if ($disable) {
            // disabled
            $disabled = ' disabled="disabled"';
        }

        // XHTML or HTML end tag?
        $endTag = ' />';
        if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
            $endTag = '>';
        }
        $type = 'text';
        if ($this->view->doctype()->isHtml5() && isset($attribs['type']) && in_array($attribs['type'], $this->_allowedTypes)) {
            $type = $attribs['type'];
            unset($attribs['type']);
        }
        $required = '';
        if ($this->view->doctype()->isHtml5() && isset($attribs['required'])) {
            $required = ' required ';
            unset($attribs['required']);
        }
        $xhtml = '<input type="' . $type . '" '
            . ' name="' . $this->view->escape($name) . '"'
            . ' id="' . $this->view->escape($id) . '"'
            . ' value="' . $this->view->escape($value) . '"'
            . $disabled
            . $this->_htmlAttribs($attribs)
            . $required
            . $endTag;

        return $xhtml;
    }

}