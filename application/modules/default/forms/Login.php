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
class Default_Form_Login extends Zend_Form
{

    public function init()
    {
        $this->setMethod('POST');
        $this->setAction('/login/');
        $this->addElementPrefixPath('Local', 'Local/');
        $this->setAttrib('id', 'loginForm');
        $this->setAttrib('class', 'standard-form row-fluid');

        $this->addElement($this->getHiddenRedirect());

        $dologin = $this->createElement('hidden', 'dologin');
        $dologin->setValue('true');
        $dologin->setDecorators(array('ViewHelper'));
        $this->addElement($dologin);


        $loginName = $this->createElement('text', 'mail');
        $loginName->setLabel('index.login.username');
        $loginName->setFilters(array('StringTrim'));
//        $loginName->setValidators(array('EmailAddress'));
        $loginName->setRequired(true);
        $loginName->setDecorators(array('ViewHelper'));
        $loginName->setAttrib('placeholder', 'Email or Username');
        $loginName->setAttrib('class', 'inputbox email');

        $loginPass = $this->createElement('password', 'password');
        $loginPass->setLabel('index.login.password');
        $loginPass->setFilters(array('StringTrim'));
        $loginPass->setRequired(true);
        $loginPass->setDecorators(array('ViewHelper'));
        $loginPass->setAttrib('placeholder', 'Password');
        $loginPass->setAttrib('class', 'inputbox password');

        $rememberMe = $this->createElement('checkbox', 'remember_me')
            ->setLabel('index.login.remember_me')
            ->setDecorators(
                array(
                    'ViewHelper',
                    array('Label',
                        array(
                            'placement' => 'append',
                            'class' => 'optional'
                        )
                    ),
                    array('HtmlTag', array('tag' => 'div', 'class' => 'container-checkbox-remember-me text-left'))
                )
            );

        $submit = $this->createElement('button', 'login');
        $submit->setLabel('Login');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('class', 'btn btn-min-width btn-native');
        $submit->setAttrib('type', 'submit');

//        $hash = $this->createElement('hash', 'csrfLogin', array('salt' => 'PlattenSpalter'));
//        $hash->setDecorators(array('ViewHelper', 'Errors'));
//        $hash->getValidator('Identical')->setMessage('Your session is outdated. Please reload the page an try again.');
//        $this->addElement($hash);


        $this->addElement($loginName);
        $this->addElement($loginPass);
        $this->addElement($rememberMe);
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