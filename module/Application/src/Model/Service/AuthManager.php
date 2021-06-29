<?php
/**
 *   ocs-webserver
 *
 *   Copyright 2016 by pling GmbH.
 *
 *     This file is part of ocs-webserver.
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Application\Model\Service;


use Application\Model\Service\Exceptions\AlreadyLoggedInException;
use Application\Model\Service\Exceptions\NotLoggedInException;
use Application\Model\Service\Interfaces\AuthManagerInterface;
use Exception;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Result;
use Laminas\Session\SessionManager;

/**
 * The AuthManager service is responsible for user's login/logout and simple access
 * filtering. The access filtering feature checks whether the current visitor
 * is allowed to see the given page or not.
 */
class AuthManager implements AuthManagerInterface
{
    /**
     * @var \Application\Model\Entity\CurrentUser
     */
    private static $user;
    /**
     * Authentication service.
     *
     * @var AuthenticationService
     */
    private $authService;
    /**
     * Session manager.
     *
     * @var SessionManager
     */
    private $sessionManager;
    /**
     * Contents of the 'access_filter' config key.
     *
     * @var array
     */
    private $config;

    /**
     * Constructs the service.
     *
     * @param AuthenticationService $authService
     * @param SessionManager        $sessionManager
     * @param array                 $config
     */
    public function __construct($authService, $sessionManager, $config)
    {
        $this->authService = $authService;
        $this->sessionManager = $sessionManager;
        $this->config = $config;
    }

    /**
     * Performs a login attempt. If $rememberMe argument is true, it forces the session
     * to last for one month (otherwise the session expires on one hour).
     *
     * @param $identity
     * @param $password
     * @param $rememberMe
     *
     * @return Result
     * @throws AlreadyLoggedInException
     */
    public function login($identity, $password, $rememberMe)
    {
        // Check if user has already logged in. If so, do not allow to log in
        // twice.
        if ($this->authService->getIdentity() != null) {
            throw new AlreadyLoggedInException('Already logged in');
        }

        // Authenticate with login/password.
        /** @var AuthAdapter $authAdapter */
        $authAdapter = $this->authService->getAdapter();
        $authAdapter->setIdentity($identity);
        $authAdapter->setPassword($password);
        $result = $this->authService->authenticate();

        if ($result->isValid()) {
            self::$user = $authAdapter->getFullUserData();

            //session backwards compatibility
            $_SESSION['ocs_auth'] = array('storage' => (object)(self::$user)->getArrayCopy());
        }

        // If user wants to "remember him", we will make session to expire in
        // one month. By default session expires in 1 hour (as specified in our
        // config/global.php file).
        if ($result->getCode() == Result::SUCCESS && $rememberMe) {
            // When the session cookie will expire is defined in session_config => options => remember_me_seconds.
            $this->sessionManager->rememberMe($this->sessionManager->getConfig()->getRememberMeSeconds());
        }

        return $result;
    }

    /**
     * This is a simple access control filter. It is able to restrict unauthorized
     * users to visit certain pages.
     *
     * This method uses the 'access_filter' key in the config file and determines
     * whether the current visitor is allowed to access the given controller action
     * or not. It returns true if allowed; otherwise false.
     *
     * @param $controllerName
     * @param $actionName
     *
     * @return bool
     * @throws Exception
     */
    public function filterAccess($controllerName, $actionName)
    {
        // Determine mode - 'restrictive' (default) or 'permissive'. In restrictive
        // mode all controller actions must be explicitly listed under the 'access_filter'
        // config key, and access is denied to any not listed action for unauthorized users.
        // In permissive mode, if an action is not listed under the 'access_filter' key,
        // access to it is permitted to anyone (even for not logged in users.
        // Restrictive mode is more secure and recommended to use.
        $mode = isset($this->config['options']['mode']) ? $this->config['options']['mode'] : 'restrictive';
        if ($mode != 'restrictive' && $mode != 'permissive') {
            throw new Exception('Invalid access filter mode (expected either restrictive or permissive mode');
        }

        if (isset($this->config['controllers'][$controllerName])) {
            $items = $this->config['controllers'][$controllerName];
            foreach ($items as $item) {
                $actionList = $item['actions'];
                $allow = $item['allow'];
                if (is_array($actionList) && in_array($actionName, $actionList) || $actionList == '*') {
                    if ($allow == '*') {
                        return true;
                    } // Anyone is allowed to see the page.
                    else {
                        if ($allow == '@' && $this->authService->hasIdentity()) {
                            return true; // Only authenticated user is allowed to see the page.
                        } else {
                            return false; // Access denied.
                        }
                    }
                }
            }
        }

        // In restrictive mode, we forbid access for unauthorized users to any
        // action not listed under 'access_filter' key (for security reasons).
        if ($mode == 'restrictive' && !$this->authService->hasIdentity()) {
            return false;
        }

        // Permit access to this page.
        return true;
    }

    /**
     * @param string $identity
     */
    public function initSessionForMember($identity)
    {
        $this->authService->getStorage()->write($identity);
    }

    /**
     * Returns the current User or empty object if not logged in.
     *
     * @param bool $useCachedUser If true, the User entity is fetched only on the first call (and cached on subsequent
     *                            calls).
     *
     * @return \Application\Model\Entity\CurrentUser
     * @throws Exception
     */
    public function getCurrentUser($useCachedUser = true)
    {
        // Check if user is logged in.
        if (false === $this->authService->hasIdentity()) {
            // otherwise return an empty user object
            return new \Application\Model\Entity\CurrentUser();
        }

        // Check if user is already fetched previously.
        if ($useCachedUser && self::$user !== null) {
            return self::$user;
        }

        // Fetch user entity from database.
        /** @var AuthAdapter $authAdapter */
        $authAdapter = $this->authService->getAdapter();
        self::$user = $authAdapter->getAuthenticationRepository()->findOneByEmail($this->authService->getIdentity());

        if (false === self::$user->hasIdentity()) {
            // Oops.. the identity presents in session, but there is no such user in database.
            $GLOBALS['ocs_log']->warn('No user found with such ID: ' . $this->authService->getIdentity());
            session_unset();

            //Return empty user object
            return self::$user;
        }

        // check user status.
        if (self::$user->is_active == 0 or self::$user->is_deleted == 1) {
            // okay, user is deactivated - toast the session and move on
            session_unset();
            try {
                $this->logout();
            } catch (Exception $e) {
                $GLOBALS['ocs_log']->warn($e->getMessage());
            }
            self::$user = new \Application\Model\Entity\CurrentUser();
        }

        // Return the User entity we found (or empty one if user deactivated).
        return self::$user;
    }

    /**
     * Performs user logout.
     *
     * @throws NotLoggedInException
     */
    public function logout()
    {
        // Allow to log out only when user is logged in.
        if ($this->authService->getIdentity() == null) {
            throw new NotLoggedInException('The user is not logged in');
        }

        // Remove identity from session.
        $this->authService->clearIdentity();
        // Set local user to empty object
        self::$user = new \Application\Model\Entity\CurrentUser();

        //clear old session structure
        unset($_SESSION['ocs_auth']);
    }

}