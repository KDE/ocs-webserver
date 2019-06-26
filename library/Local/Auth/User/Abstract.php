<?php


abstract class Local_Auth_User_Abstract implements Local_Auth_User_Interface
{
    /**
     * Singleton instance
     *
     * @var Local_Auth_User_Interface
     */
    protected static $_instance = null;

    /**
     * Auth token handler
     *
     * @var Local_Auth_Token_Interface
     */
    protected $_token = null;

    /**
     * @var array
     */
    protected $userData;

    /**
     * @var array
     */
    protected $modifiedFields;

    /**
     * @var string
     */
    protected $auth_token;

    public function getUserData()
    {
        return $this->userData;
    }

    /**
     * @param array $user_data
     * @return $this
     */
    public function setUserData(array $user_data)
    {
        $this->userData = $user_data;

        return $this;
    }

    /**
     * Retrieve row field value
     *
     * @param string $columnName The user-specified column name.
     * @return string             The corresponding column value.
     * @throws Local_Auth_Exception
     */
    public function __get($columnName)
    {
        if (!array_key_exists($columnName, $this->userData)) {
            throw new Local_Auth_Exception("Specified column \"$columnName\" is not in the row");
        }

        return $this->userData[$columnName];
    }

    /**
     * Set row field value
     *
     * @param string $columnName The column key.
     * @param mixed  $value The value for the property.
     * @return void
     * @throws Local_Auth_Exception
     */
    public function __set($columnName, $value)
    {
        if (!array_key_exists($columnName, $this->userData)) {
            throw new Local_Auth_Exception("Specified column \"$columnName\" is not in the row");
        }
        $this->userData[$columnName] = $value;
        $this->modifiedFields[$columnName] = true;
    }

    public function hasIdentity()
    {
        return !empty($this->userData);
    }

    public function getIdentity()
    {
        return $this->userData;
    }

    public function clearIdentity()
    {
        $this->userData = null;
        $this->auth_token = null;
    }

    /**
     * @param Local_Auth_Token_Interface $token
     * @return Local_Auth_User_Abstract
     */
    public function setTokenHandler($token)
    {
        $this->_token = $token;

        return $this;
    }

    /**
     * @return Local_Auth_Token_Interface
     */
    public function getTokenHandler()
    {
        if (null === $this->_token) {
            $this->setTokenHandler(new Local_Auth_Token_Jwt());
        }

        return $this->_token;
    }

    public function setAuthToken($auth_token)
    {
        $this->auth_token = $auth_token;

        return $this;
    }

    public function getAuthToken()
    {
        return $this->auth_token;
    }

    public function hasAuthToken()
    {
        return !empty($this->auth_token);
    }

    public function clearAuthToken()
    {
        $this->auth_token = null;

        return $this;
    }

    /**
     * @param array $config
     * @return $this
     */
    public function initAuthToken(array $config)
    {
        // create auth token
        $jwt = new Local_Auth_Token_Jwt($config);
        $this->auth_token = $jwt->encode($this->userData);

        return $this;
    }

    /**
     * @param array $config
     * @return $this
     * @throws Exception
     */
    public abstract function startSession(array $config);

    /**
     * @param array $config
     * @return $this
     */
    public abstract function initSession(array $config);

    /**
     * @param array $config
     * @return $this
     */
    public abstract function removeSession(array $config);

}