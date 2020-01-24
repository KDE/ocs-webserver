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
 * Created: 02.05.2018
 */
class VerifyController extends Local_Controller_Action_DomainSwitch
{

    public function resendAction()
    {
        $session = new Zend_Session_Namespace();
        $member_id = (int)$session->mail_verify_member_id;
        if (empty($member_id)) {
            throw new Zend_Controller_Action_Exception('missing member id parameter');
        }

        if ($this->_request->isGet()) {
            return;
        }

        $crsf_token = $this->getParam('crsf_token');
        if (false == Default_Model_CsrfProtection::validateCsrfToken($crsf_token)) {
            return;
        }

        $modelMember = new Default_Model_Member();
        $memberData = $modelMember->fetchMemberData($member_id);

        if (count($memberData->toArray()) == 0) {
            throw new Zend_Controller_Action_Exception('could not found data for member_id: ' . print_r($member_id, true));
        }

        $modelEmail = new Default_Model_MemberEmail();
        $primaryEmail = $modelEmail->fetchMemberPrimaryMail($member_id);
        $mailValidationValue = $primaryEmail['email_verification_value'];
        $memberEmail = $primaryEmail['email_address'];

        Zend_Registry::get('logger')->info(__METHOD__ . ' - resend of verification mail requested by user id: '
            . $memberData->member_id)
        ;

        $this->resendVerificationMail($memberData, $memberEmail, $mailValidationValue);

        Zend_Registry::get('logger')->info(__METHOD__ . ' - verification mail successfully transmitted to mail server for user id: '
            . $memberData->member_id)
        ;

        $session->mail_verify_member_id = null;

        $this->_helper->flashMessenger->addMessage('<p class="text-info">We have send a new verification mail to the stored mail address. </p>');
        $this->forward('index', 'explore', 'default');
    }

    /**
     * @param Zend_Db_Table_Row $memberData
     * @param                   $memberEmail
     * @param string            $verificationVal
     *
     * @throws Zend_Exception
     */
    private function resendVerificationMail($memberData, $memberEmail, $verificationVal)
    {
        $config = Zend_Registry::get('config');
        $fromEmailAddress = $config->resources->mail->defaultFrom->email;

        $confirmMail = new Default_Plugin_SendMail('tpl_verify_user');
        $confirmMail->setTemplateVar('servername', $this->getServerName());
        $confirmMail->setTemplateVar('username', $memberData->username);
        $confirmMail->setTemplateVar('verificationlinktext',
            '<a href="https://' . $this->getServerName() . '/verification/' . $verificationVal
            . '">Click here to verify your email address</a>');
        $confirmMail->setTemplateVar('verificationlink',
            '<a href="https://' . $this->getServerName() . '/verification/' . $verificationVal . '">https://' . $this->getServerName()
            . '/verification/' . $verificationVal . '</a>');
        $confirmMail->setTemplateVar('verificationurl', 'https://' . $this->getServerName() . '/verification/' . $verificationVal);
        $confirmMail->setReceiverMail($memberEmail);
        $confirmMail->setFromMail($fromEmailAddress);
        $confirmMail->send();
    }

    /**
     * @return mixed
     */
    protected function getServerName()
    {
        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();

        return $request->getHttpHost();
    }

    public function requestAction()
    {
        $this->_helper->viewRenderer('resend');
        $member_id = (int)$this->getParam('i', null);

        $modelMember = new Default_Model_Member();
        $memberData = $modelMember->fetchMemberData($member_id);

        if (count($memberData->toArray()) == 0) {
            throw new Zend_Controller_Action_Exception('could not found data for member_id: ' . print_r($member_id, true));
        }

        $modelEmail = new Default_Model_MemberEmail();
        $primaryEmail = $modelEmail->fetchMemberPrimaryMail($member_id);
        $mailValidationValue = $primaryEmail['email_verification_value'];
        $memberEmail = $primaryEmail['email_address'];

        Zend_Registry::get('logger')->info(__METHOD__ . ' - resend of verification mail requested by user id: '. $this->_authMember->member_id)
        ;

        $this->resendVerificationMail($memberData, $memberEmail, $mailValidationValue);

        Zend_Registry::get('logger')->info(__METHOD__ . ' - verification mail successfully transmitted to mail server for user id: '
                                           . $memberData->member_id)
        ;

        $this->_helper->flashMessenger->addMessage('<p class="text-info">We have send a new verification mail to the stored mail address. </p>');
        $this->redirect('/');
    }

}