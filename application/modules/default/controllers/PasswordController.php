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
class PasswordController extends Local_Controller_Action_DomainSwitch
{

    const C = 10800;
    private $from_alias = "opendesktop.org";
    private $from_mail = "contact@opendesktop.org";
    private $options = array(
        // Encryption type - Openssl or Mcrypt
        'adapter'   => 'mcrypt',
        // Initialization vector
        'vector'    => '236587hgtyujkirtfgty5678',
        // Encryption algorithm
        'algorithm' => 'rijndael-192',
        // Encryption key
        'key'       => 'KFJGKDK$$##^FFS345678FG2'
    );

    public function requestAction()
    {
        if ($this->_request->isGet()) {
            return;
        }

        $email = $this->getParam('mail');
        if (empty($email)) {
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - param mail is empty');

            return;
        }
        $validate = new Zend_Validate_EmailAddress();

        if (false == $validate->isValid($email)) {
            $this->_helper->flashMessenger->addMessage('<p class="text-error">Please type in a valid email address.</p>');
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - param mail not valid');

            return;
        }

        $modelMember = new Default_Model_Member();
        $member = $modelMember->findActiveMemberByMail($email);

        if (empty($member->member_id)) {

            $member = $modelMember->findActiveMemberByMail($email.'_double');

            if (empty($member->member_id)) {
                Zend_Registry::get('logger')->debug(__METHOD__ . ' - no active member found. ' . $email);

                return;
            }
        }

        $url = $this->generateUrl($member);

        $this->sendMail($email, $url, 'Reset your password');
        $this->redirect("/login");
    }

    private function generateUrl($member)
    {
        $encrypt = $this->getEncryptFilter();

        $duration = self::C; // in seconds

        $payload = array(
            'member_id' => $member->member_id,
            'expire'    => time() + $duration
        );

        /** @var Zend_Controller_Request_Http $request */
        $request = Zend_Controller_Front::getInstance()->getRequest();

        $secret =  $this->base64url_encode($encrypt->filter(json_encode($payload)));

        $url = $request->getScheme() . '://' . $request->getHttpHost() . '/password/change?' . $secret;

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - generated url: ' . $url);

        Zend_Registry::get('cache')->save($secret, sha1($secret), array(), self::C);

        return $url;
    }

    /**
     * @return Zend_Filter_Encrypt
     */
    private function getEncryptFilter()
    {
        /* Initialize the library and pass the options */
        $filter = new Zend_Filter_Encrypt($this->options);

        return $filter;
    }

    /**
     * @param $mail
     * @param $url
     * @param $subject
     *
     * @throws Zend_Exception
     * @throws Zend_View_Exception
     */
    private function sendMail($email, $url, $subject)
    {
        $renderer = new Zend_View();
        $renderer->setScriptPath(APPLICATION_PATH . '/modules/default/views/emails/');

        $renderer->assign('mail', $email);
        $renderer->assign('url', $url);

        $body_text = $renderer->render('url_forgot_password.phtml');

        try {
            $mail = new Zend_Mail('utf-8');
            $mail->setBodyHtml($body_text);
            $mail->setFrom($this->from_mail, $this->from_alias);

            $mail->addTo($email);
            $mail->setSubject($subject);
            $mail->send();
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . " - " . print_r($e, true) . PHP_EOL);
        }
    }

    public function changeAction()
    {
        $debugMsg = '' . __METHOD__ . PHP_EOL;
        $uri_part = explode("?", $this->_request->getRequestUri());
        $debugMsg .= ' - $uri_part = ' . print_r($uri_part, true) . PHP_EOL;
        $secret = preg_replace('/[^-a-zA-Z0-9_=\/]/', '', array_pop($uri_part));
        $debugMsg .= ' - $secret = ' . print_r($secret, true) . PHP_EOL;

        $decrypt = $this->getDecryptFilter();
        $step1 = $this->base64url_decode($secret);
        $debugMsg .= ' - $step1 = ' . print_r($step1, true) . PHP_EOL;
        $step2 = $decrypt->filter($step1);
        $debugMsg .= ' - $step2 = ' . print_r($step2, true) . PHP_EOL;
        $payload = json_decode(trim($step2), true);
        $debugMsg .= ' - $payload = ' . print_r($payload, true) . PHP_EOL;

        if (false == Zend_Registry::get('cache')->load(sha1($secret))) {
            $debugMsg .= '- unknown request url' . PHP_EOL;
            Zend_Registry::get('logger')->debug($debugMsg);
            throw new Zend_Controller_Action_Exception('Unknown request url for password change');
        }

        if (empty($payload) OR (false == is_array($payload))) {
            $debugMsg .= '- wrong request url' . PHP_EOL;
            Zend_Registry::get('logger')->debug($debugMsg);
            throw new Zend_Controller_Action_Exception('Wrong request url for password change');
        }

        if (time() > $payload['expire']) {
            $debugMsg .= '- password change request expired' . PHP_EOL;
            Zend_Registry::get('logger')->debug($debugMsg);
            $this->_helper->flashMessenger->addMessage('<p class="text-error">Your password change request is expired.</p>');
            $this->forward('login', 'authorization');
        }

        $this->view->assign('action', '/password/change?' . $secret);

        if ($this->_request->isGet()) {
            $debugMsg .= '- show password change form' . PHP_EOL;
            Zend_Registry::get('logger')->debug($debugMsg);
            return;
        }

        $filterInput = new Zend_Filter_Input(array('*' => 'StringTrim'), array(
            'password1' => array(
                new Zend_Validate_StringLength(array('min' => 6, 'max' => 200)),
                'presence' => 'required'
            ),
            'password2' => array(
                new Zend_Validate_StringLength(array('min' => 6, 'max' => 200)),
                'presence' => 'required'
            ),
        ), $this->getAllParams());

        if (false === $filterInput->isValid()) {
            foreach ($filterInput->getMessages() as $message) {
                $this->_helper->flashMessenger->addMessage('<p class="text-error">' . $message . '</p>');
            }

            return;
        }

        $password1 = $filterInput->getUnescaped('password1');
        $password2 = $filterInput->getUnescaped('password2');

        if ($password1 != $password2) {
            $this->_helper->flashMessenger->addMessage('<p class="text-error">Your passwords are not identical.</p>');
            return;
        }

        $model_member = new Default_Model_DbTable_Member();
        $member_data = $model_member->fetchRow(array('member_id = ?' => $payload['member_id']));
        
        if($member_data->password_type == Default_Model_Member::PASSWORD_TYPE_HIVE) {
            //Save old data
            $member_data->password_old = $member_data->password;
            $member_data->password_type_old = Default_Model_Member::PASSWORD_TYPE_HIVE;
            
            //Change type and password
            $member_data->password_type = Default_Model_Member::PASSWORD_TYPE_OCS;
        }
        
        $member_data->password = Local_Auth_Adapter_Ocs::getEncryptedPassword($password1, Default_Model_Member::PASSWORD_TYPE_OCS);
        $member_data->save();

        Zend_Registry::get('cache')->remove(sha1($secret));

        //Update Auth-Services
        try {
            $id_server = new Default_Model_Ocs_OAuth();
            $id_server->updatePasswordForUser($member_data->member_id);
            $messages = $id_server->getMessages();
            if (false == empty($messages)) {
                Zend_Registry::get('logger')->info(json_encode($messages));
            }
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $ldap_server = new Default_Model_Ocs_Ldap();
            $ldap_server->updatePassword($member_data->member_id, $password1);
            $messages = $ldap_server->getMessages();
            if (false == empty($messages)) {
                Zend_Registry::get('logger')->info(json_encode($messages));
            }
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        $debugMsg .= '- password changed' . PHP_EOL;
        Zend_Registry::get('logger')->debug($debugMsg);

        $this->_helper->flashMessenger->addMessage('<p class="text-error">Your password is changed.</p>');
        $this->redirect($this->_helper->url('login', 'authorization'));
    }
    
    
    public function setpasswordAction() 
    {
        $debugMsg = "";
        $this->view->assign('action', '/password/setpassword');

        if ($this->_request->isGet()) {
            $debugMsg .= '- show password change form' . PHP_EOL;
            Zend_Registry::get('logger')->debug($debugMsg);
            return;
        }

        $filterInput = new Zend_Filter_Input(array('*' => 'StringTrim'), array(
            'password1' => array(
                new Zend_Validate_StringLength(array('min' => 6, 'max' => 200)),
                'presence' => 'required'
            ),
            'password2' => array(
                new Zend_Validate_StringLength(array('min' => 6, 'max' => 200)),
                'presence' => 'required'
            ),
        ), $this->getAllParams());

        if (false === $filterInput->isValid()) {
            foreach ($filterInput->getMessages() as $message) {
                $this->_helper->flashMessenger->addMessage('<p class="text-error">' . $message . '</p>');
            }

            return;
        }

        $password1 = $filterInput->getUnescaped('password1');
        $password2 = $filterInput->getUnescaped('password2');

        if ($password1 != $password2) {
            $this->_helper->flashMessenger->addMessage('<p class="text-error">Your passwords are not identical.</p>');
            return;
        }

        $model_member = new Default_Model_DbTable_Member();
        $auth = Zend_Auth::getInstance();
        $memberId = $auth->getStorage()->read()->member_id;
        $member_data = $model_member->fetchRow(array('member_id = ?' => $memberId));
        
        $member_data->password = Local_Auth_Adapter_Ocs::getEncryptedPassword($password1, Default_Model_Member::PASSWORD_TYPE_OCS);
        $member_data->save();

        //Update Auth-Services
        try {
            $id_server = new Default_Model_Ocs_OAuth();
            $id_server->updatePasswordForUser($member_data->member_id);
            $messages = $id_server->getMessages();
            if (false == empty($messages)) {
                Zend_Registry::get('logger')->info(json_encode($messages));
            }
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $ldap_server = new Default_Model_Ocs_Ldap();
            $ldap_server->updatePassword($member_data->member_id, $password1);
            $messages = $ldap_server->getMessages();
            if (false == empty($messages)) {
                Zend_Registry::get('logger')->info(json_encode($messages));
            }
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        $debugMsg .= '- password for Github User changed' . PHP_EOL;
        Zend_Registry::get('logger')->debug($debugMsg);

        $this->_helper->flashMessenger->addMessage('<p class="text-error">Your password was set.</p>');
        $this->redirect($this->_helper->url('login', 'authorization'));
    }
    
    

    /**
     * @return Zend_Filter_Encrypt
     */
    private function getDecryptFilter()
    {
        /* Initialize the library and pass the options */
        $filter = new Zend_Filter_Decrypt($this->options);

        return $filter;
    }

    protected function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

}