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
class Default_Form_Register extends Zend_Form
{

    /**
     * @throws Zend_Exception
     * @throws Zend_Form_Exception
     * @throws Zend_Validate_Exception
     */
    public function init()
    {
        $this->setMethod('POST');
        $this->setAction('/register/');
        $this->addElementPrefixPath('Local', 'Local/');
        $this->setAttrib('id', 'registerForm');
        $this->setAttrib('class', 'standard-form row-fluid center');
        $redir = $this->createElement('hidden', 'redirect')->setDecorators(array('ViewHelper'));
        $this->addElement($redir);

        $usernameValidChars = new Zend_Validate_Regex('/^(?=.{4,20}$)(?![-])(?!.*[-]{2})[a-z0-9-]+(?<![-])$/');
        $userExistCheck = new Local_Validate_UsernameExists();
        $userExistCheck->setMessage('This username already exists.', Local_Validate_UsernameExists::EXISTS);
        $userExistCheck->setMessage('This username already exists.');
        $userEmptyCheck = new Zend_Validate_NotEmpty();
        $userEmptyCheck->setMessage('RegisterFormUsernameErr', Zend_Validate_NotEmpty::IS_EMPTY);
        $userNameLength = new Zend_Validate_StringLength(array('min' => 4, 'max' => 20));

        $fname = $this->createElement('text', 'username')
                      ->setDecorators(array('ViewHelper', 'Errors'))
                      ->setRequired(true)
                      ->addFilter(new Zend_Filter_StringTrim())
                      ->addFilter(new Zend_Filter_StripNewlines())
                      ->addValidator($userEmptyCheck, true)
                      ->addValidator($userNameLength, true)
                      ->addValidator($usernameValidChars, true)
                      ->addValidator($userExistCheck, true)
                      ->setAttrib('placeholder', 'Username (4 chars minimum)')
                      ->setAttrib('class', 'form-control');

        /*
        $opencode = Zend_Registry::get('config')->settings->server->opencode;
        if ($opencode->host) {
            $groupNameExists = new Local_Validate_GroupnameExistsInOpenCode();
            $fname->addValidator($groupNameExists, true);
        }*/

        $mailValidCheck = new Zend_Validate_EmailAddress();
        $mailValidCheck->setMessage('RegisterFormEmailErrNotValid', Zend_Validate_EmailAddress::INVALID)
                       ->setMessage('RegisterFormEmailErrNotValid', Zend_Validate_EmailAddress::INVALID_FORMAT)
                       ->setMessage('RegisterFormEmailErrNotValid', Zend_Validate_EmailAddress::INVALID_LOCAL_PART)
                       ->setMessage("RegisterFormEmailErrWrongHost", Zend_Validate_EmailAddress::INVALID_HOSTNAME)
                       ->setMessage("RegisterFormEmailErrWrongHost2", Zend_Validate_Hostname::INVALID_HOSTNAME)
                       ->setMessage("RegisterFormEmailErrHostLocal", Zend_Validate_Hostname::LOCAL_NAME_NOT_ALLOWED)
                       ->setOptions(array('domain' => true));

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
                     ->setDecorators(array('ViewHelper', 'Errors'))
                     ->setRequired(true)
                     ->setAttrib('placeholder', 'Email')
                     ->setAttrib('class', 'form-control');

        $pass1 = $this->createElement('password', 'password1')
                      ->setLabel('RegisterFormPasswordLabel')
                      ->setRequired(true)//->addErrorMessage('RegisterFormPasswordErr')
                      ->setDecorators(array('ViewHelper', 'Errors'))
                      ->setAttrib('placeholder', 'Password')
                      ->addValidator('stringLength', true, array(6, 200))
                      ->setAttrib('placeholder', 'Password (6 chars minimum)')
                      ->setAttrib('class', 'form-control');

        $pass2 = $this->createElement('password', 'password2')
                      ->setLabel('RegisterFormPassword2Label')
                      ->setRequired(true)
                      ->addErrorMessage('RegisterFormPassword2Err')
                      ->setDecorators(array('ViewHelper', 'Errors'))
                      ->setAttrib('placeholder', 'Confirm Password')
                      ->setAttrib('class', 'form-control');

        $passValid = new Local_Validate_PasswordConfirm($pass2->getValue());
        $pass1->addValidator($passValid, true);

        $submit = $this->createElement('button', 'login');
        $submit->setLabel('Register');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('class', 'btn btn-native btn-min-width');
        $submit->setAttrib('type', 'submit');

        $this->addElement($fname)
             ->addElement($mail)
             ->addElement($pass1)
             ->addElement($pass2)
             ->addElement($submit);

        if (APPLICATION_ENV == 'development') {
            return;
        }

        $this->addPrefixPath('Cgsmith\\Form\\Element', APPLICATION_LIB . '/Cgsmith/Form/Element', Zend_Form::ELEMENT);
        $this->addElementPrefixPath('Cgsmith\\Validate\\', APPLICATION_LIB . '/Cgsmith/Validate/',
            Zend_Form_Element::VALIDATE);
        $captcha = $this->createElement('recaptcha', 'g-recaptcha-response', array(
            'siteKey'   => Zend_Registry::get('config')->recaptcha->sitekey,
            'secretKey' => Zend_Registry::get('config')->recaptcha->secretkey,
        ));

        $this->addElement($captcha);
    }

}