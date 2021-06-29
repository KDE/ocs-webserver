<?php
/** @noinspection PhpUnused */
/** @noinspection PhpUndefinedFieldInspection */

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

namespace Application\Controller;

use Application\Form\LoginForm;
use Application\Form\RegisterForm;
use Application\Form\ResendConfirmMailForm;
use Application\Model\Entity\CurrentUser;
use Application\Model\Repository\AuthenticationRepository;
use Application\Model\Service\ActivityLogService;
use Application\Model\Service\AuthManager;
use Application\Model\Service\Entity\Auth\Result;
use Application\Model\Service\Exceptions\AlreadyLoggedInException;
use Application\Model\Service\Exceptions\NotLoggedInException;
use Application\Model\Service\InfoService;
use Application\Model\Service\Interfaces\AuthManagerInterface;
use Application\Model\Service\Interfaces\ReviewProfileDataServiceInterface;
use Application\Model\Service\LoginHistoryService;
use Application\Model\Service\Ocs\Forum;
use Application\Model\Service\Ocs\Gitlab;
use Application\Model\Service\Ocs\Ldap;
use Application\Model\Service\Ocs\OAuth;
use Application\Model\Service\RegisterManager;
use Application\Model\Service\ReviewProfileDataService;
use Application\Model\Service\UrlEncrypt;
use Exception;
use Laminas\InputFilter\InputFilter;
use Laminas\Log\Logger;
use Laminas\Session\Container;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

/**
 * Class AuthController
 * This controller is responsible for letting the user to log in and log out.*
 *
 * @package Application\Controller
 */
class AuthController extends BaseController
{
    /**
     * Auth manager.
     *
     * @var AuthManager
     */
    private $auth_manager;

    /**
     * @var AuthenticationRepository
     */
    private $authentication_repository;

    /** @var RegisterManager */
    private $register_manager;

    /**
     * @var ReviewProfileDataService
     */
    private $reviewProfileService;
    /**
     * @var InfoService
     */
    private $info_service;

    /**
     * Constructor.
     *
     * @param AuthenticationRepository          $authentication_repository
     * @param AuthManagerInterface              $auth_manager
     * @param RegisterManager                   $register_manager
     * @param ReviewProfileDataServiceInterface $review_profile_data_service
     * @param InfoService                       $info_service
     */
    public function __construct(
        AuthenticationRepository $authentication_repository,
        AuthManagerInterface $auth_manager,
        RegisterManager $register_manager,
        ReviewProfileDataServiceInterface $review_profile_data_service,
        InfoService $info_service
    ) {
        parent::__construct();
        $this->authentication_repository = $authentication_repository;
        $this->auth_manager = $auth_manager;
        $this->register_manager = $register_manager;
        $this->reviewProfileService = $review_profile_data_service;
        $this->info_service = $info_service;
    }

    /**
     * Authenticates user given username|email address and password credentials.
     *
     * @throws Exception
     */
    public function loginAction()
    {
        $this->layout()->setTemplate('layout/flat-ui');
        $this->layout()->setVariable('noheader', true);


        // Retrieve the redirect URL (if passed). We will redirect the user to this
        // URL after successful login.
        $redirectUrl = (string)$this->params()->fromRoute('redirect', '');
        $redirectUrl = UrlEncrypt::sanitizeUrlParam($redirectUrl);
        if (strlen($redirectUrl) > 2048) {
            throw new Exception("Too long redirectUrl argument passed");
        }

        // Create login form
        $form = new LoginForm();
        $form->get('redirect_url')->setValue($redirectUrl);

        // Store login status.
        $isLoginError = false;

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {

            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Validate form
            if ($form->isValid()) {

                // Get filtered and validated data
                $data = $form->getData();

                try {
                    // Perform login attempt.
                    $result = $this->auth_manager->login($data['email'], $data['password'], $data['remember_me']);
                } catch (AlreadyLoggedInException $e) {
                    $this->flashMessenger()->addErrorMessage('Already logged in.');
                    $filter = new UrlEncrypt();
                    $redirect = $filter->decryptFromUrl($redirectUrl);

                    return $this->redirect()->toUrl($redirect ? $redirect : '/');
                }

                $isLoginError = true;

                // Check result.
                if ($result->getCode() == Result::SUCCESS) {

                    $identity = $this->auth_manager->getCurrentUser();

                    LoginHistoryService::log($identity->member_id);

                    if ($identity->hasHivePassword()) {
                        //If the user is an old hive user, we have to update the password
                        $result = $this->authentication_repository->changePasswordType($identity, $data['password']);

                        if ($result == true) {
                            //Update Auth-Services
                            try {
                                $id_server = $this->getEvent()->getApplication()->getServiceManager()->get(
                                    OAuth::class
                                );
                                $id_server->updatePasswordForUser($identity->member_id);
                                $messages = $id_server->getMessages();
                                if (false == empty($messages)) {
                                    $GLOBALS['ocs_log']->info(json_encode($messages));
                                }
                            } catch (Exception $e) {
                                $GLOBALS['ocs_log']->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                            }
                            try {
                                $ldap_server = $this->getEvent()->getApplication()->getServiceManager()->get(
                                    Ldap::class
                                );
                                $ldap_server->updatePassword($identity->member_id, $data['password']);
                                $messages = $ldap_server->getMessages();
                                if (false == empty($messages)) {
                                    $GLOBALS['ocs_log']->info(json_encode($messages));
                                }
                            } catch (Exception $e) {
                                $GLOBALS['ocs_log']->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                            }
                        }
                    }

                    // Get redirect URL.
                    $redirectUrl = $this->params()->fromPost('redirect_url', '');
                    $filter = new UrlEncrypt();
                    $sanitizedRedirectUrl = UrlEncrypt::sanitizeUrlParam($redirectUrl);
                    $redirect = empty($sanitizedRedirectUrl) ? '' : $filter->decryptFromUrl($sanitizedRedirectUrl);

                    //TODO: add a whitelist for own hosts
                    /*
                    if (!empty($redirect)) {
                        // The below check is to prevent possible redirect attack
                        // (if someone tries to redirect user to another domain).
                        $uri = new Uri($redirect);
                        if (!$uri->isValid() || $uri->getHost() != null) {
                            throw new Exception('Incorrect redirect URL: ' . $redirect);
                        }
                    }
                    */

                    //user has to correct his data?
                    $modelReviewProfile = $this->reviewProfileService;
                    if (false === $modelReviewProfile->hasValidProfile($identity)) {
                        $GLOBALS['ocs_log']->info(
                            __METHOD__ . PHP_EOL . ' - User has to change user data!' . PHP_EOL . ' - error code: ' . print_r(
                                $modelReviewProfile->getErrorCode(), true
                            )
                        );

                        return $this->redirect()->toRoute(
                            'application_register', array(
                                                      'action'   => 'change',
                                                      'e'        => $modelReviewProfile->getErrorCode(),
                                                      'redirect' => $redirectUrl,
                                                  )
                        );
                    }

                    // If redirect URL is provided, redirect the user to that URL;
                    // otherwise redirect to Home page.
                    if (empty($redirect)) {
                        $host = $this->ocsConfig->settings->client->default->baseurl;
                        $redirect = $host . $this->url()->fromRoute('application_start');
                    }

//                    return $this->redirect()->toUrl($redirect);
                    $domains = $this->info_service->getActiveStoresForCrossDomainLogin();
                    $view_model = new ViewModel(array('redirect' => $redirect, 'domains' => $domains));

                    // Optionally specify a template; if we don't, by default it will be
                    // auto-determined based on the module name, controller name and this action.
                    // In this example, the template would resolve to "application/auth/login",
                    // and thus the file "application/auth/login.phtml"; the following overrides
                    // that to set the template to "redirectme", and thus the file "redirectme.phtml"
                    // (note the path should start on the module config value "template_path_stack").
                    $view_model->setTemplate('application/auth/redirectme');
                    $view_model->setTerminal(true);
                    $this->layout()->setTemplate('layout/empty');

                    return $view_model;
                }

                if ($result->getCode() == Result::FAILURE_MAIL_NOT_VALIDATED) {
                    /** @var Container $namespace */
                    $namespace = $GLOBALS['ocs_session'];
                    /** @var string $identity */
                    $identity = $result->getIdentity();
                    $namespace->mail_verify_member_id = $identity;

                    return $this->redirect()->toRoute('application_register', array('action' => 'resend'));
                }

            } else {
                $isLoginError = true;
            }

            // at this point something went wrong. let us log some information to clarify what's going on
            /** @var Logger $log */
            $log = $this->getEvent()->getApplication()->getServiceManager()->get('Ocs_Log');
            $log->info(
                __METHOD__ . ' :: login attempt failure -> identity = ' . json_encode($form->get('email')->getValue())
            );
            $log->info(__METHOD__ . ' :: login attempt failure -> form results = ' . json_encode($form->getMessages()));
            if (isset($result)) {
                $log->info(
                    __METHOD__ . ' :: login attempt failure -> db results = ' . json_encode($result->getMessages())
                );
            }
            $log->info(__METHOD__ . ' :: login attempt failure -> server info = ' . json_encode($_SERVER));
        }

        return new ViewModel(
            [
                'form'         => $form,
                'isLoginError' => $isLoginError,
                'redirectUrl'  => $redirectUrl,
            ]
        );
    }

    /**
     * The "logout" action performs logout operation.
     */
    public function logoutAction()
    {
        try {
            $this->auth_manager->logout();
        } catch (NotLoggedInException $e) {
            $this->flashMessenger()->addInfoMessage("You're not logged in.");

            return $this->redirect()->toRoute('application_home');
        }

        // Get redirect URL.
//        $redirectUrl = $this->params()->fromRoute('redirect', '');
//        $filter = new UrlEncrypt();
//        $redirect = $filter->decryptFromUrl($redirectUrl);

        //TODO: add a whitelist for own hosts
        // if (!empty($redirect)) {
        //     // The below check is to prevent possible redirect attack
        //     // (if someone tries to redirect user to another domain).
        //     $uri = new Uri($redirect);
        //     if (!$uri->isValid() || $uri->getHost() != null) {
        //         throw new Exception('Incorrect redirect URL: ' . $redirect);
        //     }
        // }

        // If redirect URL is provided, redirect the user to that URL;
        // otherwise redirect to Home page.
//        if (empty($redirect)) {
        $redirect = $this->url()->fromRoute('application_home');
//        }

        $domains = $this->info_service->getActiveStoresForCrossDomainLogin();

        $view_model = new ViewModel(
            [
                'domains'  => $domains,
                'redirect' => $redirect,
            ]
        );
        $view_model->setTerminal(true);

        return $view_model;
    }

    public function registerAction()
    {
        $this->layout()->setTemplate('layout/flat-ui');

        // Create register form
        $form = new RegisterForm();

        // Store login status.
        $isRegisterError = false;

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {

            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Validate form
            if ($form->isValid()) {

                // Get filtered and validated data
                $data = $form->getData();

                // Perform register attempt.
                $stored_user_data = $this->register_manager->register(
                    array(
                        'username' => $data['username'],
                        'password' => $data['password'],
                        'mail'     => $data['mail'],
                    )
                );

                // Check result.
                if ($stored_user_data) {
                    ActivityLogService::logActivity(
                        $stored_user_data['main_project_id'], null, $stored_user_data['member_id'],
                        ActivityLogService::MEMBER_JOINED, array()
                    );

                    LoginHistoryService::log($stored_user_data['member_id']);

                    $host = $this->currentHost()->get();
                    $this->flashMessenger()->addSuccessMessage(
                        "Thank you for registering at {$host}. Please check your email account and confirm your email address."
                    );

                    return $this->redirect()->toRoute('application_explore');
                }
            }
        }

        $this->layout()->setVariable('noheader', true);

        return new ViewModel(
            [
                'form'            => $form,
                'isRegisterError' => $isRegisterError,
            ]
        );
    }

    public function validateAction()
    {
        if (!$this->getRequest()->isPost()) {
            return new JsonModel(array('status' => 'error'));
        }

        $formRegister = new RegisterForm();

        $name = $this->params()->fromPost('name');
        $value = $this->params()->fromPost('value');

        $formRegister->setData(array($name => $value));

        $result = $formRegister->setValidationGroup([$name])->isValid();

        return new JsonModel(array('status' => $result, $name => $formRegister->get($name)->getMessages()));
    }

    public function confirmAction()
    {
        $filterInput = new InputFilter();
        $filterInput->add(
            [
                'name'       => 'vid',
                'required'   => true,
                'filters'    => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripNewlines'],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 2048,
                        ],
                    ],
                ],
            ]
        );
        $filterInput->setData($this->params()->fromRoute());
        $_vId = $filterInput->getValue('vid');

        if (!$_vId) {
            return $this->redirect()->toRoute('application_explore');
        }

        try {
            $resultSet = $this->register_manager->finishConfirmedRegistration($_vId);
        } catch (Exception $e) {
            $this->flashMessenger()->addErrorMessage('User has already been activated or token is invalid.');

            return $this->redirect()->toRoute('application_explore');
        }
        $registeredMember = $resultSet->current();
        $this->auth_manager->initSessionForMember($registeredMember->mail);
        /** @var Logger $logger */
        $logger = $GLOBALS['ocs_log'];

        try {
            /** @var OAuth $oauth */
            $oauth = $this->getEvent()->getApplication()->getServiceManager()->get(OAuth::class);
            $oauth->createUserFromArray($registeredMember->getArrayCopy());
            $logger->info(__METHOD__ . ' - oauth : ' . var_export($oauth->getMessages(), true));
        } catch (Exception $e) {
            $logger->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            /** @var Ldap $ldap */
            $ldap = $this->getEvent()->getApplication()->getServiceManager()->get(Ldap::class);
            $ldap->addUserFromArray($registeredMember->getArrayCopy());
            $logger->info(__METHOD__ . ' - ldap : ' . var_export($ldap->getMessages(), true));
        } catch (Exception $e) {
            $logger->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            /** @var Gitlab $openCode */
            $openCode = $this->getEvent()->getApplication()->getServiceManager()->get(Gitlab::class);
            $openCode->createUserFromArray($registeredMember->getArrayCopy());
            $logger->info(__METHOD__ . ' - opencode : ' . var_export($openCode->getMessages(), true));
        } catch (Exception $e) {
            $logger->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            /** @var Forum $forum */
            $forum = $this->getEvent()->getApplication()->getServiceManager()->get(Forum::class);
            $forum->createUserFromArray($registeredMember->getArrayCopy());
            $logger->info(__METHOD__ . ' - forum : ' . var_export($forum->getMessages(), true));
        } catch (Exception $e) {
            $logger->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        ActivityLogService::logActivity(
            $registeredMember->member_id, null, $registeredMember->member_id,
            ActivityLogService::MEMBER_EMAIL_CONFIRMED, array()
        );
        LoginHistoryService::log($registeredMember->member_id);
        $host = $this->ocsConfig->settings->client->default->baseurl;

        return $this->redirect()->toUrl($host . $this->url()->fromRoute('application_start'));
    }

    /**
     * Displays the "Not Authorized" page.
     */
    public function notAuthorizedAction()
    {
        $requested_uri = UrlEncrypt::sanitizeUrlParam($this->params()->fromQuery('redirect', null));
        $this->layout()->setTemplate('layout/flat-ui');
        $this->layout()->setVariable('redirect', $requested_uri);

        $this->getResponse()->setStatusCode(403);

        return new ViewModel([
            'redirect' => $requested_uri
                             ]);
    }

    public function resendAction()
    {
        $this->layout()->setTemplate('layout/flat-ui');

        /** @var Container $session */
        $session = $GLOBALS['ocs_session'];
        $member_id = $session->mail_verify_member_id;

        if (empty($member_id)) {
            throw new Exception('missing member id parameter');
        }

        if ($this->getRequest()->isGet()) {
            return new ViewModel(
                [
                    'form' => new ResendConfirmMailForm(),
                ]
            );
        }

        if ($this->getRequest()->isPost()) {
            $form = new ResendConfirmMailForm();

            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            if ($form->isValid()) {
                $result = $this->register_manager->resendConfirmation($member_id);

                $GLOBALS['ocs_log']->info(
                    __METHOD__ . ' - resend of verification mail requested by user id: ' . $member_id
                );

                $session->mail_verify_member_id = null;

                $this->flashMessenger()->addInfoMessage(
                    'We have send a new verification mail to the stored mail address.'
                );

                return $this->redirect()->toRoute('application_home');
            }
        }

        return new ViewModel(
            [
                'form' => new ResendConfirmMailForm(),
            ]
        );
    }

    public function changeAction()
    {
        $this->layout()->setTemplate('layout/flat-ui');

        $form = new RegisterForm();

        if ($this->getRequest()->isGet()) {
            return new ViewModel(
                array(
                    'form'      => $form,
                    'errorCode' => (int)$this->params()->fromRoute("e"),
                    'redirect'  => UrlEncrypt::sanitizeUrlParam($this->params()->fromRoute('redirect')),
                )
            );
        }

        $form->setData($_POST);
        if ($this->params()->fromRoute("e") < 20) {
            $form->setValidationGroup(['username', 'csrf']);
        }
        if ($this->params()->fromRoute("e") >= 20) {
            $form->setValidationGroup(['mail', 'csrf']);
        }

        if (false === $form->isValid()) {
            return new ViewModel(
                array(
                    'form'      => $form,
                    'errorCode' => (int)$this->params()->fromRoute("e"),
                    'redirect'  => UrlEncrypt::sanitizeUrlParam($this->params()->fromRoute('redirect')),
                )
            );
        }

        $values = $form->getData();
        $identity = $this->auth_manager->getCurrentUser();

        if (isset($values['username']) and ($identity->username != $values['username'])) {
            $data = array(
                'member_id'    => $identity->member_id,
                'username'     => $values['username'],
                'username_old' => $identity->username,
            );
            $result = $this->authentication_repository->insertOrUpdate($data);
            $identity = $this->auth_manager->getCurrentUser(false);
            ActivityLogService::logActivity(
                $identity->member_id, null, $identity->member_id, ActivityLogService::MEMBER_UPDATED,
                array('description' => 'user changed his username')
            );
            $this->flashMessenger()->addSuccessMessage("Thank you for updating your profile.");
        }
        if (isset($values['mail'])) {
            $identity = $this->auth_manager->getCurrentUser(false);
            $mail = $this->register_manager->getMemberService()->getMemberEmailService()->saveEmailAsPrimary(
                $identity->member_id, $values['mail']
            );
            $this->auth_manager->initSessionForMember($values['mail']);
            $user = array(
                'username' => $identity->username,
                'mail'     => $values['mail'],
            );
            $this->register_manager->getMemberService()->getMemberEmailService()->sendConfirmationMail(
                $user, $mail['email_verification_value']
            );
            $this->flashMessenger()->addSuccessMessage(
                "Thank you for updating your profile. Please check your email account and confirm your email address."
            );
        }

        /** @var Logger $logger */
        $logger = $GLOBALS['ocs_log'];

        try {
            $id_server = $this->getEvent()->getApplication()->getServiceManager()->get(OAuth::class);
            $id_server->updateUserFromArray($identity->getArrayCopy());
            $logger->info(__METHOD__ . ' - oauth : ' . var_export($id_server->getMessages(), true));
        } catch (Exception $e) {
            $logger->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $ldap_server = $this->getEvent()->getApplication()->getServiceManager()->get(Ldap::class);
            $ldap_server->updateUserFromArray($identity->getArrayCopy());
            $logger->info(__METHOD__ . ' - ldap : ' . var_export($ldap_server->getMessages(), true));
        } catch (Exception $e) {
            $logger->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $openCode = $this->getEvent()->getApplication()->getServiceManager()->get(Gitlab::class);
            $openCode->updateUserFromArray($identity->getArrayCopy(), $data['username_old']);
            $logger->info(__METHOD__ . ' - opencode : ' . var_export($openCode->getMessages(), true));
        } catch (Exception $e) {
            $logger->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $modelForum = $this->getEvent()->getApplication()->getServiceManager()->get(Forum::class);
            $modelForum->updateUserFromArray($identity->getArrayCopy(), $data['username_old']);
            $logger->info(__METHOD__ . ' - forum : ' . var_export($modelForum->getMessages(), true));
        } catch (Exception $e) {
            $logger->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        return $this->redirect()->toRoute('application_home');
    }

}
