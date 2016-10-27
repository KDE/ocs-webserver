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
class Local_Auth_Adapter_Ocs implements Local_Auth_Adapter_Interface
{

    const MD5 = 'enc01';
    const SHA = 'enc02';
    const PASSWORDSALT = 'ghdfklsdfgjkldfghdklgioerjgiogkldfgndfohgfhhgfhgfhgfhgfhfghfgnndf';

    protected $_db;
    protected $_tableName;
    protected $_identity;
    protected $_credential;
    protected $_encryption;
    protected $_resultRow;

    /**
     * __construct() - Sets configuration options
     *
     * @param  Zend_Db_Adapter_Abstract $dbAdapter If null, default database adapter assumed
     * @param string $tableName
     * @throws Zend_Auth_Adapter_Exception
     */
    public function __construct(Zend_Db_Adapter_Abstract $dbAdapter = null, $tableName = null)
    {
        $this->_db = $dbAdapter;
        if (empty($this->_db)) {
            $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
            if (empty($this->_db)) {
                throw new Zend_Auth_Adapter_Exception('No database adapter present');
            }
        }

        $this->_tableName = $tableName;
    }

    public static function getEncryptedPassword($password, $userSource)
    {
        return $userSource == Default_Model_DbTable_Member::SOURCE_HIVE ? sha1((self::PASSWORDSALT . $password . self::PASSWORDSALT)) : md5($password);
    }

    /**
     * Performs an authentication attempt
     *
     * @throws Zend_Auth_Adapter_Exception If authentication cannot be performed
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $validator = new Zend_Validate_EmailAddress();
        if ($validator->isValid($this->_identity)) {
            $resultSet = $this->fetchUserByEmail();
        } else {
            $resultSet = $this->fetchUserByUsername();
        }

        if (count($resultSet) == 0) {
            return $this->createAuthResult(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND, $this->_identity,
                array('A record with the supplied identity could not be found.'));
        }

        if (count($resultSet) > 1) {
            return $this->createAuthResult(Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS, $this->_identity,
                array('More than one record matches the supplied identity.'));
        }

        $this->_resultRow = array_shift($resultSet);
        return $this->createAuthResult(Zend_Auth_Result::SUCCESS, $this->_identity,
            array('Authentication successful.'));
    }

    private function fetchUserByEmail()
    {
        $sql = "
            SELECT * 
            FROM {$this->_tableName} 
            WHERE 
            is_active = :active AND 
            is_deleted = :deleted AND 
            login_method = :login AND 
            mail = :mail AND 
            password = :password";

        $this->_db->getProfiler()->setEnabled(true);
        $resultSet = $this->_db->fetchAll($sql, array(
            'active' => Default_Model_DbTable_Member::MEMBER_ACTIVE,
            'deleted' => Default_Model_DbTable_Member::MEMBER_NOT_DELETED,
            'login' => Default_Model_DbTable_Member::MEMBER_LOGIN_LOCAL,
            'mail' => $this->_identity,
            'password' => $this->_credential
        ));
        Zend_Registry::get('logger')->info(__METHOD__ . ' - sql take seconds: ' . $this->_db->getProfiler()->getLastQueryProfile()->getElapsedSecs());
        $this->_db->getProfiler()->setEnabled(false);

        return $resultSet;
    }

    private function fetchUserByUsername()
    {
        $sql = "
            SELECT * 
            FROM {$this->_tableName} 
            WHERE 
            is_active = :active AND 
            is_deleted = :deleted AND 
            login_method = :login AND 
            username = :username AND 
            password = :password";

        $this->_db->getProfiler()->setEnabled(true);
        $resultSet = $this->_db->fetchAll($sql, array(
            'active' => Default_Model_DbTable_Member::MEMBER_ACTIVE,
            'deleted' => Default_Model_DbTable_Member::MEMBER_NOT_DELETED,
            'login' => Default_Model_DbTable_Member::MEMBER_LOGIN_LOCAL,
            'username' => $this->_identity,
            'password' => $this->_credential
        ));
        Zend_Registry::get('logger')->info(__METHOD__ . ' - sql take seconds: ' . $this->_db->getProfiler()->getLastQueryProfile()->getElapsedSecs());
        $this->_db->getProfiler()->setEnabled(false);

        return $resultSet;
    }

    protected function createAuthResult($code, $identity, $messages)
    {
        return new Zend_Auth_Result(
            $code,
            $identity,
            $messages
        );
    }

    /**
     * @param string $identity
     * @return Local_Auth_Adapter_Ocs
     */
    public function setIdentity($identity)
    {
        $this->_identity = $identity;
        Zend_Registry::get('logger')->info(__METHOD__ . ' - ' . print_r($identity, true));
        return $this;
    }

    /**
     * @param string $credential
     * @return Local_Auth_Adapter_Ocs
     * @throws Zend_Exception
     */
    public function setCredential($credential)
    {
        switch ($this->_encryption) {
            case self::MD5 :
                $this->_credential = md5($credential);
                Zend_Registry::get('logger')->info(__METHOD__ . ' - pling: $credential = ' . $this->_credential);
                break;
            case self::SHA :
                $this->_credential = sha1((self::PASSWORDSALT . $credential . self::PASSWORDSALT));
                Zend_Registry::get('logger')->info(__METHOD__ . ' - hive: $credential = ' . $this->_credential);
                break;
            default:
                throw new Zend_Exception('There is no default case for credential encryption.');
        }

        return $this;
    }

    /**
     * @param mixed $encryption
     * @return Local_Auth_Adapter_Ocs
     */
    public function setEncryption($encryption)
    {
        $this->_encryption = $encryption;
        return $this;
    }

    /**
     * getResultRowObject() - Returns the result row as a stdClass object
     *
     * @param  string|array $returnColumns
     * @param  string|array $omitColumns
     * @return stdClass|boolean
     */
    public function getResultRowObject($returnColumns = null, $omitColumns = null)
    {
        if (!$this->_resultRow) {
            return false;
        }

        $returnObject = new stdClass();

        if (null !== $returnColumns) {

            $availableColumns = array_keys($this->_resultRow);
            foreach ((array)$returnColumns as $returnColumn) {
                if (in_array($returnColumn, $availableColumns)) {
                    $returnObject->{$returnColumn} = $this->_resultRow[$returnColumn];
                }
            }
            return $returnObject;

        } elseif (null !== $omitColumns) {

            $omitColumns = (array)$omitColumns;
            foreach ($this->_resultRow as $resultColumn => $resultValue) {
                if (!in_array($resultColumn, $omitColumns)) {
                    $returnObject->{$resultColumn} = $resultValue;
                }
            }
            return $returnObject;

        } else {

            foreach ($this->_resultRow as $resultColumn => $resultValue) {
                $returnObject->{$resultColumn} = $resultValue;
            }
            return $returnObject;
        }
    }

}