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
class Default_Form_ProjectConfirm extends Zend_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        $this->setAction('');
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->addElementPrefixPath('Local', 'Local/');

    }

    public function init()
    {

        $this->setAttrib('class', 'standard-form');

        $mailValidCheck = new Zend_Validate_EmailAddress();
        $mailValidCheck->setMessage('RegisterFormEmailErrNotValid', Zend_Validate_EmailAddress::INVALID)
            ->setMessage('RegisterFormEmailErrNotValid', Zend_Validate_EmailAddress::INVALID_FORMAT)
            ->setMessage('RegisterFormEmailErrNotValid', Zend_Validate_EmailAddress::INVALID_LOCAL_PART)
            ->setMessage("RegisterFormEmailErrWrongHost", Zend_Validate_EmailAddress::INVALID_HOSTNAME)
            ->setMessage("RegisterFormEmailErrWrongHost2", Zend_Validate_Hostname::INVALID_HOSTNAME)
            ->setMessage("RegisterFormEmailErrHostLocal", Zend_Validate_Hostname::LOCAL_NAME_NOT_ALLOWED)
            ->setOptions(array('domain' => TRUE));

        $mailEmpty = new Zend_Validate_NotEmpty();
        $mailEmpty->setMessage('RegisterFormEmailErrEmpty', Zend_Validate_NotEmpty::IS_EMPTY);

        $mailValidatorChain = new Zend_Validate();
        $mailValidatorChain->addValidator($mailEmpty, true)
            ->addValidator($mailValidCheck, true);

        $paypal_mail = $this->createElement('text', 'paypal_mail')
            ->setLabel('PayPal-Account: *')
            ->addValidator($mailEmpty, true)
            ->addValidator($mailValidCheck, true)
            ->setRequired(false)
            ->setDecorators(
                array(
                    'ViewHelper',
                    'Errors'
                ))
            ->setAttrib('class', 'left preview-input')
            ->setAttrib('placeholder', $this->getView()->translate('Email Address'));
        $paypal_mail->addValidator(new Local_Validate_Paypal('firstname', 'lastname'));
        $this->addElement($paypal_mail);

        $firstName = $this->createElement('text', 'firstname')
            ->setLabel("Firstname: *")
            ->setRequired(false)
            ->addErrorMessage("Please enter your Firstname.")
            ->setDecorators(
                array(
                    'ViewHelper',
                    'Errors'
                ))
            ->setAttrib('class', 'left preview-input')
            ->setAttrib('placeholder', $this->getView()->translate('First Name'));
        $this->addElement($firstName);


        $lastName = $this->createElement('text', 'lastname')
            ->setLabel("Lastname: *")
            ->setRequired(false)
            ->addErrorMessage("Please enter your Lastname.")
            ->setDecorators(
                array(
                    'ViewHelper',
                    'Errors'
                ))
            ->setAttrib('class', 'left preview-input')
            ->setAttrib('placeholder', $this->getView()->translate('Last Name'));
        $this->addElement($lastName);

        $save = $this->createElement('button', 'save');
        $save->setLabel('Save');
        $save->setDecorators(array('ViewHelper'));
        $save->setAttrib('class', 'btn btn-submit right preview-button');
        $save->setAttrib('type', 'submit');

        $back = $this->createElement('button', 'back');
        $back->setLabel('Back');
        $back->setDecorators(array('ViewHelper'));
        $back->setAttrib('class', 'btn btn-grey left preview-button');
        $back->setAttrib('type', 'submit');
//        $back->setAttrib('onclick', 'javascript:history.back();return false;');

        $publish = $this->createElement('button', 'publish');
        $publish->setLabel('Save & Publish');
        $publish->setDecorators(array('ViewHelper'));
        $publish->setAttrib('class', 'btn btn-purple right preview-button');
        $publish->setAttrib('type', 'submit');

        $this->addElement($back)
            ->addElement($save)
            ->addElement($publish);

    }

}

