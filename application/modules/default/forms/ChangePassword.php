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
class Default_Form_ChangePassword extends Zend_Form
{

    public function init()
    {
        $this->setMethod('POST');
        $this->setAction('/change_password/');
        $this->addElementPrefixPath('Local', 'Local/');
        $this->setAttrib('id', 'changePasswordForm');
        //$this->setAttrib('class', 'standard-form row-fluid');

        $this->addElement($this->getHiddenRedirect());

        $pass1 = $this->createElement('password', 'password1')
                      ->setLabel('RegisterFormPasswordLabel')
                      ->setRequired(true)//->addErrorMessage('RegisterFormPasswordErr')
                      ->setDecorators(array('ViewHelper', 'Errors'))
                      ->setAttrib('placeholder', 'Password')
                      ->addValidator('stringLength', true, array(6, 200))
                      ->setAttrib('class', 'form-control')
        ;

        $pass2 = $this->createElement('password', 'password2')
                      ->setLabel('RegisterFormPassword2Label')
                      ->setRequired(true)
                      ->addErrorMessage('RegisterFormPassword2Err')
                      ->setDecorators(array('ViewHelper', 'Errors'))
                      ->setAttrib('placeholder', 'Confirm Password')
                      ->setAttrib('class', 'form-control')
        ;

        $passValid = new Local_Validate_PasswordConfirm($pass2->getValue());
        $pass1->addValidator($passValid, true);

        $submit = $this->createElement('button', 'change');
        $submit->setLabel('Change');
        $submit->setDecorators(array('ViewHelper'));
        //$submit->setAttrib('class', 'btn btn-min-width btn-native');
        //$submit->setAttrib('type', 'submit');

//        $hash = $this->createElement('hash', 'csrfLogin', array('salt' => 'PlattenSpalter'));
//        $hash->setDecorators(array('ViewHelper', 'Errors'));
//        $hash->getValidator('Identical')->setMessage('Your session is outdated. Please reload the page an try again.');
//        $this->addElement($hash);


        $this->addElement($pass1);
        $this->addElement($pass2);
        $this->addElement($submit);
    }

    private function getHiddenRedirect()
    {
        return $this->createElement('hidden', 'redirect')
            ->setFilters(array('StringTrim'))
            ->setDecorators(
                array(
                    array(
                        'ViewScript',
                        array(
                            'viewScript' => 'authorization/viewscripts/input_hidden.phtml',
                            'placement' => false
                        )
                    )
                ));
    }

}