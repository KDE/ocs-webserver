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
class Default_Form_Element_ImageHandler extends Zend_Form_Element
{

    /** @var  string */
    protected $btnLabel;


    public function init()
    {
        parent::init();
        $this->addDecorator('ViewScript', array(
            'viewScript' => 'form_element_image_handler.phtml'
        ));
    }

    /**
     * @return string $btnLabel
     */
    public function getBtnLabel()
    {
        return $this->btnLabel;
    }

    /**
     * @param string $btnLabel
     */
    public function setBtnLabel($btnLabel)
    {
        $this->btnLabel = $btnLabel;
    }

}