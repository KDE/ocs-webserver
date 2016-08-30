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
class Default_Form_ProjectShare extends Zend_Form
{

    public function init()
    {
        $this->setAttrib('class', 'partialjson');
        $this->setAttrib('data-target', '#modal-dialog');

        $shareEmail = $this->createElement('text', 'mail');
        $shareEmail->setLabel('Receiver Email');
        $shareEmail->setFilters(array('StringTrim'));
        $shareEmail->setValidators(array('EmailAddress'));
        $shareEmail->setRequired(true);
        $shareEmail->setDecorators(array('ViewHelper', 'Errors'));
        $shareEmail->addDecorator('Errors', array('class' => 'text-error'));
        $shareEmail->setAttrib('placeholder', 'Email');

        $senderEmail = $this->createElement('text', 'sender_mail');
        $senderEmail->setLabel('Sender Email');
        $senderEmail->setFilters(array('StringTrim'));
        $senderEmail->setValidators(array('EmailAddress'));
        $senderEmail->setRequired(true);
        $senderEmail->setDecorators(array('ViewHelper', 'Errors'));
        $senderEmail->addDecorator('Errors', array('class' => 'text-error'));
        $senderEmail->setAttrib('placeholder', 'Email');

        $submit = $this->createElement('button', 'send');
        $submit->setLabel('Send');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('class', 'btn btn-submit right');
        $submit->setAttrib('type', 'submit');

        $this->addElement($shareEmail);
        $this->addElement($senderEmail);
        $this->addElement($submit);

    }

}