<?php


class Default_Model_Auth_User extends Local_Auth_User_Abstract
{

    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    protected function __construct()
    {
    }

    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    protected function __clone()
    {
    }

    /**
     * Returns an instance
     *
     * Singleton pattern implementation
     *
     * @return Local_Auth_User_Interface Provides a fluent interface
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * @param array $config
     * @return $this
     * @throws Exception
     */
    public function startSession(array $config)
    {
        $name = !empty($config['name']) ? $config['name'] : 'ocs_id';

        $string_interval = isset($config['expire']) ? $config['expire'] : '30 days';
        $date = new DateTime();
        $interval = DateInterval::createFromDateString($string_interval);
        $expire = $date->add($interval)->getTimestamp();

        $path = '/';

        $domain = !empty($config['domain']) ? $config['domain'] : Zend_Controller_Front::getInstance()->getRequest()->getHttpHost();

        $secure = isset($config['secure']) ? $config['secure'] : true;

        $http_only = isset($config['http_only']) ? $config['http_only'] : true;

        setcookie($name, $this->auth_token, $expire, $path, $domain, $secure, $http_only);

        return $this;
    }

    public function initSession(array $config)
    {
        $name = !empty($config['name']) ? $config['name'] : 'ocs_id';

        $this->auth_token = isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;

        if (false == $this->getTokenHandler()->isValid($this->auth_token)) {
            return;
        }

        $session_data = $this->getTokenHandler()->decode($this->auth_token);

        $this->userData = $this->fetchUserData($session_data->sub);
    }

    private function fetchUserData($member_id)
    {
        $modelMember = new Default_Model_Member();
        $member = $modelMember->getExtendedAuthUserData($member_id);

        return $member;
    }

}