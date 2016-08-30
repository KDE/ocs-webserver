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
class Default_Form_Forgot extends Zend_Form
{

    public function init()
    {
        $this->setMethod('POST');
        $this->setAction('/login/forgot/');
        $this->addElementPrefixPath('Local', 'Local/');
        $this->setAttrib('id', 'forgotForm');

        $mailValidCheck = new Zend_Validate_EmailAddress();
        $mailValidCheck->setMessage('ForgotPassFormMailErrNotValid', Zend_Validate_EmailAddress::INVALID);

        $mailNotExistCheck = new Zend_Validate_Db_RecordExists(array('table' => 'member', 'field' => 'mail'));
        $mailNotExistCheck->setMessage('ForgotPassFormMailErrNoUser', Zend_Validate_Db_RecordExists::ERROR_NO_RECORD_FOUND);

        $mailEmpty = new Zend_Validate_NotEmpty();

        $mail = $this->createElement('text', 'mail')
            ->setLabel('ForgotPassFormMailLable')
            ->addValidator($mailEmpty, true)
            ->addValidator($mailValidCheck, true)
            ->setRequired(TRUE)
            ->setDecorators(array('ViewHelper', 'Errors'))
            ->setAttrib('placeholder', 'Email');
        $this->addElement($mail);

        $submit = $this->createElement('button', 'send');
        $submit->setLabel('Reset my password');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('class', 'btn btn-min-width btn-native');
        $submit->setAttrib('type', 'submit');

        $this->addElement($submit);
    }

}
