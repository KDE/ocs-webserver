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


use Application\Model\Entity\CurrentUser;
use Application\Model\Interfaces\AuthenticationRepositoryInterface;
use Application\Model\Repository\AuthenticationRepository;
use Application\Model\Service\Entity\Auth\Result;
use Application\Model\Service\Interfaces\FullUserDataInterface;
use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Hydrator\Reflection as ReflectionHydrator;
use Laminas\Validator\EmailAddress;
use Library\Tools\PasswordEncrypt;

/**
 * Adapter used for authenticating user. It takes login and password on input
 * and checks the database if there is a user with such login (email) and password.
 * If such user exists, the service returns its identity (email). The identity
 * is saved to session and can be retrieved later with Identity view helper provided
 * by ZF3.
 */
class AuthAdapter implements AdapterInterface, FullUserDataInterface
{
    const PASSWORDSALT = 'ghdfklsdfgjkldfghdklgioerjgiogkldfgndfohgfhhgfhgfhgfhgfhfghfgnndf';

    /**
     * User identity.
     *
     * @var string
     */
    private $identity;

    /**
     * Password
     *
     * @var string
     */
    private $password;

    /**
     * Entity manager.
     *
     * @var AuthenticationRepositoryInterface
     */
    private $repository_authentication;
    /**
     * @var CurrentUser
     */
    private $fullUserData;

    /**
     * Constructor.
     *
     * @param $repositoryAuthentication
     */
    public function __construct($repositoryAuthentication)
    {
        $this->repository_authentication = $repositoryAuthentication;
    }

    /**
     * Sets user email.
     *
     * @param string $identity
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;
    }

    /**
     * Sets password.
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = (string)$password;
    }

    /**
     * Performs an authentication attempt.
     *
     * @return Result|\Laminas\Authentication\Result
     */
    public function authenticate()
    {
        $validator = new EmailAddress();
        if ($validator->isValid($this->identity)) {
            // Check the database if there is a user with such email.
            $resultSet = $this->repository_authentication->findAllByEmail($this->identity);
        } else {
            // Otherwise check the database if there is a user with such username.
            $resultSet = $this->repository_authentication->findAllByUsername($this->identity);
        }

        if ($resultSet->count() > 1) {
            // For historical reasons, before we can return a result, we have to check the password against all the users we found.
            $resultSet = $this->matchPasswordUsers($resultSet);
            // check it again
            if ($resultSet->count() > 1) {
                return new Result(
                    Result::FAILURE_IDENTITY_AMBIGUOUS, null, ['More than one record matches the supplied identity.']
                );
            }
        }

        // If there is no such user, return 'Identity Not Found' status.
        if ($resultSet->count() == 0) {
            return new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND, null, ['A record with the supplied identity could not be found.']
            );
        }

        $this->fullUserData = $this->repository_authentication->findOneById($resultSet->current()->member_id);

        if (empty($this->fullUserData->email_checked)) {
            return new Result(Result::FAILURE_MAIL_NOT_VALIDATED, $this->fullUserData->mail, ['Mail address not validated.']);
        }

        // If the user with such email exists, we need to check if it is active or retired.
        // Do not allow retired users to log in.
        if (($this->fullUserData->is_active === AuthenticationRepository::MEMBER_INACTIVE) or ($this->fullUserData->is_deleted === AuthenticationRepository::MEMBER_DELETED)) {
            return new Result(Result::FAILURE_ACCOUNT_INACTIVE, $this->fullUserData->mail, ['User is retired or inactive.']);
        }

        // Now we need to calculate hash based on user-entered password and compare
        // it with the password hash stored in database.
        if (hash_equals($this->fullUserData->password, $this->getEncryptedPassword($this->password, $this->fullUserData->password_type))) {
            // Great! The password hash matches. Return user identity (email) to be
            // saved in session for later use.
            return new Result(Result::SUCCESS, $this->fullUserData->mail, ['Authenticated successfully.']);
        }

        // If password check didn't pass return 'Invalid Credential' failure status.
        return new Result(Result::FAILURE_CREDENTIAL_INVALID, $this->fullUserData->mail, ['Invalid credentials.']);
    }

    private function matchPasswordUsers(HydratingResultSet $resultSet)
    {
        $resultSetNew = new HydratingResultSet(new ReflectionHydrator(), new CurrentUser);
        $buffer = array();
        foreach ($resultSet->toArray() as $item) {
            if (hash_equals($item['password'], $this->getEncryptedPassword($this->password, $item['password_type']))) {
                // the given password doesn't match. we remove it from the resultset
                $buffer[] = $item;
            }
        }
        $resultSetNew->initialize($buffer);

        return $resultSetNew;
    }

    /**
     * @param string $password
     * @param int    $passwordType
     *
     * @return string
     */
    private function getEncryptedPassword($password, $passwordType)
    {
        return PasswordEncrypt::get($password, $passwordType);
    }

    /**
     * @return CurrentUser
     */
    public function getFullUserData()
    {
        return $this->fullUserData;
    }

    /**
     * @return AuthenticationRepositoryInterface
     */
    public function getAuthenticationRepository()
    {
        return $this->repository_authentication;
    }

}
