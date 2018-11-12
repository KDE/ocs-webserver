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
class Default_Form_Rectification extends Zend_Form
{

    /**
     * @throws Zend_Exception
     * @throws Zend_Form_Exception
     * @throws Zend_Validate_Exception
     */
    public function init()
    {
        $this->addElementPrefixPath('Local', 'Local/');

        $usernameValidChars = new Zend_Validate_Regex('/^(?=.{4,20}$)(?![-])(?!.*[-]{2})[a-z0-9-]+(?<![-])$/');
        $userExistCheck = new Local_Validate_UsernameExists();
        $userExistCheck->setMessage('This username already exists.', Local_Validate_UsernameExists::EXISTS);
        $userEmptyCheck = new Zend_Validate_NotEmpty();
        $userEmptyCheck->setMessage('RegisterFormUsernameErr', Zend_Validate_NotEmpty::IS_EMPTY);
        $userNameLength = new Zend_Validate_StringLength(array('min' => 4, 'max' => 40));
        $groupNameExists = new Local_Validate_GroupnameExistsInOpenCode();

        $fname = $this->createElement('text', 'username')
                      ->setDecorators(array('ViewHelper', 'Errors'))
                      ->addFilter(new Zend_Filter_StringTrim())
                      ->addFilter(new Zend_Filter_StripNewlines())
                      ->addValidator($userEmptyCheck, true)
                      ->addValidator($userNameLength, true)
                      ->addValidator($usernameValidChars, true)
                      ->addValidator($userExistCheck, true)
                      ->addValidator($groupNameExists, true)
        ;

        $mailValidCheck = new Zend_Validate_EmailAddress();
        $mailValidCheck->setMessage('RegisterFormEmailErrNotValid', Zend_Validate_EmailAddress::INVALID)
                       ->setMessage('RegisterFormEmailErrNotValid', Zend_Validate_EmailAddress::INVALID_FORMAT)
                       ->setMessage('RegisterFormEmailErrNotValid', Zend_Validate_EmailAddress::INVALID_LOCAL_PART)
                       ->setMessage("RegisterFormEmailErrWrongHost", Zend_Validate_EmailAddress::INVALID_HOSTNAME)
                       ->setMessage("RegisterFormEmailErrWrongHost2", Zend_Validate_Hostname::INVALID_HOSTNAME)
                       ->setMessage("RegisterFormEmailErrHostLocal", Zend_Validate_Hostname::LOCAL_NAME_NOT_ALLOWED)
                       ->setOptions(array('domain' => true))
        ;

        $mailExistCheck = new Local_Validate_EmailExists();
        $mailExistCheck->setMessage('RegisterFormEmailErrAlreadyRegistered', Local_Validate_EmailExists::EXISTS);

        $mailEmpty = new Zend_Validate_NotEmpty();
        $mailEmpty->setMessage('RegisterFormEmailErrEmpty', Zend_Validate_NotEmpty::IS_EMPTY);

        $mailValidatorChain = new Zend_Validate();
        $mailValidatorChain->addValidator($mailEmpty, true)
                           ->addValidator($mailValidCheck, true)
                           ->addValidator($mailExistCheck, true);

        $mail = $this->createElement('text', 'mail')
                     ->setLabel('RegisterFormEmailLabel')
                     ->addValidator($mailEmpty, true)
                     ->addValidator($mailValidCheck, true)
                     ->addValidator($mailExistCheck, true)
                     ->setDecorators(array(
                'ViewHelper',
                'Errors'
            ))
        ;

        $submit = $this->createElement('button', 'save');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));

        $this->addElement($fname)
             ->addElement($mail)
             ->addElement($submit);
    }

}