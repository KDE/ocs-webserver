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

    const LOGIN_INFINITY = 'infinity';

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
     * @param string $identity
     * @param string $socialNetwork
     * @return null|object
     */
    public function getAuthDataFromSocialUser($identity, $socialNetwork)
    {
        $dataTable = $this->_dataTable;
        $where = $dataTable->select()
            ->where('login_method = ?', $socialNetwork)
            ->where('social_username = ?', $identity)
            ->where('is_deleted = 0')
            ->where('is_active = 1');

        $resultRow = $dataTable->fetchRow($where);

        if ($resultRow) {
            return (object)$resultRow->toArray();
        } else {
            return null;
        }
    }

    /**
     * @param string $userId
     * @param string $userSecret
     * @param bool $rememberMe
     * @param string $loginMethod
     * @return Zend_Auth_Result
     */
    public function authenticateUserSession($userId, $userSecret, $rememberMe, $loginMethod = null)
    {
        if (false === empty($loginMethod)) {
            $this->_loginMethod = $loginMethod;
        }

        $authResult = $this->authenticateCredentials($userId, $userSecret, $loginMethod);

        if ($authResult->isValid()) {

            if (true == $rememberMe) {
                $this->_setOrRefreshRememberMe();
            }

            //Zend_Session::regenerateId();
            Zend_Session::rememberMe(1209600);

            $this->_storeAuthSessionData();

            $this->updateUserLastOnline('member_id', $this->_authUserData->member_id);
        }

        return $authResult;
    }

    public function authenticateCredentials($identity, $credential, $loginMethod = null)
    {
        $authAdapter = Local_Auth_AdapterFactory::getAuthAdapter($identity, $loginMethod);
        $authAdapter->setIdentity($identity);
        $authAdapter->setCredential($credential);
        $authResult = $authAdapter->authenticate();

        if ($authResult->isValid()) {
            $this->_authUserData = $authAdapter->getResultRowObject(null, 'password');
        }
        return $authResult;
    }

    protected function _setOrRefreshRememberMe()
    {
        $config = Zend_Registry::get('config');
        $cookieName = $config->settings->auth_session->remember_me->name;
        $remember_me_seconds = $config->settings->auth_session->remember_me->timeout;
        $cookieExpire = time() + (int)$remember_me_seconds;
        $domain = Zend_Controller_Front::getInstance()->getRequest()->getHttpHost();

        $sessionTable = new Default_Model_Session();
        $sessionDataRow = $sessionTable->updateOrCreateSession($this->_authUserData->member_id);

        $sessionData = array();
        $sessionData['mi'] = $sessionDataRow->member_id;
        $sessionData['u'] = $sessionDataRow->uuid;

        setcookie($cookieName, serialize($sessionData), $cookieExpire, '/', $domain, null, true);
    }

    protected function _storeAuthSessionData()
    {
        $extendedAuthData = $this->getExtendedAuthUserData($this->_authUserData);

        $auth = Zend_Auth::getInstance();
        $auth->getStorage()->write($extendedAuthData);
    }

    /**
     * @param object $authUserData
     * @return object
     */
    public function getExtendedAuthUserData($authUserData)
    {
        $extendedAuthUserData = new stdClass();
        if (isset($this->_loginMethod) AND $this->_loginMethod == self::LOGIN_INFINITY) {
            $modelMember = new Default_Model_Member();
            $memberData = $modelMember->fetchMemberData($authUserData->member_id);
            $extendedAuthUserData->username = $memberData->username;
            $extendedAuthUserData->roleId = $memberData->roleId;
            $extendedAuthUserData->avatar = $memberData->avatar;
            $extendedAuthUserData->profile_image_url = $memberData->profile_image_url;
            $extendedAuthUserData->is_active = $memberData->is_active;
            $extendedAuthUserData->is_deleted = $memberData->is_deleted;
            $extendedAuthUserData->roleName = Default_Plugin_AclRules::ROLENAME_COOKIEUSER;
        } else {
            $extendedAuthUserData->roleName = $this->getRoleNameForUserRole($authUserData->roleId);
        }
        $extendedAuthUserData->projects = $this->getProjectIdsForUser($authUserData->member_id);

        return (object)array_merge((array)$authUserData, (array)$extendedAuthUserData);
    }

    /**
     * @param int $roleId
     * @return string
     * @throws exception
     */
    protected function getRoleNameForUserRole($roleId)
    {
        $database = Zend_Db_Table::getDefaultAdapter();

        $sql = "
                SELECT shortname
                FROM member_role
                WHERE  member_role_id = ?;
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
     * @return array
     */
    protected function getProjectIdsForUser($identifier)
    {
        $database = Zend_Db_Table::getDefaultAdapter();
        $sql = "
                SELECT p.project_id
                FROM project AS p
                WHERE p.member_id = ?;
        ";
        $sql = $database->quoteInto($sql, $identifier, 'INTEGER', 1);
        $resultSet = $database->query($sql)->fetchAll();

        return $this->generateArrayWithKeyProjectId($resultSet);
    }

    /**
     * @param array $inputArray
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
     * @param string $identifier
     * @param string|int $identity
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
     */
    public function storeAuthSessionDataByIdentity($identity)
    {
        $authDataAll = $this->getAllAuthUserData('member_id', $identity);

        $auth = Zend_Auth::getInstance();
        $auth->getStorage()->write($authDataAll);
    }

    /**
     * @param string $identifier
     * @param string|int $identity
     * @return object
     */
    public function getAllAuthUserData($identifier, $identity)
    {
        $authUserData = $this->getAuthUserData($identifier, $identity);
        return $this->getExtendedAuthUserData($authUserData);
    }

    /**
     * @param string $identifier
     * @param string|int $identity
     * @return object
     */
    public function getAuthUserData($identifier, $identity)
    {
        Zend_Registry::get('logger')->info(__METHOD__ . ' - $identifier: ' . print_r($identifier,
                true) . ' :: $identity: ' . print_r($identity, true));
        /** @var Zend_Db_Table_Abstract $dataTable */
        $dataTable = $this->_dataTable;
        $where = $dataTable->select()->where($dataTable->getAdapter()->quoteIdentifier($identifier, true) . ' = ?',
            $identity);
        $resultRow = $dataTable->fetchRow($where)->toArray();
        Zend_Registry::get('logger')->info(__METHOD__ . ' - $resultrow: ' . print_r($resultRow, true));
        unset($resultRow['password']);
        return (object)$resultRow;
    }

    /**
     * @param string $identity
     * @return object
     */
    public function getAuthUserDataFromUnverified($identity)
    {
        Zend_Registry::get('logger')->info(__METHOD__ . ' - $identity: ' . print_r($identity, true));
        $sql = "
        SELECT member.* FROM member
        STRAIGHT_JOIN member_email ON member.member_id = member_email.email_member_id AND email_deleted = 0 AND email_checked is null
        WHERE member_email.email_verification_value = :verification;
        ";
        $resultRow = $this->_dataTable->getAdapter()->fetchRow($sql, array('verification' => $identity));
        Zend_Registry::get('logger')->info(__METHOD__ . ' - $resultRow: ' . print_r($resultRow, true));
        unset($resultRow['password']);
        return (object)$resultRow;
    }

    /**
     * ppload and OCS
     *
     * @param string $identity
     * @param string $credential
     * @param string $loginMethod
     * @return mixed
     */
    public function getAuthDataFromApi($identity, $credential, $loginMethod = null)
    {
        $authResult = $this->authenticateCredentials($identity, $credential, $loginMethod);

        if ($authResult->isValid()) {
            return $this->_authUserData;
        }
        return false;
    }

    /**
     * @param string $identifier
     * @param string|int $identity
     * @return int
     */
    public function removeAllCookieInformation($identifier, $identity)
    {

        $dataTable = new Default_Model_DbTable_Session();
        $where = $dataTable->getAdapter()->quoteInto($dataTable->getAdapter()->quoteIdentifier($identifier,
                true) . ' = ?', $identity);

        return $dataTable->delete($where);
    }

    /**
     * @param int $identifier
     * @return array
     */
    protected function getGroupProjectIdsForUser($identifier)
    {
        $database = Zend_Db_Table::getDefaultAdapter();
        $sql = "
                SELECT p.project_id
                FROM member_group m, project AS p
                WHERE m.group_id = p.member_id
    				  AND m.is_active = 1
    				  AND m.member_id = ?;
        ";
        $sql = $database->quoteInto($sql, $identifier, 'INTEGER', 1);
        $resultSet = $database->query($sql)->fetchAll();

        return $this->generateArrayWithKeyProjectId($resultSet);
    }

}