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
class Default_Model_CsrfProtection
{

    /**
     * @param Zend_Form $form
     * @param string $csrf_salt_name
     * @param string $field_name
     * @return Zend_Form_Element
     */
    public static function createCSRF($form, $csrf_salt_name, $field_name = "csrf")
    {
        $element = $form->createElement('hash', $field_name, array(
            'salt' => $csrf_salt_name
        ));
        //Create unique ID if you need to use some Javascript on the CSRF Element
        $element->setAttrib('id', $form->getName() . '_' . $element->getId());
        $element->setDecorators(array('ViewHelper'));
        $form->addElement($element);
        return $element;
    }

}