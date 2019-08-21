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
class SettingsController extends Local_Controller_Action_DomainSwitch
{

    protected $_auth;
    protected $_memberId;
    /** @var  Zend_Db_Table */
    protected $_memberTable;
    /** @var  Zend_Db_Table_Row */
    protected $_memberSettings;

    protected $_projectTable;
    /** @var  Zend_Db_Table_Row */
    protected $_mainproject;

    private $htmlVerifier = '&lt;meta name="ocs-site-verification" content="?" /&gt;';

    public function init()
    {
        parent::init();
        $this->getResponse()->clearHeaders(array('Expires', 'Pragma', 'Cache-Control'))->setHeader('Pragma', 'no-cache',
            true)
             ->setHeader('Cache-Control', 'private, no-cache, must-revalidate', true);

        $this->_auth = Zend_Auth::getInstance();
        $this->_memberId = $this->_auth->getStorage()->read()->member_id;
        $this->_memberTable = new Default_Model_DbTable_Member();

        $showMember = $this->_memberTable->find($this->_memberId)->current();
        $this->view->member = $showMember;
        $this->_memberSettings = $showMember;

        $this->_projectTable = new Default_Model_DbTable_Project();

        // init default main project
        $main_project_id = $showMember->main_project_id;
        $mainproject_rowset = $this->_projectTable->find($main_project_id);
        $this->_mainproject = $this->view->mainproject = $mainproject_rowset->current();


        $action = $this->getRequest()->getActionName();
        $title = '';
        if ($action == 'index') {
            $title = 'settings';
        } else {
            $title = $action;
        }
        $this->view->headTitle($title . ' - ' . $this->getHeadTitle(), 'SET');
    }

    public function indexAction()
    {
        $this->view->member = $this->_memberSettings;
        $memberSettings = $this->_memberSettings->toArray();

        $paypalValidStatusTable = new Default_Model_DbTable_PaypalValidStatus();
        $paypalValidStatus = $paypalValidStatusTable->find($this->_memberSettings->paypal_valid_status)->current();
        $this->view->paypal_valid_status = $paypalValidStatus;

        $this->view->profileform = $this->formProfile();
        $this->view->profileform->populate($memberSettings);
        $this->view->profileform->aboutme->setValue($this->_mainproject->description);

        $this->view->accounts = $this->formConnectedAccounts();
        $this->view->accounts->populate($memberSettings);

        $this->view->github = $this->formGithub();
        $this->view->github->populate($memberSettings);

        $this->view->pictureform = $this->formProfilePicture();
        $this->view->pictureform->populate($memberSettings);

        $this->view->pictureformbg = $this->formProfilePictureBackground();
        $this->view->pictureformbg->populate($memberSettings);

        $this->view->passwordform = $this->formPassword();

        $websiteOwner = new Local_Verification_WebsiteOwner();
        $linkWebsite = stripslashes($this->_memberSettings->link_website);
        $this->view->homepageform =
            $this->formHomepage($linkWebsite, $websiteOwner->generateAuthCode($linkWebsite),
                $this->_memberSettings->validated);

        $this->view->newsletterform = $this->formNewsletter(stripslashes($this->_memberSettings->newsletter));

        $this->view->paymentform = $this->formPayment();
        $this->view->paymentform->populate($memberSettings);
    }

    /**
     * @return Default_Form_Settings
     * @throws Zend_Form_Exception
     */
    private function formProfile()
    {
        $form = new Default_Form_Settings();
        $form->setMethod("POST")->setAttrib("id", "settingsProfileForm")->setAction('/settings/saveprofile');

        $userNameLength = new Zend_Validate_StringLength(array('min' => 4, 'max' => 35));
        $username =
            $form->createElement('text',
                'username')->setLabel("Username:")->setRequired(false)->setFilters(array('StringTrim'))
                 ->addValidator($userNameLength)->setAttrib('readonly', 'true')->setDecorators(array(
                    'ViewHelper',
                    'Label',
                    'Errors',
                    array(
                        'ViewScript',
                        array(
                            'viewScript' => 'settings/viewscripts/flatui_input.phtml',
                            'placement'  => false
                        )
                    )
                ));
        $form->addElement($username);

        $firstname = $form->createElement('text',
            'firstname')->setLabel("First Name:")->setRequired(false)->removeDecorator('HtmlTag')
                          ->setFilters(array('StringTrim'))->setDecorators(array(
                'ViewHelper',
                'Label',
                'Errors',
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'settings/viewscripts/flatui_input.phtml',
                        'placement'  => false
                    )
                )
            ));
        $form->addElement($firstname);

        $lastname = $form->createElement('text',
            'lastname')->setLabel("Last Name:")->setRequired(false)->removeDecorator('HtmlTag')
                         ->setFilters(array('StringTrim'))->setDecorators(array(
                'ViewHelper',
                'Label',
                'Errors',
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'settings/viewscripts/flatui_input.phtml',
                        'placement'  => false
                    )
                )
            ));
        $form->addElement($lastname);

        $city = $form->createElement('text',
            'city')->setLabel("City:")->setRequired(false)->setFilters(array('StringTrim'))
                     ->removeDecorator('HtmlTag')->setDecorators(array(
                'ViewHelper',
                'Label',
                'Errors',
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'settings/viewscripts/flatui_input.phtml',
                        'placement'  => false
                    )
                )
            ));
        $form->addElement($city);

        $country = $form->createElement('text',
            'country')->setLabel("Country:")->setRequired(false)->setFilters(array('StringTrim'))
                        ->removeDecorator('HtmlTag')->setDecorators(array(
                'ViewHelper',
                'Label',
                'Errors',
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'settings/viewscripts/flatui_input.phtml',
                        'placement'  => false
                    )
                )
            ));
        $form->addElement($country);

        $about = $form->createElement('textarea',
            'aboutme')->setLabel('About me:')->setRequired(false)->setAttrib('class', 'about')
                      ->setDecorators(array(
                          'ViewHelper',
                          'Label',
                          'Errors',
                          array(
                              'ViewScript',
                              array(
                                  'viewScript' => 'settings/viewscripts/flatui_input.phtml',
                                  'placement'  => false
                              )
                          )
                      ));
        $form->addElement($about);

        return $form;
    }


    /**
     * Forms
     */

    /**
     * @return Default_Form_Settings
     * @throws Zend_Form_Exception
     */
    private function formConnectedAccounts()
    {
        $form = new Default_Form_Settings();
        $form->setMethod("POST")->setAttrib("id", "settingsConnectedAccounts")->setAction('/settings/accounts');

        $facebook = $form->createElement('text', 'link_facebook')->setLabel("Facebook Profile:")->setRequired(false)
                         ->removeDecorator('HtmlTag')->setDecorators(array(
                'ViewHelper',
                'Label',
                'Errors',
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'settings/viewscripts/flatui_input.phtml',
                        'placement'  => false
                    )
                )
            ));
        $facebook->addValidator(new Local_Validate_PartialUrl());
        $form->addElement($facebook);

        $twitter =
            $form->createElement('text',
                'link_twitter')->setLabel("Twitter Profile:")->setRequired(false)->removeDecorator('HtmlTag')
                 ->setDecorators(array(
                     'ViewHelper',
                     'Label',
                     'Errors',
                     array(
                         'ViewScript',
                         array(
                             'viewScript' => 'settings/viewscripts/flatui_input.phtml',
                             'placement'  => false
                         )
                     )
                 ));
        $twitter->addValidator(new Local_Validate_PartialUrl);
        $form->addElement($twitter);

        $github =
            $form->createElement('text',
                'link_github')->setLabel("GitHub Profile:")->setRequired(false)->removeDecorator('HtmlTag')
                 ->setDecorators(array(
                     'ViewHelper',
                     'Label',
                     'Errors',
                     array(
                         'ViewScript',
                         array(
                             'viewScript' => 'settings/viewscripts/flatui_input.phtml',
                             'placement'  => false
                         )
                     )
                 ));
        $github->addValidator(new Local_Validate_PartialUrl);
        $form->addElement($github);

        return $form;
    }

    /**
     * @return Default_Form_Settings
     * @throws Zend_Form_Exception
     */
    private function formGithub()
    {
        $form = new Default_Form_Settings();
        $form->setMethod("POST")->setAttrib("id", "settingsGithub")->setAction('/settings/github');

        $github = new Default_Form_Element_UsernameGithub('link_github');
        $github->setLabel("GitHub Profile:")->setRequired(false)->removeDecorator('HtmlTag')->setDecorators(array(
            'ViewHelper',
            'Label',
            'Errors',
            array(
                'ViewScript',
                array(
                    'viewScript' => 'settings/viewscripts/flatui_input.phtml',
                    'placement'  => false
                )
            )
        ));
        $form->addElement($github);

        $token = new Default_Form_Element_TokenGithub('token_github');
        $token->setLabel("GitHub Access Token:")->setRequired(false)->removeDecorator('HtmlTag')->setDecorators(array(
            'ViewHelper',
            'Label',
            'Errors',
            array(
                'ViewScript',
                array(
                    'viewScript' => 'settings/viewscripts/flatui_input.phtml',
                    'placement'  => false
                )
            )
        ));
        $form->addElement($token);

        return $form;
    }

    /**
     * @return Default_Form_Settings
     * @throws Zend_File_Transfer_Exception
     * @throws Zend_Form_Exception
     */
    private function formProfilePicture()
    {
        $form = new Default_Form_Settings();
        $form->setMethod("POST")->setAttrib("id", "settingsPictureForm")->setAction('/settings/picture')
             ->setAttrib('enctype', 'multipart/form-data');

        $hiddenProfilePicture = $form->createElement('hidden', 'profile_image_url')->setDecorators(array(
            'ViewHelper',
            array(
                'ViewScript',
                array(
                    'viewScript' => 'settings/viewscripts/flatui_hidden_image.phtml',
                    'placement'  => false
                )
            )
        ))->setAttrib('data-target', '#profile-picture-preview');

        $form->addElement($hiddenProfilePicture);

        $imageTable = new Default_Model_DbTable_Image();
        $productPicture =
            $form->createElement('file',
                'profile_picture_upload')->setDisableLoadDefaultDecorators(true)->setLabel('Profile Picture')
                 ->setRequired(false)->setDecorators(array(
                    'File',
                    array(
                        'ViewScript',
                        array(
                            'viewScript' => 'settings/viewscripts/flatui_profile_image.phtml',
                            'placement'  => false
                        )
                    )

                ))->setAttrib('class', 'product-picture')
                 ->setAttrib('onchange', 'ImagePreview.previewImage(this, \'profile-picture-preview\');')
                 ->setTransferAdapter(new Local_File_Transfer_Adapter_Http())->setMaxFileSize(2097152)->addValidator('Count',
                    false, 1)
                 ->addValidator('Size', false, array('min' => '5kB', 'max' => '2MB'))
                 ->addValidator('Extension', false, $imageTable->getAllowedFileExtension())->addValidator('ImageSize',
                    false, array(
                        'minwidth'  => 20,
                        'maxwidth'  => 1024,
                        'minheight' => 20,
                        'maxheight' => 1024
                    ))->addValidator('MimeType', false, $imageTable->getAllowedMimeTypes());

        $form->addElement($productPicture);

        $facebook_username = $form->createElement('text',
            'facebook_username')->setLabel("From Facebook Profile:")->setRequired(false)
                                  ->removeDecorator('HtmlTag')
                                  ->setAttrib('data-href', 'https://graph.facebook.com/{username}/picture?type=large')
                                  ->setAttrib('data-target', '#profile-picture-preview')->setAttrib('data-src',
                'facebook')
                                  ->setAttrib('class', 'avatar')->setDecorators(array(
                'ViewHelper',
                'Label',
                'Errors'
            ));
        $form->addElement($facebook_username);

        $twitter_username = $form->createElement('text',
            'twitter_username')->setLabel("From Twitter Profile:")->setRequired(false)
                                 ->removeDecorator('HtmlTag')
                                 ->setAttrib('data-href', 'http://twitter.com/api/users/profile_image/{username}')
                                 ->setAttrib('data-target', '#profile-picture-preview')->setAttrib('data-src',
                'twitter')
                                 ->setAttrib('class', 'avatar')->setDecorators(array(
                'ViewHelper',
                'Label',
                'Errors'
            ));
        $form->addElement($twitter_username);

        $gravatar_email = $form->createElement('text',
            'gravatar_email')->setLabel("From Gravatar Profile:")->setRequired(false)
                               ->setAttrib('data-href', 'http://www.gravatar.com/avatar/{username}.jpg')
                               ->setAttrib('data-target', '#profile-picture-preview')->setAttrib('data-func', 'MD5')
                               ->setAttrib('data-src', 'gravatar')->setAttrib('class', 'avatar')->setDecorators(array(
                'ViewHelper',
                'Label',
                'Errors'
            ));
        $form->addElement($gravatar_email);

        $hiddenProfilePictureSrc = $form->createElement('hidden', 'profile_img_src')->setDecorators(array(
            'ViewHelper'
        ));

        $form->addElement($hiddenProfilePictureSrc);

        return $form;
    }

    /**
     * @return Default_Form_Settings
     * @throws Zend_File_Transfer_Exception
     * @throws Zend_Form_Exception
     */
    private function formProfilePictureBackground()
    {
        $form = new Default_Form_Settings();
        $form->setMethod("POST")->setAttrib("id",
            "settingsPictureBackgroundForm")->setAction('/settings/picturebackground')
             ->setAttrib('enctype', 'multipart/form-data');

        $hiddenProfilePicture = $form->createElement('hidden', 'profile_image_url_bg')->setDecorators(array(
            'ViewHelper',
            array(
                'ViewScript',
                array(
                    'viewScript' => 'settings/viewscripts/flatui_hidden_image.phtml',
                    'placement'  => false
                )
            )
        ))->setAttrib('data-target', '#profile-picture-bg-preview');

        $form->addElement($hiddenProfilePicture);

        $imageTable = new Default_Model_DbTable_Image();
        $productPicture = $form->createElement('file',
            'profile_picture_background_upload')->setDisableLoadDefaultDecorators(true)
                               ->setLabel('Background Picture')->setRequired(false)->setDecorators(array(
                'File',
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'settings/viewscripts/flatui_profile_image_background.phtml',
                        'placement'  => false
                    )
                )

            ))->setAttrib('class', 'product-picture')->setAttrib('onchange',
                'ImagePreview.previewImageMember(this, \'profile-picture-background-preview\');')
                               ->setTransferAdapter(new Local_File_Transfer_Adapter_Http())//->setMaxFileSize(2097152)
                               ->addValidator('Count', false,
                1)//->addValidator('Size', false, array('min' => '5kB', 'max' => '2MB'))
                               ->addValidator('Extension', false, $imageTable->getAllowedFileExtension())
                               ->addValidator('MimeType', false, $imageTable->getAllowedMimeTypes());

        $form->addElement($productPicture);

        return $form;
    }

    /**
     * @return Default_Form_Settings
     * @throws Zend_Form_Exception
     */
    private function formPassword()
    {
        $form = new Default_Form_Settings();
        $form->setMethod("POST")->setAttrib("id", "settingsPasswordForm")->setAction('/settings/password');

        $passOld = $form->createElement('password', 'passwordOld')->setLabel('Enter old Password:')->setRequired(true)
                        ->removeDecorator('HtmlTag')->addValidator(new Local_Validate_OldPasswordConfirm())->setDecorators(array(
                'ViewHelper',
                'Label',
                'Errors',
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'settings/viewscripts/flatui_input.phtml',
                        'placement'  => false
                    )
                )
            ));

        $pass1 = $form->createElement('password', 'password1')->setLabel('Enter new Password:')->setRequired(true)
                      ->addValidator(new Zend_Validate_NotEmpty(Zend_Validate_NotEmpty::STRING))->removeDecorator('HtmlTag')
                      ->setDecorators(array(
                          'ViewHelper',
                          'Label',
                          'Errors',
                          array(
                              'ViewScript',
                              array(
                                  'viewScript' => 'settings/viewscripts/flatui_input.phtml',
                                  'placement'  => false
                              )
                          )
                      ));

        $pass2 = $form->createElement('password', 'password2')->setLabel('Re-enter new Password:')->setRequired(true)
                      ->addValidator(new Zend_Validate_NotEmpty(Zend_Validate_NotEmpty::STRING))->removeDecorator('HtmlTag')
                      ->setDecorators(array(
                          'ViewHelper',
                          'Label',
                          'Errors',
                          array(
                              'ViewScript',
                              array(
                                  'viewScript' => 'settings/viewscripts/flatui_input.phtml',
                                  'placement'  => false
                              )
                          )
                      ));

        $passValid = new Local_Validate_PasswordConfirm($pass2->getValue());
        $pass1->addValidator($passValid);

        $form->addElement($passOld)->addElement($pass1)->addElement($pass2);

        return $form;
    }

    /**
     * @param string $valHomepage
     * @param string $valVerifyKey
     * @param bool   $isVerified
     *
     * @return Local_Form
     * @throws Zend_Form_Exception
     */
    private function formHomepage($valHomepage = '', $valVerifyKey = '', $isVerified = false)
    {

        $form = new Local_Form();
        $form->setMethod("POST")->setAttrib("id", "settingsHomepageForm")->setAction('/settings/homepage')
             ->addPrefixPath('Local_Form_Element_', 'Local/Form/Element/', 'element');

        $homepage = $form->createElement('text',
            'link_website')->setLabel("Website:")->setRequired(false)->setValue($valHomepage)
                         ->addValidator(new Local_Validate_PartialUrl)->setDecorators(array(
                'ViewHelper',
                'Label',
                'Errors',
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'settings/viewscripts/flatui_input.phtml',
                        'placement'  => false
                    )
                )
            ));
        if ($isVerified) {
            $homepage->setDescription('<div class="image checked"></div>');
        } else {
            $homepage->setDescription('<div class="image unchecked"></div>');
        }
        $homepage->addDecorators(array(
            array('Description', array('tag' => '', 'escape' => false))
        ));

        $form->addElement($homepage);

        $hash = $form->createElement('hash', 'csrf', array('salt' => 'RumbaSpiess'));
        $hash->setDecorators(array('ViewHelper', 'Errors'));
        $hash->getValidator('Identical')->setMessage('Your session is outdated. Please reload the page an try again.');
        $form->addElement($hash);

        if ('' != $valVerifyKey) {
            $value = str_replace('?', $valVerifyKey, $this->htmlVerifier);
            $verifyCode =
                $form->createElement('note',
                    'html_verifier')->setValue($value)->removeDecorator('HtmlTag')->removeDecorator('Label');
            $form->addElement($verifyCode);
        }

        return $form;
    }

    /**
     * @param string $valNewsletter
     *
     * @return Default_Form_Settings
     * @throws Zend_Form_Exception
     * @throws Zend_Validate_Exception
     */
    private function formNewsletter($valNewsletter = '')
    {
        $form = new Default_Form_Settings();
        $form->setMethod("POST")->setAttrib("id", "settingsNewsletterForm")->setAction('/settings/newsletter');

        $questionValid = new Zend_Validate_InArray(array('1', '0'));
        $questionValid->setMessage('Yes is required!');

        $question = $form->createElement('checkbox', 'newsletter')//            ->addValidator($questionValid, true)
                         ->setRequired(true)->removeDecorator('HtmlTag')->removeDecorator('Label');

        $question->setValue($valNewsletter);
        $form->addElement($question);

        return $form;
    }

    /**
     * @param string $valPaypalEmail
     * @param string $valWalletAddress
     * @param string $valDwollaId
     *
     * @return Default_Form_Settings
     * @throws Zend_Form_Exception
     * @throws Zend_Validate_Exception
     */
    private function formPayment($valPaypalEmail = '', $valWalletAddress = '', $valDwollaId = '')
    {
        $form = new Default_Form_Settings();
        $form->setMethod("POST")->setAttrib("id", "settingsPaymentForm")->setAction('/settings/payment');

        $mailValidCheck = new Zend_Validate_EmailAddress();
        $mailValidCheck->setMessage('RegisterFormEmailErrNotValid', Zend_Validate_EmailAddress::INVALID)
                       ->setMessage('RegisterFormEmailErrNotValid', Zend_Validate_EmailAddress::INVALID_FORMAT)
                       ->setMessage('RegisterFormEmailErrNotValid', Zend_Validate_EmailAddress::INVALID_LOCAL_PART)
                       ->setMessage("RegisterFormEmailErrWrongHost", Zend_Validate_EmailAddress::INVALID_HOSTNAME)
                       ->setMessage("RegisterFormEmailErrWrongHost2", Zend_Validate_Hostname::INVALID_HOSTNAME)
                       ->setMessage("RegisterFormEmailErrHostLocal", Zend_Validate_Hostname::LOCAL_NAME_NOT_ALLOWED)
                       ->setOptions(array('domain' => true));

        $mailEmpty = new Zend_Validate_NotEmpty();
        $mailEmpty->setMessage('RegisterFormEmailErrEmpty', Zend_Validate_NotEmpty::IS_EMPTY);

        $mailValidatorChain = new Zend_Validate();
        $mailValidatorChain->addValidator($mailValidCheck, true);

        $mail = $form->createElement('text', 'paypal_mail')->setLabel('Paypal: Email Adress')->setRequired(false)
                     ->addValidator($mailValidCheck, true)->setDecorators(array(
                'ViewHelper',
                'Label',
                'Errors',
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'settings/viewscripts/flatui_input.phtml',
                        'placement'  => false
                    )
                )
            ));
        $mail->setValue($valPaypalEmail);
        $form->addElement($mail);

        $bitcoinAddress =
            $form->createElement('text',
                'wallet_address')->setLabel('Bitcoin: Your Public Wallet Address')->setRequired(false)
                 ->setDecorators(array(
                     'ViewHelper',
                     'Label',
                     'Errors',
                     array(
                         'ViewScript',
                         array(
                             'viewScript' => 'settings/viewscripts/flatui_input.phtml',
                             'placement'  => false
                         )
                     )
                 ))->addValidators(array(
                    array(
                        'regex',
                        false,
                        array(
                            'pattern'  => '/^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$/',
                            'messages' => 'The Bitcoin Address is not valid.'
                        )
                    )
                ));
        $bitcoinAddress->setValue($valWalletAddress);
        $form->addElement($bitcoinAddress);

        $dwolla = $form->createElement('text',
            'dwolla_id')->setLabel('Dwolla: User ID (xxx-xxx-xxxx)')->setRequired(false)
                       ->setDecorators(array(
                           'ViewHelper',
                           'Label',
                           'Errors',
                           array(
                               'ViewScript',
                               array(
                                   'viewScript' => 'settings/viewscripts/flatui_input.phtml',
                                   'placement'  => false
                               )
                           )
                       ));
        $dwolla->setValue($valDwollaId);
        $form->addElement($dwolla);

        return $form;
    }

    public function profileAction()
    {
        $this->view->member = $this->_memberSettings;
        $memberSettings = $this->_memberSettings->toArray();


        $this->view->profileform = $this->formProfile();
        $this->view->profileform->populate($memberSettings);
        $this->view->profileform->aboutme->setValue($this->_mainproject->description);

        $this->view->accounts = $this->formConnectedAccounts();
        $this->view->accounts->populate($memberSettings);


        $this->view->pictureform = $this->formProfilePicture();
        $this->view->pictureform->populate($memberSettings);

        $this->view->pictureformbg = $this->formProfilePictureBackground();
        $this->view->pictureformbg->populate($memberSettings);


        $websiteOwner = new Local_Verification_WebsiteOwner();
        $linkWebsite = stripslashes($this->_memberSettings->link_website);
        $this->view->homepageform =
            $this->formHomepage($linkWebsite, $websiteOwner->generateAuthCode($linkWebsite),
                $this->_memberSettings->validated);
    }

    public function savetagsAction()
    {
        $this->_helper->layout->disableLayout();
        $error_text = '';

        $tag_id = null;
        if (!empty($_POST['tag_id'])) {
            $tag_id = $_POST['tag_id'];
        }
        $tag_group_id = $_POST['tag_group_id'];
        $tag_object_id = $this->_memberId;
        $model = new Default_Model_Tags();
        $model->saveOSTagForUser($tag_id, $tag_group_id, $tag_object_id);
        $this->_helper->json(array('status' => 'ok'));
    }

    public function saveprofileAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer('partials/profile');

        if ($this->_request->isPost()) {
            $form = $this->formProfile();

            if ($form->isValid($_POST)) {
                $values = $form->getValues();

                //remove email and username
                unset($values['username']);
                unset($values['mail']);

                $values['firstname'] = Default_Model_HtmlPurify::purify($values['firstname']);
                $values['lastname'] = Default_Model_HtmlPurify::purify($values['lastname']);
                $values['city'] = Default_Model_HtmlPurify::purify($values['city']);
                $values['country'] = Default_Model_HtmlPurify::purify($values['country']);
                $values['aboutme'] = Default_Model_HtmlPurify::purify($values['aboutme']);

                $this->_memberSettings->setFromArray($values);
                $this->_memberSettings->save();

                $this->_mainproject->description = $values['aboutme'];

                $this->_mainproject->save();

                $this->view->profileform = $form;
                $this->view->save = 1;

                // ppload
                // Update profile information
                $this->_updatePploadProfile();
            } else {
                $this->view->profileform = $form;
                $this->view->error = 1;
            }
        } else {
            $form = $this->formProfile();
            $form->populate($this->_memberSettings->toArray());
            $this->view->profileform = $form;
        }
    }

    /**
     * ppload
     */
    protected function _updatePploadProfile()
    {
        $pploadApi = new Ppload_Api(array(
            'apiUri'   => PPLOAD_API_URI,
            'clientId' => PPLOAD_CLIENT_ID,
            'secret'   => PPLOAD_SECRET
        ));

        $profileName = '';
        if ($this->_memberSettings->firstname
            || $this->_memberSettings->lastname) {
            $profileName = trim($this->_memberSettings->firstname . ' ' . $this->_memberSettings->lastname);
        } else {
            if ($this->_memberSettings->username) {
                $profileName = $this->_memberSettings->username;
            }
        }

        $profileRequest = array(
            'owner_id'    => $this->_memberId,
            'name'        => $profileName,
            'email'       => $this->_memberSettings->mail,
            'homepage'    => $this->_memberSettings->link_website,
            'image'       => $this->_memberSettings->profile_image_url,
            'description' => $this->_mainproject->description
        );
        $profileResponse = $pploadApi->postProfile($profileRequest);
    }

    public function accountsAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer('partials/accounts');

        if ($this->_request->isPost()) {
            $form = $this->formConnectedAccounts();

            if ($form->isValid($_POST)) {
                $this->_memberSettings->setFromArray($form->getValues());
                $this->_memberSettings->save();

                $this->view->accounts = $form;
                $this->view->save = 1;
            } else {
                $this->view->accounts = $form;
                $this->view->error = 1;
            }
        } else {
            $form = $this->formProfile();
            $form->populate($this->_memberSettings->toArray());
            $this->view->accounts = $form;
        }
    }

    public function githubAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer('partials/github');

        if ($this->_request->isPost()) {
            $form = $this->formGithub();

            if ($form->isValid($_POST)) {
                $this->_memberSettings->setFromArray($form->getValues());
                $this->_memberSettings->save();

                $memberToken = new Default_Model_DbTable_MemberToken();
                $memberToken->save(array(
                    'token_member_id'         => $this->_memberId,
                    'token_provider_name'     => 'github_personal',
                    'token_value'             => $form->getValue('token_github'),
                    'token_provider_username' => $form->getValue('link_github')
                ));

                $this->view->github = $form;
                $this->view->save = 1;
            } else {
                $this->view->github = $form;
                $this->view->error = 1;
            }
        } else {
            $form = $this->formProfile();
            $form->populate($this->_memberSettings->toArray());
            $this->view->github = $form;
        }
    }

    public function pictureAction()
    {
        ini_set('memory_limit', '128M');

        $this->_helper->layout->disableLayout();

        if ($this->_request->isPost()) {
            $form = $this->formProfilePicture();

            $formFilename = $form->getElement('profile_picture_upload')->getFileName();
            if (is_array($formFilename)) {
                Zend_Registry::get('logger')->info(__METHOD__ . ' :: form input:' . print_r($formFilename, true));
                $filename = $formFilename['profile_picture_upload'];
            } else {
                Zend_Registry::get('logger')->info(__METHOD__ . ' :: form input:' . print_r($formFilename, true));
                $filename = $formFilename;
            }
            $profilePictureTitleFilename = pathinfo($filename);

            if (!isset($profilePictureTitleFilename)) {
                $form->populate($this->_memberSettings->toArray());
                $form->addErrorMessage('Please select a new picture');
                $form->markAsError();

                $this->view->pictureform = $form;
                $this->view->error = 1;
                $this->renderScript('settings/partials/picture.phtml');

                return;
            }
            if ($form->isValid($_POST)) {

                $tmpProfilePictureTitle =
                    IMAGES_UPLOAD_PATH . 'tmp/' . Local_Tools_UUID::generateUUID() . '_' . $profilePictureTitleFilename['basename'];
                $form->getElement('profile_picture_upload')
                     ->addFilter('Rename', array('target' => $tmpProfilePictureTitle, 'overwrite' => true));

                $values = $form->getValues();

                if (array_key_exists('profile_picture_upload', $values) && $values['profile_picture_upload'] != "") {
                    $imageService = new Default_Model_DbTable_Image();
                    $newImageName = $imageService->saveImageOnMediaServer($tmpProfilePictureTitle);
                }
                if ($form->getElement('facebook_username')->getValue() !== null) {
                    $this->_memberSettings->facebook_username = $values['facebook_username'];
                }
                if ($form->getElement('twitter_username')->getValue() !== null) {
                    $this->_memberSettings->twitter_username = $values['twitter_username'];
                }
                if ($form->getElement('gravatar_email')->getValue() !== null) {
                    $this->_memberSettings->gravatar_email = $values['gravatar_email'];
                }
                if ($values['profile_img_src'] == 'local' && isset($newImageName)) {
                    $this->_auth->getIdentity()->avatar = $newImageName;
                    $this->_auth->getIdentity()->profile_image_url = IMAGES_MEDIA_SERVER . '/cache/200x200-2/img/' . $newImageName;
                    $this->_memberSettings->avatar = $newImageName;
                    $this->_memberSettings->profile_image_url = IMAGES_MEDIA_SERVER . '/cache/200x200-2/img/' . $newImageName;
                    $this->_memberSettings->avatar_type_id = Default_Model_DbTable_Member::MEMBER_AVATAR_TYPE_USERUPDATED;
                }
                $this->_memberSettings->profile_img_src = $values['profile_img_src'];

                $this->_memberSettings->save();
                $this->view->member = $this->_memberSettings;
                $form->populate($this->_memberSettings->toArray());

                try {
                    $id_server = new Default_Model_Ocs_OAuth();
                    $id_server->updateAvatarForUser($this->_memberSettings->member_id);
                } catch (Exception $e) {
                    Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                }
                try {
                    $ldap_server = new Default_Model_Ocs_Ldap();
                    $ldap_server->updateAvatar($this->_memberSettings->member_id);
                    Zend_Registry::get('logger')->debug(__METHOD__ . ' - ldap : ' . implode(PHP_EOL . " - ",
                            $ldap_server->getMessages()));
                } catch (Exception $e) {
                    Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                }

                $this->view->save = 1;
                $this->view->pictureform = $form;

                // ppload
                // Update profile information
                $this->_updatePploadProfile();

                $this->renderScript('settings/partials/picture.phtml');
            } else {
                $this->view->pictureform = $form;
                $this->view->error = 1;
                $this->renderScript('settings/partials/picture.phtml');
            }
        } else {
            $form = $this->formProfilePicture();
            $form->populate($this->_memberSettings->toArray());
            $this->view->pictureform = $form;
            $this->renderScript('settings/partials/picture.phtml');
        }
    }

    public function deletepicturebackgroundAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_memberSettings->profile_image_url_bg = null;
        $this->_memberSettings->save();

        $this->_helper->json(array(
            'status' => 'ok'
        ));
    }

    public function picturebackgroundAction()
    {
        ini_set('memory_limit', '128M');

        $this->_helper->layout->disableLayout();

        if ($this->_request->isPost()) {
            $form = $this->formProfilePictureBackground();

            $profilePictureElement = $form->getElement('profile_picture_background_upload');
            if (!isset($profilePictureElement)) {
                $form->populate($this->_memberSettings->toArray());
                $form->addErrorMessage('Please select a new picture');
                $form->markAsError();

                $this->view->pictureformbg = $form;
                $this->view->error = 1;
                $this->renderScript('settings/partials/picture-bg.phtml');

                return;
            }

            $profilePictureTitleFilename = pathinfo($form->getElement('profile_picture_background_upload')->getFileName());

            if ($form->isValid($_POST)) {

                $tmpProfilePictureTitle =
                    IMAGES_UPLOAD_PATH . 'tmp/' . Local_Tools_UUID::generateUUID() . '_' . $profilePictureTitleFilename['basename'];
                $form->getElement('profile_picture_background_upload')
                     ->addFilter('Rename', array('target' => $tmpProfilePictureTitle, 'overwrite' => true));

                $values = $form->getValues();

                if (array_key_exists('profile_picture_background_upload', $values)
                    && $values['profile_picture_background_upload'] != "") {
                    $imageService = new Default_Model_DbTable_Image();
                    $newImageName = $imageService->saveImageOnMediaServer($tmpProfilePictureTitle);
                }

                if (isset($newImageName)) {
                    $this->_memberSettings->profile_image_url_bg = IMAGES_MEDIA_SERVER . '/cache/1920x450-2/img/' . $newImageName;
                }

                $this->_memberSettings->save();
                $this->view->member = $this->_memberSettings;
                $form->populate($this->_memberSettings->toArray());

                $this->view->save = 1;
                $this->view->pictureformbg = $form;

                $this->renderScript('settings/partials/picture-bg.phtml');
            } else {
                $this->view->pictureformbg = $form;
                $this->view->error = 1;
                $this->renderScript('settings/partials/picture-bg.phtml');
            }
        } else {
            $form = $this->formProfilePictureBackground();
            $form->populate($this->_memberSettings->toArray());
            $this->view->pictureformbg = $form;
            $this->renderScript('settings/partials/picture-bg.phtml');
        }
    }

    public function passwordAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer('partials/password');

        $form = $this->formPassword();

        if (false === $this->_request->isPost()) {

            $this->view->passwordform = $form;

            return;
        }

        if (false === $form->isValid($_POST)) {
            $this->view->passwordform = $form;
            $this->view->error = 1;

            return;
        }

        $values = $form->getValues();

        if ($this->_memberSettings->password != Local_Auth_Adapter_Ocs::getEncryptedPassword($values['passwordOld'], $this->_memberSettings->password_type)) {
            $form->addErrorMessage('Your old Password is wrong!');
            $this->view->passwordform = $form;
            $this->view->error = 1;

            return;
        }

        //20180801 ronald: If a Hive User changes his password, we change the password type to our Default
        if ($this->_memberSettings->password_type == Default_Model_Member::PASSWORD_TYPE_HIVE) {
            //Save old data
            $this->_memberSettings->password_old = $this->_memberSettings->password;
            $this->_memberSettings->password_type_old = Default_Model_Member::PASSWORD_TYPE_HIVE;

            //Change type and password
            $this->_memberSettings->password_type = Default_Model_Member::PASSWORD_TYPE_OCS;
        }

        $this->_memberSettings->password = Local_Auth_Adapter_Ocs::getEncryptedPassword($values['password1'], $this->_memberSettings->password_type);
        $this->_memberSettings->save();

        $this->view->passwordform = $form;
        $this->view->save = 1;

        //Update Auth-Services
        try {
            $id_server = new Default_Model_Ocs_OAuth();
            $id_server->updatePasswordForUser($this->_memberSettings->member_id);
            Zend_Registry::get('logger')->info(__METHOD__ . ' - ldap : ' . implode(PHP_EOL . " - ", $id_server->getMessages()));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        try {
            $ldap_server = new Default_Model_Ocs_Ldap();
            $ldap_server->updatePassword($this->_memberSettings->member_id, $values['password1']);
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - ldap : ' . implode(PHP_EOL . " - ", $ldap_server->getMessages()));
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    public function homepageAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer('partials/website');

        if ($this->_request->isGet()) {
            $websiteVerifier = new Local_Verification_WebsiteOwner();
            $authCode = $websiteVerifier->generateAuthCode($this->_memberSettings->link_website);
            $form = $this->formHomepage($this->_memberSettings->link_website, $authCode,
                $this->_memberSettings->validated);
            $this->view->homepageform = $form;

            return;
        }

        $form = $this->formHomepage($_POST['link_website']);
        if ($form->isNotValid($_POST)) {
            $this->view->homepageform = $form;
            $this->view->error = 1;

            return;
        }

        $values = $form->getValues();

        if ($this->_memberSettings->link_website == $values['link_website']) {
            $websiteVerifier = new Local_Verification_WebsiteOwner();
            $authCode = $websiteVerifier->generateAuthCode($this->_memberSettings->link_website);
            $form = $this->formHomepage($this->_memberSettings->link_website, $authCode);
            $this->view->homepageform = $form;
            $this->view->save = 0;

            return;
        }

        $websiteVerifier = new Local_Verification_WebsiteOwner();
        $authCode = $websiteVerifier->generateAuthCode($values['link_website']);

        //$queue = Local_Queue_Factory::getQueue();
        //$command = new Backend_Commands_CheckMemberWebsite($this->_memberId, $values['link_website'], $authCode);
        //$queue->send(serialize($command));

        $this->_memberSettings->link_website = $values['link_website'];
        $this->_memberSettings->validated = 0;
        $this->_memberSettings->save();

        $this->view->save = 1;
        $this->view->homepageform = $this->formHomepage($values['link_website'], $authCode);

        // ppload
        // Update profile information
        $this->_updatePploadProfile();
    }

    public function newsletterAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer('partials/newsletter');

        if ($this->_request->isPost()) {
            $form = $this->formNewsletter();

            if ($form->isValid($_POST)) {
                $values = $form->getValues();

                $this->_memberSettings->newsletter = $values['newsletter'];

                $this->_memberSettings->save();

                $this->view->newsletterform = $this->formNewsletter($this->_memberSettings->newsletter);

                $this->view->save = 1;
            } else {
                $this->view->newsletterform = $form;
                $this->view->error = 1;
            }
        } else {
            $form = $this->formNewsletter($this->_memberSettings->newsletter);

            $this->view->newsletterform = $form;
        }
    }

    public function paymentAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer('partials/payment');

        if ($this->_request->isPost()) {
            $form = $this->formPayment();

            if ($form->isValid($_POST)) {
                $values = $form->getValues();
                //If the user changes the paypal address, we set the valid staus back to null
                if ($this->_memberSettings->paypal_mail != $values['paypal_mail']) {
                    //$showMember = $this->_memberTable->find($this->_memberId)->current();
                    //$showMember->paypal_valid_status = null;
                    //$this->_memberTable->save($showMember);
                    //$this->view->member = $showMember;
                    $this->_memberTable->update(array('paypal_valid_status' => null),
                        'member_id = ' . $this->_memberId);

                    //Log if paypal changes

                    $desc = 'Paypal-Address changed from ';
                    if (isset($this->_memberSettings->paypal_mail)) {
                        $desc .= $this->_memberSettings->paypal_mail;
                    }
                    $desc .= ' to ' . $values['paypal_mail'];
                    Default_Model_ActivityLog::logActivity($this->_memberSettings->member_id, null, $this->_memberId,
                        Default_Model_ActivityLog::MEMBER_PAYPAL_CHANGED, array('title' => '', 'description' => $desc));
                }

                $this->_memberSettings->paypal_mail = $values['paypal_mail'];
                $this->_memberSettings->wallet_address = $values['wallet_address'];
                $this->_memberSettings->dwolla_id = $values['dwolla_id'];

                $this->_memberSettings->save();

                $this->view->paymentform = $this->formPayment();
                $this->view->paymentform->populate($this->_memberSettings->toArray());

                $this->view->save = 1;
            } else {
                $this->view->paymentform = $form;
                $this->view->error = 1;
            }
        } else {
            $form = $this->formPayment();
            $form->populate($this->_memberSettings->toArray());

            $this->view->paymentform = $form;
        }
    }

    public function deleteAction()
    {
        $this->_memberSettings->is_deleted = 1;
        $this->_memberSettings->is_active = 0;
        $this->_memberSettings->save();

        $tableProject = new Default_Model_Project();
        $tableProject->setAllProjectsForMemberDeleted($this->_memberId);

        $auth = Zend_Auth::getInstance();
        $auth->clearIdentity();

        $session = new Zend_Session_Namespace();
        $session->unsetAll();
        Zend_Session::forgetMe();
        Zend_Session::destroy();

        $config = Zend_Registry::get('config');
        $cookieName = $config->settings->session->remember_me->name;
        $cookieData = $this->_request->getCookie($cookieName, null);
        if ($cookieData) {
            $cookieData = unserialize($cookieData);
            $remember_me_seconds = $config->settings->session->remember_me->cookie_lifetime;
            $domain = Local_Tools_ParseDomain::get_domain($this->getRequest()->getHttpHost());
            $cookieExpire = time() - $remember_me_seconds;

            setcookie($cookieName, null, $cookieExpire, '/', $domain, null, true);

            //TODO: Remove Cookie from database
            $modelAuthorization = new Default_Model_Authorization();
            $modelAuthorization->removeAllCookieInformation('member_id', $cookieData['mi']);
        }

        // ppload
        // Delete owner and related data
        $pploadApi = new Ppload_Api(array(
            'apiUri'   => PPLOAD_API_URI,
            'clientId' => PPLOAD_CLIENT_ID,
            'secret'   => PPLOAD_SECRET
        ));
        $ownerResponse = $pploadApi->deleteOwner($this->_memberId);
    }

    public function githubtokenAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer('partials/github');

        $modelGithubOauth = new Default_Model_Oauth_Github(
            Zend_Registry::get('db'),
            'member',
            Zend_Registry::get('config')->third_party->github);
        $modelGithubOauth->authStart('/settings');
    }

    public function addemailAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer('partials/email');

        $filterInput = $this->createFilter();

        if ($filterInput->hasInvalid()) {
            $this->view->messages = $filterInput->getMessages();

            return;
        }

        $resultSet = $this->saveEmail($filterInput);

        $this->sendConfirmationMail($resultSet->toArray());

        $this->view->messages =
            array('user_email' => array('success' => 'Your email was saved. Please check your email account for verification email.'));
    }

    /**
     * @return Zend_Filter_Input
     * @throws Zend_Validate_Exception
     */
    protected function createFilter()
    {
        $mailValidCheck = new Zend_Validate_EmailAddress();
        $mailValidCheck->setOptions(array('domain' => true));

        $mailExistCheck = new Zend_Validate_Db_NoRecordExists(array(
            'table'   => 'member_email',
            'field'   => 'email_address',
            'exclude' => array('field' => 'email_deleted', 'value' => 1)
        ));
        $mailExistCheck->setMessage('RegisterFormEmailErrAlreadyRegistered',
            Zend_Validate_Db_NoRecordExists::ERROR_RECORD_FOUND);

        // Filter-Parameter
        $filterInput = new Zend_Filter_Input(array('*' => 'StringTrim', 'user_email' => 'StripTags'), array(
            'user_email' => array(
                $mailValidCheck,
                $mailExistCheck,
                'presence' => 'required'
            )
        ), $this->getAllParams());

        return $filterInput;
    }

    /**
     * @param Zend_Filter_Input $filterInput
     *
     * @return Zend_Db_Table_Row_Abstract
     * @throws Exception
     */
    protected function saveEmail($filterInput)
    {
        $data = array();
        $data['email_member_id'] = $this->_authMember->member_id;
        $data['email_address'] = $filterInput->getEscaped('user_email');
        $data['email_hash'] = md5($filterInput->getEscaped('user_email'));
        $data['email_verification_value'] =
            Default_Model_MemberEmail::getVerificationValue($this->_authMember->username,
                $filterInput->getEscaped('user_email'));
        $modelMemberEmail = new Default_Model_DbTable_MemberEmail();

        return $modelMemberEmail->save($data);
    }

    /**
     * @param array $data
     * @throws Zend_Exception
     */
    protected function sendConfirmationMail($data)
    {
        $config = Zend_Registry::get('config');
        $defaultFrom = $config->resources->mail->defaultFrom->email;

        $confirmMail = new Default_Plugin_SendMail('tpl_verify_email');
        $confirmMail->setTemplateVar('servername', $this->getServerName());
        $confirmMail->setTemplateVar('username', $this->_authMember->username);
        $confirmMail->setTemplateVar('email_address', $data['email_address']);
        $confirmMail->setTemplateVar('verificationlinktext',
            '<a href="https://' . $this->getServerName() . '/settings/verification/v/' . $data['email_verification_value']
            . '">Click here to verify your email address</a>');
        $confirmMail->setTemplateVar('verificationlink',
            '<a href="https://' . $this->getServerName() . '/settings/verification/v/' . $data['email_verification_value']
            . '">https://' . $this->getServerName() . '/settings/verification/v/' . $data['email_verification_value'] . '</a>');
        $confirmMail->setTemplateVar('verificationurl',
            'https://' . $this->getServerName() . '/settings/verification/v/' . $data['email_verification_value']);
        $confirmMail->setReceiverMail($data['email_address']);
        $confirmMail->setFromMail($defaultFrom);
        $confirmMail->send();
    }

    /**
     * @return mixed
     */
    protected function getServerName()
    {
        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();

        return $request->getHttpHost();
    }

    public function removeemailAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer('partials/email');

        $emailId = (int)$this->getParam('i');

        $modelEmail = new Default_Model_DbTable_MemberEmail();

        $result = $modelEmail->delete($emailId);

        $this->view->messages = array('user_email' => array('success' => 'Your email was removed.'));
    }

    public function setdefaultemailAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer('partials/email');

        $emailId = (int)$this->getParam('i');

        $modelEmail = new Default_Model_MemberEmail();
        $result = $modelEmail->setDefaultEmail($emailId, $this->_authMember->member_id);

        if (true === $result) {
            try {
                $id_server = new Default_Model_Ocs_OAuth();
                $id_server->updateMailForUser($this->_authMember->member_id);
                Zend_Registry::get('logger')->debug(__METHOD__ . ' - oauth : ' . implode(PHP_EOL . " - ", $id_server->getMessages()));
                Zend_Registry::get('logger')->debug(__METHOD__ . ' - oauth : ' . print_r($id_server->getMessages(), true));
            } catch (Exception $e) {
                Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
            try {
                $ldap_server = new Default_Model_Ocs_Ldap();
                $ldap_server->updateMail($this->_authMember->member_id);
                Zend_Registry::get('logger')->debug(__METHOD__ . ' - ldap : ' . implode(PHP_EOL . " - ", $ldap_server->getMessages()));
            } catch (Exception $e) {
                Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
            try {
                $openCode = new Default_Model_Ocs_Gitlab();
                $openCode->updateMail($this->_authMember->member_id);
                Zend_Registry::get('logger')->debug(__METHOD__ . ' - opencode : ' . implode(PHP_EOL . " - ", $openCode->getMessages()));
            } catch (Exception $e) {
                Zend_Registry::get('logger')->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
        }
    }

    public function resendverificationAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer('partials/email');

        $emailId = (int)$this->getParam('i');

        $modelEmail = new Default_Model_DbTable_MemberEmail();
        $data = $modelEmail->find($emailId)->current();
        $data->email_verification_value = md5($data->email_address . $this->_authMember->username . time());
        $data->save();
        $this->sendConfirmationMail($data);

        $this->view->messages =
            array('user_email' => array('success' => 'New verification mail was send. Please check your email account.'));
    }

    public function verificationAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        // Filter-Parameter
        $filterInput = new Zend_Filter_Input(array('*' => 'StringTrim', 'v' => 'StripTags'), array(
            'v' => array(
                'presence' => 'required'
            )
        ), $this->getAllParams());

        if ($filterInput->hasInvalid()) {
            $this->_helper->flashMessenger->addMessage('<p class="text-error">There was an error verifying your email. </p>');
            $this->forward('index');

            return;
        }

        $modelEmail = new Default_Model_MemberEmail();
        $result = $modelEmail->verificationEmail($filterInput->getEscaped('v'));

        if ($result == 1) {
            $this->_helper->flashMessenger->addMessage('<p class="text-success">Your email was successfully verified. </p>');
        } else {
            $this->_helper->flashMessenger->addMessage('<p class="text-danger">There was an error verifying your email.</p>');
        }
        $this->forward('index');
    }

}
