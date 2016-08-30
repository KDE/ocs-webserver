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

    public function init()
    {

        $this->setMethod('POST');
        $this->setAction('/register/');
        $this->addElementPrefixPath('Local', 'Local/');
        $this->setAttrib('id', 'registerForm');
        $this->setAttrib('class', 'standard-form row-fluid center');
        $redir = $this->createElement('hidden', 'redirect')->setDecorators(array('ViewHelper'));
        $this->addElement($redir);

        $usernameValidChars = new Zend_Validate_Regex('/^[^\s\\\"\.\';\^\[\]\$\{\}]*$/');
        $userExistCheck = new Zend_Validate_Db_NoRecordExists(array(
            'table' => 'member',
            'field' => 'username',
            'exclude' => array('field' => 'is_deleted', 'value' => Default_Model_DbTable_Member::MEMBER_DELETED)
        ));
        $userExistCheck->setMessage('This username already exists.', Zend_Validate_Db_NoRecordExists::ERROR_RECORD_FOUND);
        $userEmptyCheck = new Zend_Validate_NotEmpty();
        $userEmptyCheck->setMessage('RegisterFormUsernameErr', Zend_Validate_NotEmpty::IS_EMPTY);
        $userNameLength = new Zend_Validate_StringLength(array('min' => 4, 'max' => 35));
//        $userNameLength->setMessages(array(
//            Zend_Validate_StringLength::TOO_SHORT =>
//                'Der String \'%value%\' ist zu kurz',
//            Zend_Validate_StringLength::TOO_LONG  =>
//                'Der String \'%value%\' ist zu lang'
//        ));

        $fname = $this->createElement('text', 'username')
            ->setDecorators(array('ViewHelper', 'Errors'))
            ->setRequired(true)
            ->addValidator($userEmptyCheck)
            ->addValidator($userExistCheck)
            ->addValidator($userNameLength)
            ->addValidator($usernameValidChars)
            ->setAttrib('placeholder', 'Username (4 chars minimum)')
            ->setAttrib('class', 'form-control')
        ;

        $mailValidCheck = new Zend_Validate_EmailAddress();
        $mailValidCheck->setMessage('RegisterFormEmailErrNotValid', Zend_Validate_EmailAddress::INVALID)
            ->setMessage('RegisterFormEmailErrNotValid', Zend_Validate_EmailAddress::INVALID_FORMAT)
            ->setMessage('RegisterFormEmailErrNotValid', Zend_Validate_EmailAddress::INVALID_LOCAL_PART)
            ->setMessage("RegisterFormEmailErrWrongHost", Zend_Validate_EmailAddress::INVALID_HOSTNAME)
            ->setMessage("RegisterFormEmailErrWrongHost2", Zend_Validate_Hostname::INVALID_HOSTNAME)
            ->setMessage("RegisterFormEmailErrHostLocal", Zend_Validate_Hostname::LOCAL_NAME_NOT_ALLOWED)
            ->setOptions(array('domain' => true));

        $mailExistCheck = new Zend_Validate_Db_NoRecordExists(array(
            'table' => 'member',
            'field' => 'mail',
            'exclude' => array('field' => 'is_deleted', 'value' => 1)
        ));
        $mailExistCheck->setMessage('RegisterFormEmailErrAllwaysRegistered', Zend_Validate_Db_NoRecordExists::ERROR_RECORD_FOUND);

        $mailEmpty = new Zend_Validate_NotEmpty();
        $mailEmpty->setMessage('RegisterFormEmailErrEmpty', Zend_Validate_NotEmpty::IS_EMPTY);

        $mailValidatorChain = new Zend_Validate();
        $mailValidatorChain->addValidator($mailEmpty, true)
            ->addValidator($mailValidCheck, true)
            ->addValidator($mailExistCheck);

        $mail = $this->createElement('text', 'mail')
            ->setLabel('RegisterFormEmailLabel')
            ->addValidator($mailEmpty, true)
            ->addValidator($mailValidCheck, true)
            ->addValidator($mailExistCheck)
            ->setDecorators(array('ViewHelper', 'Errors'))
            ->setRequired(true)
            ->setAttrib('placeholder', 'Email')
            ->setAttrib('class', 'form-control');

        $pass1 = $this->createElement('password', 'password1')
            ->setLabel('RegisterFormPasswordLabel')
            ->setRequired(true)
            //->addErrorMessage('RegisterFormPasswordErr')
            ->setDecorators(array('ViewHelper', 'Errors'))
            ->setAttrib('placeholder', 'Password')
            ->addValidator('stringLength', false, array(6, 200))
            ->setAttrib('class', 'form-control');

        $pass2 = $this->createElement('password', 'password2')
            ->setLabel('RegisterFormPassword2Label')
            ->setRequired(true)
            ->addErrorMessage('RegisterFormPassword2Err')
            ->setDecorators(array('ViewHelper', 'Errors'))
            ->setAttrib('placeholder', 'Confirm Password')
            ->setAttrib('class', 'form-control');

        $passValid = new Local_Validate_PasswordConfirm($pass2->getValue());
        $pass1->addValidator($passValid);

        /**
        $captcha = new Zend_Form_Element_Captcha('realHuman',array(
                'captcha' => array(
                    'captcha' => 'image',
                    'font' => APPLICATION_PATH . '/../httpdocs/theme/flatui/fonts/OpenSans-Regular.ttf',
                    'wordLen' => 6,
                    'timeout' => 300,
                ))
        );
        $captcha->setAttrib('placeholder', 'Please verify you\'re a human');
        **/
        
        $submit = $this->createElement('button', 'login');
        $submit->setLabel('Register');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('class', 'btn btn-native btn-min-width');
        $submit->setAttrib('type', 'submit');

        $this->addElement($fname)
            ->addElement($mail)
            ->addElement($pass1)
            ->addElement($pass2)
            //->addElement($captcha)
            ->addElement($submit);
    }

}

