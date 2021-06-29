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

namespace Application\Controller;

use Application\Form\SettingsEmailForm;
use Application\Form\SettingsGithubForm;
use Application\Form\SettingsHomepageForm;
use Application\Form\SettingsNewsletterForm;
use Application\Form\SettingsPasswordForm;
use Application\Form\SettingsPaymentForm;
use Application\Form\SettingsPictureBgForm;
use Application\Form\SettingsPictureForm;
use Application\Form\SettingsProfileForm;
use Application\Form\SettingsSocialAccountsForm;
use Application\Model\Interfaces\ImageInterface;
use Application\Model\Interfaces\MemberEmailInterface;
use Application\Model\Interfaces\MemberInterface;
use Application\Model\Interfaces\MemberTokenInterface;
use Application\Model\Interfaces\PaypalValidStatusInterface;
use Application\Model\Interfaces\ProjectInterface;
use Application\Model\Repository\MemberRepository;
use Application\Model\Service\ActivityLogService;
use Application\Model\Service\Interfaces\EmailBuilderInterface;
use Application\Model\Service\Interfaces\MemberEmailServiceInterface;
use Application\Model\Service\Interfaces\TagServiceInterface;
use Application\Model\Service\Interfaces\WebsiteOwnerServiceInterface;
use Application\Model\Service\MemberEmailService;
use Application\Model\Service\Ocs\Gitlab;
use Application\Model\Service\Ocs\Ldap;
use Application\Model\Service\Ocs\OAuth;
use ArrayObject;
use Exception;
use JobQueue\Jobs\EmailJob;
use JobQueue\Jobs\JobBuilder;
use Laminas\Filter\File\RenameUpload;
use Laminas\Filter\FilterChain;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Library\Ppload\PploadApi;
use Library\Tools\PasswordEncrypt;
use Library\Tools\Uuid;

/**
 * Class SettingsController
 *
 * @package Application\Controller
 */
class SettingsController extends BaseController
{

    protected $_mainproject;
    protected $_auth;
    protected $_memberId;
    protected $_memberTable;
    protected $_memberSettings;
    protected $_projectTable;
    private $websiteOwnerService;
    private $memberRepository;
    private $projectRepository;
    private $paypalValidStatusRepository;
    private $memberEmailService;
    private $memberEmailRepository;
    private $memberTokenRepository;
    private $oAuthService;
    private $lDapService;
    private $gitlabService;
    private $imageRepository;
    private $tagService;
    private $mailer;

    public function __construct(
        WebsiteOwnerServiceInterface $websiteOwnerService,
        MemberInterface $memberRepository,
        ProjectInterface $projectRepository,
        PaypalValidStatusInterface $paypalValidStatusRepository,
        MemberEmailServiceInterface $memberEmailService,
        MemberEmailInterface $memberEmailRepository,
        MemberTokenInterface $memberTokenRepository,
        OAuth $oAuthService,
        Ldap $lDapService,
        Gitlab $gitlabService,
        ImageInterface $imageRepository,
        TagServiceInterface $tagService,
        EmailBuilderInterface $mailer
    ) {

        parent::__construct();
        $this->websiteOwnerService = $websiteOwnerService;
        $this->memberRepository = $memberRepository;
        $this->projectRepository = $projectRepository;
        $this->paypalValidStatusRepository = $paypalValidStatusRepository;
        $this->memberEmailService = $memberEmailService;
        $this->_projectTable = $projectRepository;
        $this->_memberTable = $memberRepository;
        $this->memberEmailRepository = $memberEmailRepository;
        $this->memberTokenRepository = $memberTokenRepository;
        $this->oAuthService = $oAuthService;
        $this->lDapService = $lDapService;
        $this->gitlabService = $gitlabService;
        $this->imageRepository = $imageRepository;
        $this->tagService = $tagService;
        $this->mailer = $mailer;

        $this->_auth = $this->ocsUser;
        $this->_memberSettings = $this->ocsUser;
        $this->_memberId = $this->ocsUser->member_id;

        // init default main project
        $main_project_id = $this->ocsUser->main_project_id;
        $mainproject_rowset = $this->projectRepository->findById($main_project_id);
        $this->_mainproject = $mainproject_rowset;
    }

    public function indexAction()
    {
        $viewModel = $this->initCommonActionViewModel();
        $viewModel = $this->prepareIndex($viewModel);

        return $viewModel;
    }

    private function initCommonActionViewModel()
    {
        $viewModel = $this->initViewModel();
        $this->_auth = $this->ocsUser;
        $this->_memberSettings = $this->ocsUser;
        $this->_memberId = $this->ocsUser->member_id;
        $viewModel->setVariable('member', $this->ocsUser);

        // init default main project
        $main_project_id = $this->ocsUser->main_project_id;
        $mainproject_rowset = $this->projectRepository->findById($main_project_id);
        $viewModel->setVariable('mainproject', $mainproject_rowset);
        $this->_mainproject = $mainproject_rowset;

        if ($this->ocsUser->paypal_valid_status) {
            $paypalValidStatus = $this->paypalValidStatusRepository->findById($this->ocsUser->paypal_valid_status);
            $viewModel->setVariable('paypal_valid_status', $paypalValidStatus);
        } else {
            $viewModel->setVariable('paypal_valid_status', null);
        }

        $viewModel->setVariable('isAdmin', $this->isAdmin());

        $matches = $this->getEvent()->getRouteMatch();
        $action = $matches->getParam('action', '');
        if ($action == 'index') {
            $title = 'settings';
        } else {
            $title = $action;
        }
        $viewModel->setVariable('headTitle', $title . ' - ' . $this->getHeadTitle());

        /**
         * //TODO or set in view
         * $action = $this->getRequest()->getActionName();
         * $title = '';
         * if ($action == 'index') {
         * $title = 'settings';
         * } else {
         * $title = $action;
         * }
         * $this->view->headTitle($title . ' - ' . $this->getHeadTitle(), 'SET');
         **/
        return $viewModel;
    }

    private function initViewModel()
    {
        $this->layout()->setTemplate('layout/flat-ui');
        $viewModel = new ViewModel();
        $viewModel->setVariable('isAdmin', $this->isAdmin());

        return $viewModel;
    }

    private function prepareIndex(ViewModel $viewModel)
    {
        $memberSettings = $this->ocsUser;
        $listEmails = $this->memberEmailService->fetchAllMailAddresses($memberSettings->member_id);
        $viewModel->setVariable('listEmails', $listEmails);

        // email form
        $emailform = new SettingsEmailForm();
        $viewModel->setVariable('emailform', $emailform);

        // payment form
        $paymentform = new SettingsPaymentForm();
        $paymentform->setData($memberSettings->getArrayCopy());
        $viewModel->setVariable('paymentform', $paymentform);

        // github form
        $githubform = new SettingsGithubForm();
        $tokenData = $this->memberTokenRepository->fetchAllRows(
            'token_member_id = ' . $this->ocsUser->member_id . ' and token_deleted is null and token_provider_name = "github_personal"'
        )->current();
        $tokenValue = ($tokenData && $tokenData->token_value) ? $tokenData->token_value : null;
        $data = ['link_github' => $this->ocsUser->link_github, 'token_github' => $tokenValue];
        $githubform->setData($data);
        $viewModel->setVariable('github', $githubform);

        // password form
        $passwordform = new SettingsPasswordForm($this->memberRepository);
        $viewModel->setVariable('passwordform', $passwordform);

        //newletter form
        $newsletterform = new SettingsNewsletterForm();
        $newsletterform->setData(['newsletter', $this->ocsUser->newsletter]);
        $viewModel->setVariable('newsletterform', $newsletterform);

        return $viewModel;
    }

    public function profileAction()
    {
        $viewModel = $this->initCommonActionViewModel();
        $viewModel = $this->prepareProfile($viewModel);

        return $viewModel;
    }

    private function prepareProfile(ViewModel $viewModel)
    {
        $profileform = new SettingsProfileForm();
        $profileform->setData(array_merge($this->ocsUser->getArrayCopy(), ['aboutme' => $this->_mainproject->description]));
        $viewModel->setVariable('profileform', $profileform);


        $accountsForm = new SettingsSocialAccountsForm();
        $accountsForm->setData($this->ocsUser->getArrayCopy());
        $viewModel->setVariable('accounts', $accountsForm);

        $homepageform = new SettingsHomepageForm();
        $authCode = $this->websiteOwnerService->generateAuthCode($this->ocsUser->link_website);
        $link_website = stripslashes($this->ocsUser->link_website);

        if ($authCode && $authCode != '') {
            $htmlVerifier = '&lt;meta name="ocs-site-verification" content="?" /&gt;';
            $html_verifier = str_replace('?', $authCode, $htmlVerifier);

            $homepageform->setData(['link_website' => $link_website, 'html_verifier' => $html_verifier]);
            // for description decorator
            $viewModel->setVariable('validated', $this->ocsUser->validated);
        }
        $viewModel->setVariable('homepageform', $homepageform);


        $pictureform = new SettingsPictureForm();
        $pictureform->setData($this->ocsUser->getArrayCopy());
        $viewModel->setVariable('pictureform', $pictureform);

        $pictureformbg = new SettingsPictureBgForm();
        $pictureformbg->setData($this->ocsUser->getArrayCopy());
        $viewModel->setVariable('pictureformbg', $pictureformbg);

        //user computer info
        $gidsstring = $this->ocsConfig->settings->client->default->tag_group_osuser;
        $gids = explode(",", $gidsstring);
        $viewModel->setVariable('gids', $gids);
        $viewModel->setVariable('data', $this->tagService->getTagGroupsOSUser());
        $viewModel->setVariable('data2', $this->tagService->getTagsOSUser($this->ocsUser->member_id));

        return $viewModel;
    }

    public function paymentAction()
    {
        $paymentform = new SettingsPaymentForm();

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setTemplate('/application/settings/partials/payment');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $this->params()->fromPost();

            $paymentform->setData($data);

            // Validate form
            if ($paymentform->isValid()) {
                $values = $paymentform->getData();

                if ($this->ocsUser->paypal_mail != $values['paypal_mail']) {
                    $this->memberRepository->update(
                        array('paypal_valid_status' => null), 'member_id = ' . $this->ocsUser->member_id
                    );

                    //Log if paypal changes
                    $desc = 'Paypal-Address changed from ';
                    if (isset($this->ocsUser->paypal_mail)) {
                        $desc .= $this->ocsUser->paypal_mail;
                    }
                    $desc .= ' to ' . $values['paypal_mail'];

                    ActivityLogService::logActivity(
                        $this->ocsUser->member_id, null, $this->ocsUser->member_id, ActivityLogService::MEMBER_PAYPAL_CHANGED, array(
                        'title'       => '',
                        'description' => $desc,
                    )
                    );

                }

                $this->ocsUser->paypal_mail = $values['paypal_mail'];

                $this->memberRepository->update(
                    [
                        'paypal_mail' => $values['paypal_mail'],
                        'member_id'   => $this->ocsUser->member_id,
                    ]
                );

                $paymentform->setData($this->ocsUser->getArrayCopy());
                $viewModel->setVariable('paymentform', $paymentform);
                $viewModel->setVariable('save', 1);

                return $viewModel;
            } else {
                $viewModel->setVariable('error', 1);
            }
        }
        $paymentform->setData($this->ocsUser->getArrayCopy());
        $viewModel->setVariable('paymentform', $paymentform);

        return $viewModel;

    }

    public function addemailAction()
    {
        $form = new SettingsEmailForm();

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setTemplate('/application/settings/partials/email.phtml');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $this->params()->fromPost();
            $form->setData($data);
            // Validate form
            if ($form->isValid()) {
                $arrayobject = $this->saveEmail($form->getInputFilter());
                $viewModel->setVariable('messages', array('user_email' => array('success' => 'Your email was saved. Please check your email account for verification email.')));
                $viewModel->setVariable('emailform', new SettingsEmailForm());
            } else {
                $viewModel->setVariable('messages', $form->getMessages());
                $viewModel->setVariable('emailform', $form);
                $listEmails = $this->memberEmailService->fetchAllMailAddresses($this->ocsUser->member_id);
                $viewModel->setVariable('listEmails', $listEmails);

                return $viewModel;
            }
        }

        $this->sendConfirmationMail($arrayobject);

        $listEmails = $this->memberEmailService->fetchAllMailAddresses($this->ocsUser->member_id);
        $viewModel->setVariable('listEmails', $listEmails);

        return $viewModel;
    }

    /**
     * @param $filterInput
     *
     * @return ArrayObject|null
     */
    protected function saveEmail($filterInput)
    {
        $data = array();
        $data['email_member_id'] = $this->ocsUser->member_id;
        $data['email_address'] = $filterInput->getValue('user_email');
        $data['email_hash'] = md5($filterInput->getValue('user_email'));
        $data['email_verification_value'] = MemberEmailService::getVerificationValue(
            $this->ocsUser->username, $filterInput->getValue('user_email')
        );
        $email_id = $this->memberEmailRepository->insert($data);

        return $this->memberEmailRepository->fetchById($email_id);
    }

    /**
     * @param array $data
     */
    protected function sendConfirmationMail($data)
    {
        $defaultFrom = $this->configHelp('ocs_config')->settings->mail->defaults->fromMail;

        $confirmMail = $this->mailer;
        $mail = $confirmMail->withTemplate('tpl_verify_email')->setTemplateVar('servername', $this->getServerName())
                            ->setTemplateVar('username', $this->ocsUser->username)
                            ->setTemplateVar('email_address', $data['email_address'])->setTemplateVar(
                'verificationlinktext', '<a href="' . $this->getRequest()->getUri()
                                                           ->getScheme() . '://' . $this->getServerName() . '/settings/verification/v/' . $data['email_verification_value'] . '">Click here to verify your email address</a>'
            )->setTemplateVar(
                'verificationlink', '<a href="' . $this->getRequest()->getUri()
                                                       ->getScheme() . '://' . $this->getServerName() . '/settings/verification/v/' . $data['email_verification_value'] . '">' . $this->getRequest()
                                                                                                                                                                                      ->getUri()
                                                                                                                                                                                      ->getScheme() . '://' . $this->getServerName() . '/settings/verification/v/' . $data['email_verification_value'] . '</a>'
            )->setTemplateVar(
                'verificationurl', '' . $this->getRequest()->getUri()
                                             ->getScheme() . '://' . $this->getServerName() . '/settings/verification/v/' . $data['email_verification_value']
            )->setReceiverMail($data['email_address'])->setFromMail($defaultFrom)->build();

        $mail_config = $this->configHelp('ocs_config')->settings->mail;
        //@formatter:off
        JobBuilder::getJobBuilder()
                  ->withJobClass(EmailJob::class)
                  ->withParam('mail', serialize($mail))
                  ->withParam('withFileTransport', $mail_config['transport']['withFileTransport'])
                  ->withParam('withSmtpTransport', $mail_config['transport']['withSmtpTransport'])
                  ->withParam('config', serialize($mail_config))
                  ->build();
        //@formatter:on
    }

    /**
     * @return string|null
     */
    protected function getServerName()
    {
        return $this->getRequest()->getUri()->getHost();
    }

    public function githubAction()
    {
        $form = new SettingsGithubForm();

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setTemplate('/application/settings/partials/github.phtml');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $this->params()->fromPost();
            $form->setData($data);
            // Validate form
            if ($form->isValid()) {

                $data = $form->getData();

                if ($this->ocsUser->link_github != $data['link_github']) {

                    $this->memberRepository->update(
                        [
                            'member_id'   => $this->ocsUser->member_id,
                            'link_github' => $data['link_github'],
                        ]
                    );

                }

                $tokenData = $this->memberTokenRepository->fetchAllRows(
                    'token_member_id = ' . $this->ocsUser->member_id . ' and token_deleted is null and token_provider_name = "github_personal"'
                )->current();

                $dataTmp = [
                    'token_member_id'         => $this->ocsUser->member_id,
                    'token_provider_name'     => 'github_personal',
                    'token_value'             => $data['token_github'],
                    'token_provider_username' => $data['link_github'],
                ];
                if ($tokenData != null) {
                    $dataTmp['token_id'] = $tokenData->token_id;
                }
                $this->memberTokenRepository->insertOrUpdate($dataTmp);

                $viewModel->setVariable('save', 1);

            } else {
                $viewModel->setVariable('error', 1);
            }
        }
        $viewModel->setVariable('github', $form);

        return $viewModel;
    }

    public function newsletterAction()
    {
        $form = new SettingsNewsletterForm();

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setTemplate('/application/settings/partials/newsletter.phtml');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $this->params()->fromPost();

            $form->setData($data);
            // Validate form
            if ($form->isValid()) {

                $data = $form->getData();

                $this->memberRepository->update(
                    [
                        'newsletter' => $data['newsletter'],
                        'member_id'  => $this->ocsUser->member_id,
                    ]
                );

                $viewModel->setVariable('save', 1);

            } else {
                $viewModel->setVariable('error', 1);
            }
        } else {
            $form->setData(['newletter' => $this->ocsUser->newsletter]);
        }
        $viewModel->setVariable('newsletterform', $form);

        return $viewModel;

    }

    public function pictureAction()
    {
        $this->_memberSettings = $this->ocsUser;
        $form = new SettingsPictureForm();

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setTemplate('/application/settings/partials/picture.phtml');

        $request = $this->getRequest();
        if ($request->isPost()) {

            // Make certain to merge the files info!
            $data = array_merge_recursive(
                $request->getPost()->toArray(), $request->getFiles()->toArray()
            );

            $form->setData($data);

            $formFilename = $form->get('profile_picture_upload')->getValue();
            $this->ocsLog->info(__METHOD__ . ' :: form input:' . print_r($formFilename, true));
            if (!is_array($formFilename) || !array_key_exists('name', $formFilename)) {
                // in case formFilename not existing.
                $viewModel->setVariable('error', 1);
                $form->setData($this->ocsUser->getArrayCopy());
                $viewModel->setVariable('pictureform', $form);

                return $viewModel;
            }
            $filename = $formFilename['name'];
            $profilePictureTitleFilename = pathinfo($filename);


            if (!isset($profilePictureTitleFilename)) {
                $form->setData($this->ocsUser->getArrayCopy());
                $viewModel->setVariable('pictureform', $form);
                $form->get('profile_picture_upload')->setMessages(array('Please select a new picture'));
                $viewModel->setVariable('pictureform', $form);

                return $viewModel;
            }

            // Validate form
            if ($form->isValid()) {

                $data = $form->getData();

                $tmpProfilePictureTitle = IMAGES_UPLOAD_PATH . '/tmp/' . Uuid::generateUUID() . '_' . $profilePictureTitleFilename['basename'];
                $filter = new RenameUpload($tmpProfilePictureTitle);
                $filter->filter($data['profile_picture_upload']);

                if (array_key_exists('profile_picture_upload', $data) && $data['profile_picture_upload'] != "") {
                    $newImageName = $this->imageRepository->saveImageOnMediaServer($tmpProfilePictureTitle);
                }

                $dataToUpdate = ['member_id' => $this->ocsUser->member_id];
                if ($data['profile_img_src'] == 'local' && isset($newImageName)) {

                    $dataToUpdate['avatar'] = $newImageName;
                    $dataToUpdate['profile_image_url'] = IMAGES_MEDIA_SERVER . '/cache/200x200-2/img/' . $newImageName;
                    $dataToUpdate['avatar_type_id'] = MemberRepository::MEMBER_AVATAR_TYPE_USERUPDATED;
                    $this->ocsUser->profile_image_url = $dataToUpdate['profile_image_url'];
                }
                $dataToUpdate['profile_img_src'] = $data['profile_img_src'];

                $this->memberRepository->update($dataToUpdate);


                try {
                    $id_server = $this->oAuthService;
                    $id_server->updateAvatarForUser($this->_memberSettings->member_id);
                    $this->ocsLog->debug(__METHOD__ . ' - oauth : ' . var_export($id_server->getMessages(), true));
                } catch (Exception $e) {
                    $this->ocsLog->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                }

                try {
                    $ldap_server = $this->lDapService;
                    $ldap_server->updateAvatar($this->_memberSettings->member_id, $this->_memberSettings->profile_image_url);
                    $this->ocsLog->debug(__METHOD__ . ' - ldap : ' . var_export($ldap_server->getMessages(), true));
                } catch (Exception $e) {
                    $this->ocsLog->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                }

                // ppload
                // Update profile information
                $this->_updatePploadProfile();

                $form->setData($this->ocsUser->getArrayCopy());
                $viewModel->setVariable('save', 1);

            } else {
                $viewModel->setVariable('error', 1);
            }
        } else {
            // init picture form
            $form->setData($this->ocsUser->getArrayCopy());
        }
        $viewModel->setVariable('pictureform', $form);

        return $viewModel;

        //ini_set('memory_limit', '128M');
    }

    protected function _updatePploadProfile()
    {
        $pploadApi = new PploadApi(
            array(
                'apiUri'   => PPLOAD_API_URI,
                'clientId' => PPLOAD_CLIENT_ID,
                'secret'   => PPLOAD_SECRET,
            )
        );

        $profileName = '';
        if ($this->_memberSettings->firstname || $this->_memberSettings->lastname) {
            $profileName = trim($this->_memberSettings->firstname . ' ' . $this->_memberSettings->lastname);
        } else {
            if ($this->_memberSettings->username) {
                $profileName = $this->_memberSettings->username;
            }
        }

        $desc = "";
        if ($this->_mainproject && !empty($this->_mainproject->description)) {
            $desc = $this->_mainproject->description;
        }

        $profileRequest = array(
            'owner_id'    => $this->_memberSettings->member_id,
            'name'        => $profileName,
            'email'       => $this->_memberSettings->mail,
            'homepage'    => $this->_memberSettings->link_website,
            'image'       => $this->_memberSettings->profile_image_url,
            'description' => $desc,
        );
        $profileResponse = $pploadApi->postProfile($profileRequest);
    }

    public function deletepicturebackgroundAction()
    {
        $this->memberRepository->update(['member_id' => $this->ocsUser->member_id, 'profile_image_url_bg' => null]);
        $viewModel = new JsonModel();
        $viewModel->setVariable('status', 'ok');

        return $viewModel;
    }

    public function savetagsAction()
    {
        /*
        $tag_id = null;
        if (!empty($_POST['tag_id'])) {
            $tag_id = $_POST['tag_id'];
        }
        */

        $tag_id = (int)$this->params()->fromPost('tag_id');
        $tag_group_id = (int)$this->params()->fromPost('tag_group_id');

        $tag_object_id = $this->ocsUser->member_id;
        $this->tagService->saveOSTagForUser($tag_id, $tag_group_id, $tag_object_id);
        $viewModel = new JsonModel();
        $viewModel->setVariable('status', 'ok');

        return $viewModel;
    }

    public function picturebackgroundAction()
    {
        $form = new SettingsPictureBgForm();

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setTemplate('/application/settings/partials/picture-bg.phtml');

        $request = $this->getRequest();
        if ($request->isPost()) {

            // Make certain to merge the files info!
            $data = array_merge_recursive(
                $request->getPost()->toArray(), $request->getFiles()->toArray()
            );

            $form->setData($data);

            $formFilename = $form->get('profile_picture_background_upload')->getValue();
            $this->ocsLog->info(__METHOD__ . ' :: form input:' . print_r($formFilename, true));
            if (!is_array($formFilename) || !array_key_exists('name', $formFilename)) {
                return $viewModel;
            }
            $filename = $formFilename['name'];
            $profilePictureTitleFilename = pathinfo($filename);


            if (!isset($profilePictureTitleFilename)) {
                $form->setData($this->ocsUser->getArrayCopy());
                $form->get('profile_picture_background_upload')->setMessages('Please select a new picture');
                $viewModel->setVariable('pictureformbg', $form);

                return $viewModel;
            }

            // Validate form
            if ($form->isValid()) {

                $data = $form->getData();

                $tmpProfilePictureTitle = IMAGES_UPLOAD_PATH . '/tmp/' . Uuid::generateUUID() . '_' . $profilePictureTitleFilename['basename'];
                $filter = new RenameUpload($tmpProfilePictureTitle);
                $filter->filter($data['profile_picture_background_upload']);

                if (array_key_exists('profile_picture_background_upload', $data) && $data['profile_picture_background_upload'] != "") {
                    $newImageName = $this->imageRepository->saveImageOnMediaServer($tmpProfilePictureTitle);
                }

                $dataToUpdate = ['member_id' => $this->ocsUser->member_id];
                $dataToUpdate['profile_image_url_bg'] = IMAGES_MEDIA_SERVER . '/cache/1920x450/img/' . $newImageName;
                $this->ocsUser->profile_image_url_bg = $dataToUpdate['profile_image_url_bg'];
                $this->memberRepository->update($dataToUpdate);

                $form->setData($this->ocsUser->getArrayCopy());
                $viewModel->setVariable('save', 1);

            } else {
                $viewModel->setVariable('error', 1);
            }
        } else {
            // init picture form
            $form->setData($this->ocsUser->getArrayCopy());
        }
        $viewModel->setVariable('pictureformbg', $form);

        return $viewModel;

        //ini_set('memory_limit', '128M');
    }

    public function homepageAction()
    {
        $form = new SettingsHomePageForm();

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setTemplate('/application/settings/partials/website.phtml');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $this->params()->fromPost();

            $form->setData($data);
            // Validate form
            if ($form->isValid()) {

                $data = $form->getData();
                $this->memberRepository->update(
                    [
                        'link_website' => $data['link_website'],
                        'member_id'    => $this->ocsUser->member_id,
                    ]
                );

                $authCode = $this->websiteOwnerService->generateAuthCode($data['link_website']);
                $htmlVerifier = '&lt;meta name="ocs-site-verification" content="?" /&gt;';
                $html_verifier = str_replace('?', $authCode, $htmlVerifier);
                $form->setData(['link_website' => $data['link_website'], 'html_verifier' => $html_verifier]);
                $viewModel->setVariable('save', 1);

            } else {
                $viewModel->setVariable('error', 1);
            }
        } else {
            $authCode = $this->websiteOwnerService->generateAuthCode($this->ocsUser->link_website);
            $link_website = stripslashes($this->ocsUser->link_website);

            if ($authCode && $authCode != '') {
                $htmlVerifier = '&lt;meta name="ocs-site-verification" content="?" /&gt;';
                $html_verifier = str_replace('?', $authCode, $htmlVerifier);
                $form->setData(['link_website' => $link_website, 'html_verifier' => $html_verifier]);
                // for description decorator
            }
        }
        $viewModel->setVariable('validated', $this->ocsUser->validated);
        $viewModel->setVariable('homepageform', $form);

        return $viewModel;
    }

    public function accountsAction()
    {
        $form = new SettingsSocialAccountsForm();

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setTemplate('/application/settings/partials/accounts.phtml');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $this->params()->fromPost();

            $form->setData($data);
            // Validate form
            if ($form->isValid()) {

                $data = $form->getData();

                $this->memberRepository->update(
                    [
                        'link_facebook' => $data['link_facebook'],
                        'link_twitter'  => $data['link_twitter'],
                        'member_id'     => $this->ocsUser->member_id,
                    ]
                );

                $viewModel->setVariable('save', 1);

            } else {
                $viewModel->setVariable('error', 1);
            }
        } else {
            $form->setData($this->ocsUser->getArrayCopy());
        }
        $viewModel->setVariable('accounts', $form);

        return $viewModel;
    }

    public function saveprofileAction()
    {
        $form = new SettingsProfileForm();

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setTemplate('/application/settings/partials/profile.phtml');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $this->params()->fromPost();

            $form->setData($data);
            // Validate form
            if ($form->isValid()) {

                $data = $form->getData();

                $this->memberRepository->update(
                    [
                        'firstname' => $data['firstname'],
                        'lastname'  => $data['lastname'],
                        'city'      => $data['city'],
                        'country'   => $data['country'],
                        'member_id' => $this->ocsUser->member_id,
                    ]
                );
                $this->projectRepository->update(
                    [
                        'project_id'  => $this->ocsUser->main_project_id,
                        'description' => $data['aboutme'],
                    ]
                );
                $viewModel->setVariable('save', 1);

            } else {
                $viewModel->setVariable('error', 1);
            }
        } else {
            $profileform = new SettingsProfileForm();
            $profileform->setData(array_merge($this->ocsUser->getArrayCopy(), ['aboutme' => $this->_mainproject->description]));
        }
        $viewModel->setVariable('profileform', $form);

        return $viewModel;
    }

    public function passwordAction()
    {
        $form = new SettingsPasswordForm($this->memberRepository);

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setTemplate('/application/settings/partials/password.phtml');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $this->params()->fromPost();
            $form->setData($data);
            // Validate form
            if ($form->isValid()) {

                $data = $form->getData();

                $dataToUpdate = [];
                //20180801 ronald: If a Hive User changes his password, we change the password type to our Default
                if ($this->ocsUser->password_type == MemberRepository::PASSWORD_TYPE_HIVE) {
                    //Save old data
                    $dataToUpdate['password_old'] = $this->ocsUser->password;
                    $dataToUpdate['password_type_old'] = MemberRepository::PASSWORD_TYPE_HIVE;
                    $dataToUpdate['password_type'] = MemberRepository::PASSWORD_TYPE_OCS;
                }
                $dataToUpdate['password'] = PasswordEncrypt::get($data['password1'], $this->ocsUser->password_type);
                $dataToUpdate['member_id'] = $this->ocsUser->member_id;
                $this->memberRepository->update($dataToUpdate);

                // Update Auth-Services
                try {
                    $id_server = $this->oAuthService;
                    $id_server->updatePasswordForUser($this->ocsUser->member_id);
                    $this->ocsLog->info(__METHOD__ . ' - oauth : ' . var_export($id_server->getMessages(), true));
                } catch (Exception $e) {
                    $this->ocsLog->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                }
                try {
                    $ldap_server = $this->lDapService;
                    $ldap_server->updatePassword($this->ocsUser->member_id, $data['password1']);
                    $this->ocsLog->info(__METHOD__ . ' - ldap : ' . var_export($ldap_server->getMessages(), true));
                } catch (Exception $e) {
                    $this->ocsLog->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                }


                $viewModel->setVariable('save', 1);

            } else {
                $viewModel->setVariable('error', 1);
            }
        }
        $viewModel->setVariable('passwordform', $form);

        return $viewModel;
    }

    public function removeemailAction()
    {
        $emailId = (int)$this->params()->fromPost('i');
        $result = $this->memberEmailRepository->delete($emailId);
        $viewModel = $this->createEmailActionCommons();
        $viewModel->setVariable('messages', array('user_email' => array('success' => 'Your email was removed.')));

        return $viewModel;
    }

    /**
     * @return ViewModel
     */
    private function createEmailActionCommons()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setTemplate('/application/settings/partials/email');
        $listEmails = $this->memberEmailService->fetchAllMailAddresses($this->ocsUser->member_id);
        $viewModel->setVariable('listEmails', $listEmails);
        $emailform = new SettingsEmailForm();
        $viewModel->setVariable('emailform', $emailform);

        return $viewModel;
    }

    public function setdefaultemailAction()
    {
        $emailId = (int)$this->params()->fromPost('i');

        $result = $this->memberEmailService->setDefaultEmail($emailId, $this->ocsUser->member_id);
        $viewModel = $this->createEmailActionCommons();
        if (true === $result) {
            try {
                $id_server = $this->oAuthService;
                $id_server->updateMailForUser($this->ocsUser->member_id);
                $this->ocsLog->debug(__METHOD__ . ' - oauth : ' . var_export($id_server->getMessages(), true));
            } catch (Exception $e) {
                $this->ocsLog->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
            try {
                $ldap_server = $this->lDapService;
                $ldap_server->updateMail($this->ocsUser->member_id);
                $this->ocsLog->debug(__METHOD__ . ' - ldap : ' . var_export($ldap_server->getMessages(), true));
            } catch (Exception $e) {
                $this->ocsLog->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
            try {
                $openCode = $this->gitlabService;
                $openCode->updateMail($this->ocsUser->member_id);
                $this->ocsLog->debug(__METHOD__ . ' - opencode : ' . var_export($openCode->getMessages(), true));
            } catch (Exception $e) {
                $this->ocsLog->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
        }

        $viewModel->setVariable('messages', array('user_email' => array('success' => 'Your default email was successfully changed. You have to logout and login again to update your session.')));

        return $viewModel;
    }

    public function resendverificationAction()
    {
        $emailId = (int)$this->params()->fromPost('i');

        $memberEmail = $this->memberEmailRepository->fetchById($emailId);

        $memberEmail->email_verification_value = md5($memberEmail->email_address . $this->ocsUser->username . time());
        $this->memberEmailRepository->update(
            [
                'email_id'                 => $memberEmail->email_id,
                'email_verification_value' => $memberEmail->email_verification_value,
            ]
        );

        $this->sendConfirmationMail($memberEmail->getArrayCopy());
        $viewModel = $this->createEmailActionCommons();
        $viewModel->setVariable('messages', array('user_email' => array('success' => 'New verification mail was send. Please check your email account.')));

        return $viewModel;
    }

    public function verificationAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);

        $filter = new FilterChain();
        // Insert filters into filter chain.
        $filter->setOptions(
            [
                'filters' => [
                    [
                        'name'     => 'StringTrim',
                        'priority' => FilterChain::DEFAULT_PRIORITY,
                    ],
                    [
                        'name'     => 'StripTags',
                        'priority' => FilterChain::DEFAULT_PRIORITY,
                    ],
                ],
            ]
        );
        $v = $this->params('v');
        $email = $filter->filter($v);
        if (empty($email)) {
            $this->flashMessenger()->addMessage('<p class="text-error">There was an error verifying your email. </p>');

            return $this->redirect()->toRoute('application_settings');
        }
        $result = $this->memberEmailService->verificationEmail($email);
        if ($result == 1) {
            $this->flashMessenger()->addMessage('<p class="text-success">Your email was successfully verified. </p>');
        } else {
            $this->flashMessenger()->addMessage('<p class="text-danger">There was an error verifying your email.</p>');
        }

        return $this->redirect()->toRoute('application_settings');
    }
}