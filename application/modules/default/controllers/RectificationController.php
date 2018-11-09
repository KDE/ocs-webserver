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
 *
 * Created: 03.09.2018
 */
class RectificationController extends Local_Controller_Action_DomainSwitch
{

    public function indexAction()
    {
        $this->forward('change');
    }

    public function changeAction()
    {
        //todo: Prevent users from calling this action directly
        $form = new Default_Form_Rectification();

        $this->view->assign('redirect', $this->getParam('redirect'));

        if ($this->_request->isGet()) {
            $errorCode = $this->getParam("e");
            $this->view->assign('errorCode', $errorCode);

            return;
        }

        if (false === $form->isValid($_POST)) {
            $errorCode = $this->getParam("e");
            $this->view->assign('errorCode', $errorCode);

            return;
        }

        $values = $form->getValidValues($_POST);

        if (empty($values['username']) AND empty($values['mail'])) {
            $errorCode = $this->getParam("e");
            $this->view->assign('errorCode', $errorCode);

            return;
        }

        $modelMember = new Default_Model_Member();
        $member = $modelMember->fetchMember($this->_authMember->member_id);
        $oldUsername = null;
        if (isset($values['username']) AND ($member->username != $values['username'])) {
            $oldUsername = $member->username;
            $member->username = $values['username'];
            $member->username_old = $oldUsername;
            $member->save();
            $this->_authMember->username = $values['username'];
        }
        if (isset($values['mail'])) {
            $oldEmailAddress = $member->mail;
            $member->mail_old = $member->mail;
            $member->mail = $values['mail'];
            
            $member->save();
            $this->_authMember->mail = $values['mail'];
            
            $modelEmail = new Default_Model_MemberEmail();
            $dataMail = $modelEmail->saveEmailAsPrimary($this->_authMember->member_id, $values['mail']);
            $modelEmail->sendConfirmationMail((array)$this->_authMember, $dataMail->email_verification_value);
        }

        Zend_Auth::getInstance()->getStorage()->write($this->_authMember);

        try {
            $id_server = new Default_Model_Ocs_OAuth();
            $id_server->createUser($this->_authMember->member_id);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $ldap_server = new Default_Model_Ocs_Ldap();
            $ldap_server->createUser($this->_authMember->member_id);
            if (isset($oldUsername)) {
                $ldap_server->deleteByUsername($oldUsername);
            }
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        //$modelMember = new  Default_Model_Member();
        //$record = $modelMember->fetchMemberData($this->_authMember->member_id, false);
        //
        //try {
        //    $id_server = new Default_Model_Ocs_OAuth();
        //    $id_server->updateUserFromArray($record->toArray(), $oldUsername, $oldEmailAddress);
        //} catch (Exception $e) {
        //    Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        //}
        //try {
        //    $ldap_server = new Default_Model_Ocs_Ldap();
        //    $ldap_server->updateUserFromArray($record->toArray(), $oldUsername, $oldEmailAddress);
        //    Zend_Registry::get('logger')->debug(__METHOD__ . ' - ldap : ' . implode(PHP_EOL." - ", $ldap_server->getMessages()));
        //} catch (Exception $e) {
        //    Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        //}
        //try {
        //    $openCode = new Default_Model_Ocs_Gitlab();
        //    $openCode->updateUserFromArray($record->toArray(), $oldUsername, $oldEmailAddress);
        //    Zend_Registry::get('logger')->debug(__METHOD__ . ' - opencode : ' . implode(PHP_EOL." - ", $openCode->getMessages()));
        //} catch (Exception $e) {
        //    Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        //}
        //try {
        //    $modelForum = new Default_Model_Ocs_Forum();
        //    $modelForum->updateUserFromArray($record->toArray(), $oldUsername, $oldEmailAddress);
        //} catch (Exception $e) {
        //    Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        //}
        //
        //
        if ($this->_request->isXmlHttpRequest()) {
            $this->_helper->json(array('status' => 'ok', 'redirect' => '/'));
        } else {
            $this->redirect('/');
        }
    }

    public function validateAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $this->view->headScript()->appendFile('//www.google.com/recaptcha/api.js');
        $this->view->addHelperPath(APPLICATION_LIB . '/Cgsmith/View/Helper', 'Cgsmith\\View\\Helper\\');
        $formRegister = new Default_Form_Rectification();

        $name = $this->getParam('name');
        $value = $this->getParam('value');

        $result = $formRegister->getElement($name)->isValid($value, array('omitMember' => array($this->_authMember->member_id)));

        $this->_helper->json(array('status' => $result, $name => $formRegister->getElement($name)->getMessages()));
    }

}