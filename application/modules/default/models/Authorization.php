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
class Default_Model_Authorization
{

    const LOGIN_REMEMBER_ME = 'infinity';

    /** @var string */
    protected $_dataModelName;
    /** @var  Zend_Db_Table_Abstract */
    protected $_dataTable;

    /** @var  string */
    protected $_loginMethod;
    /** @var  object */
    protected $_authUserData;

    /**
     * @param string $_dataModelName
     */
    function __construct($_dataModelName = 'Default_Model_DbTable_Member')
    {
        $this->_dataModelName = $_dataModelName;
        $this->_dataTable = new $this->_dataModelName;
    }

    /**
     * @throws Zend_Session_Exception
     * @throws Zend_Exception
     */
    public function logout()
    {
        $auth = Zend_Auth::getInstance();
        $auth->clearIdentity();

        $session = new Zend_Session_Namespace();
        //$session->unsetAll();
        //Zend_Session::forgetMe();
        Zend_Session::rememberUntil(Zend_Registry::get('config')->resources->session->cookie_lifetime);
        //Zend_Session::destroy();

        $modelRememberMe = new Default_Model_RememberMe();
        $modelRememberMe->deleteSession();
    }

    /**
     * @param string $userId
     * @param string $userSecret
     * @param bool   $setRememberMe
     * @param string $loginMethod
     *
     * @return Zend_Auth_Result
     * @throws Zend_Auth_Storage_Exception
     * @throws Zend_Session_Exception
     * @throws exception
     */
    public function authenticateUser($userId, $userSecret, $setRememberMe = false, $loginMethod = null)
    {
        if (false === empty($loginMethod)) {
            $this->_loginMethod = $loginMethod;
        }

        $authResult = $this->authenticateCredentials($userId, $userSecret, $loginMethod);
        if ($authResult->isValid()) {
            $this->updateRememberMe($setRememberMe);
            Zend_Session::regenerateId();
            Zend_Session::rememberMe();
            $this->_storeAuthSessionData();
            $this->updateUserLastOnline('member_id', $this->_authUserData->member_id);
        }

        return $authResult;
    }

    /**
     * @param      $identity
     * @param      $credential
     * @param null $loginMethod
     *
     * @return Zend_Auth_Result
     * @throws Zend_Auth_Adapter_Exception
     * @throws Zend_Exception
     */
    protected function authenticateCredentials($identity, $credential, $loginMethod = null)
    {
        /** @var Local_Auth_Adapter_Ocs $authAdapter */
        $authAdapter = Local_Auth_AdapterFactory::getAuthAdapter($identity, $credential, $loginMethod);
        $authAdapter->setIdentity($identity);
        $authAdapter->setCredential($credential);
        $authResult = $authAdapter->authenticate();

        if ($authResult->isValid()) {
            $this->_authUserData = $authAdapter->getResultRowObject(null, 'password');
        }

        return $authResult;
    }

    /**
     * @param bool $setRememberMe
     *
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     */
    public function updateRememberMe($setRememberMe = false)
    {
        $modelRememberMe = new Default_Model_RememberMe();
        if (false == $setRememberMe) {
            $modelRememberMe->deleteSession();

            return;
        }
        if ($modelRememberMe->hasValidCookie()) {
            $modelRememberMe->updateSession($this->_authUserData->member_id);
        } else {
            $modelRememberMe->createSession($this->_authUserData->member_id);
        }
    }

    /**
     * @throws Zend_Auth_Storage_Exception
     * @throws exception
     */
    protected function _storeAuthSessionData()
    {
        $extendedAuthData = $this->getExtendedAuthUserData($this->_authUserData);

        $auth = Zend_Auth::getInstance();
        $auth->getStorage()->write($extendedAuthData);
    }

    /**
     * @param object $authUserData
     *
     * @return object
     * @throws exception
     */
    protected function getExtendedAuthUserData($authUserData)
    {
        $extendedAuthUserData = new stdClass();
        if (isset($this->_loginMethod) AND $this->_loginMethod == self::LOGIN_REMEMBER_ME) {
            $modelMember = new Default_Model_Member();
            $memberData = $modelMember->fetchMemberData($authUserData->member_id);
            $extendedAuthUserData->external_id = $memberData->external_id;
            $extendedAuthUserData->username = $memberData->username;
            $extendedAuthUserData->roleId = $memberData->roleId;
            $extendedAuthUserData->avatar = $memberData->avatar;
            $extendedAuthUserData->profile_image_url = $memberData->profile_image_url;
            $extendedAuthUserData->is_active = $memberData->is_active;
            $extendedAuthUserData->is_deleted = $memberData->is_deleted;
            $extendedAuthUserData->roleName = $this->getRoleNameForUserRole($memberData->roleId);
        } else {
            $extendedAuthUserData->roleName = $this->getRoleNameForUserRole($authUserData->roleId);
        }
        $extendedAuthUserData->projects = $this->getProjectIdsForUser($authUserData->member_id);

        return (object)array_merge((array)$authUserData, (array)$extendedAuthUserData);
    }

    /**
     * @param int $roleId
     *
     * @return string
     * @throws exception
     */
    protected function getRoleNameForUserRole($roleId)
    {
        $database = Zend_Db_Table::getDefaultAdapter();

        $sql = "
                SELECT `shortname`
                FROM `member_role`
                WHERE  `member_role_id` = ?;
        ";
        $sql = $database->quoteInto($sql, $roleId, 'INTEGER', 1);
        $resultSet = $database->query($sql)->fetchAll();
        if (count($resultSet) > 0) {
            return $resultSet[0]['shortname'];
        } else {
            throw new Exception('undefined member role');
        }
    }

    /**
     * @param int $identifier
     *
     * @return array
     * @throws Zend_Db_Statement_Exception
     */
    protected function getProjectIdsForUser($identifier)
    {
        $database = Zend_Db_Table::getDefaultAdapter();
        $sql = "
                SELECT `p`.`project_id`
                FROM `project` AS `p`
                WHERE `p`.`member_id` = ?;
        ";
        $sql = $database->quoteInto($sql, $identifier, 'INTEGER', 1);
        $resultSet = $database->query($sql)->fetchAll();

        return $this->generateArrayWithKeyProjectId($resultSet);
    }

    /**
     * @param array $inputArray
     *
     * @return array
     */
    protected function generateArrayWithKeyProjectId($inputArray)
    {
        $arrayWithKeyProjectId = array();
        foreach ($inputArray as $element) {
            $arrayWithKeyProjectId[$element['project_id']] = $element;
        }

        return $arrayWithKeyProjectId;
    }

    /**
     * @param string     $identifier
     * @param string|int $identity
     *
     * @return int
     */
    public function updateUserLastOnline($identifier, $identity)
    {
        /** @var Zend_Db_Table_Abstract $dataTable */
        $dataTable = $this->_dataTable;

        return $dataTable->update(array('last_online' => new Zend_Db_Expr('NOW()')),
            $dataTable->getAdapter()->quoteIdentifier($identifier, true) . ' = ' . $identity);
    }

    /**
     * @return object
     */
    public function getAuthData()
    {
        return $this->_authUserData;
    }

    /**
     * @param int $identity
     *
     * @throws Zend_Auth_Storage_Exception
     * @throws exception
     */
    public function storeAuthSessionDataByIdentity($identity)
    {
        $authDataAll = $this->getAllAuthUserData('member_id', $identity);

        $auth = Zend_Auth::getInstance();
        $auth->getStorage()->write($authDataAll);
    }

    /**
     * @param string     $identifier
     * @param string|int $identity
     *
     * @return object
     * @throws exception
     */
    protected function getAllAuthUserData($identifier, $identity)
    {
        $this->_authUserData = $this->getAuthUserData($identifier, $identity);

        return $this->getExtendedAuthUserData($this->_authUserData);
    }

    /**
     * @param string     $identifier
     * @param string|int $identity
     *
     * @return object
     * @throws Zend_Exception
     */
    protected function getAuthUserData($identifier, $identity)
    {
        $dataTable = $this->_dataTable;
        $where = $dataTable->select()->where($dataTable->getAdapter()->quoteIdentifier($identifier, true) . ' = ?', $identity);
        $resultRow = $dataTable->fetchRow($where)->toArray();
        unset($resultRow['password']);

        return (object)$resultRow;
    }

    /**
     * @param string $identity
     *
     * @return null|object
     * @throws Zend_Exception
     */
    public function getAuthUserDataFromUnverified($identity)
    {
        $sql = "
            SELECT `m`.*, `member_email`.`email_verification_value`, `member_email`.`email_checked`, `mei`.`external_id` 
            FROM `member_email`
            JOIN `member` AS `m` ON `m`.`member_id` = `member_email`.`email_member_id`
            LEFT JOIN `member_external_id` AS `mei` ON `mei`.`member_id` = `m`.`member_id`
            WHERE `member_email`.`email_deleted` = 0 AND `member_email`.`email_verification_value` = :verification AND `m`.`is_deleted` = 0
        ";
        $resultRow = $this->_dataTable->getAdapter()->fetchRow($sql, array('verification' => $identity));
        if ($resultRow) {
            unset($resultRow['password']);

            return (object)$resultRow;
        }

        return null;
    }

    /**
     * ppload and OCS
     *
     * @param string $identity
     * @param string $credential
     * @param string $loginMethod
     *
     * @return mixed
     * @throws Zend_Auth_Adapter_Exception
     * @throws Zend_Exception
     */
    public function getAuthDataFromApi($identity, $credential, $loginMethod = null)
    {
        $authResult = $this->authenticateCredentials($identity, $credential, $loginMethod);

        if ($authResult->isValid()) {
            Zend_Session::regenerateId();
            $this->_storeAuthSessionData();
            return $this->_authUserData;
        }

        return false;
    }

    /**
     * @param string     $identifier
     * @param string|int $identity
     *
     * @return int
     */
    public function removeAllCookieInformation($identifier, $identity)
    {
        $dataTable = new Default_Model_DbTable_Session();
        $where = $dataTable->getAdapter()->quoteInto($dataTable->getAdapter()->quoteIdentifier($identifier, true) . ' = ?', $identity);

        return $dataTable->delete($where);
    }

}