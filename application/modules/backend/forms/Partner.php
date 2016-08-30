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
/**
 * Description of Backend_Form_Partner
 *
 * @author Björn Schramke
 */
class Backend_Form_Partner extends Zend_Form
{

    public function init()
    {
        $name = $this->createElement('text', 'name')
            ->setLabel('Name des Partners')
            ->setRequired(true)
            ->addErrorMessage('Es muss ein Name angegeben werden.');

        $logo = $this->createElement('file', 'image_logo');
        $logo->setLabel('Logo (200x200): ')
            ->setDestination(( string )Zend_Registry::get('partnerImages'))
            ->setMultiFile(1)
            ->getDecorator('description')->setEscape(false);

        $this->addElement($name)
            ->addElement($logo)
            ->addElement('submit', 'send', array('label' => 'Speichern'))
            ->addElement('submit', 'sendclose', array('label' => 'Speichern & Schließen'));
    }

    public function setValues($valName = "", $valLogo = "")
    {
        $name = $this->getElement('name')->setValue($valName);

        if ($valLogo != "") {
            $logo = $this->getElement('image_logo');
            $logo->setDescription('Vorhandenes Bild:<br/><img src="/images_fe/partner/' . $valLogo . '" border="0" height="200px" />');
            $logo->getDecorator('description')->setEscape(false);
        }
    }

}

