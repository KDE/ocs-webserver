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
class Default_Form_Product extends Zend_Form
{

    protected $onlineGalleryImageSources = array();
    private $member_id = null;

    public function __construct($options = null)
    {
        if (isset($options['pictures'])) {
            $this->onlineGalleryImageSources = $options['pictures'];
        }
        if (isset($options['member_id'])) {
            $this->member_id = $options['member_id'];
        }

        parent::__construct($options);
    }

    /**
     * @param $sources array
     */
    public function setOnlineGalleryImageSources($sources)
    {
        $this->onlineGalleryImageSources = $sources;
        $this->init();
    }

    public function init()
    {
        $this->setAction('');
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->addPrefixPath('Default_Form_Element', APPLICATION_PATH . '/modules/default/forms/elements/', Zend_Form::ELEMENT);

        $this->addElement($this->getTitleElement())
             ->addElement($this->getCategoryIdElement())
             ->addElement($this->getDescriptionElement())
             ->addElement($this->getVersionElement())
             ->addElement($this->getSmallImageElement())
             ->addElement($this->getImageUploadElement())
             ->addSubForm($this->getGalleryElement(), 'gallery')
             ->addElement($this->getEmbedCodeElement())
             ->addElement($this->getProjectHomepageElement())
             ->addElement($this->getSourceElement())
             ->addElement($this->getFacebookElement())
             ->addElement($this->getTwitterElement())
             ->addElement($this->getGoogleElement())
             ->addElement($this->getTagElement())
             ->addElement($this->getTagUserElement())
             ->addElement($this->getHiddenProjectId())
             ->addElement($this->getSubmitElement())
             ->addElement($this->getCancelElement())
             ->addElement($this->getLicenseIdElement())
             ->addElement($this->getIsOriginal())
             ->addElement($this->getIsOriginalOrModification())
             ->addElement($this->getIsGitlab())
             ->addElement($this->getGitlabProjectId())
             ->addElement($this->getShowGitlabProjectIssues())
             ->addElement($this->getUseGitlabProjectReadme())
        ;
    }

    private function getTitleElement()
    {
//        $validatorRegEx = new Zend_Validate_Regex(array('pattern' => "/^[ \/\[\]\.\-_A-Za-z0-9'\pL]{1,}$/iu")); // with unicode character class
        $validatorRegEx = new Zend_Validate_Regex(array('pattern' => "/^[ \/\[\]\.\-_A-Za-z0-9']{1,}$/iu"));
        $validatorRegEx->setMessages(array(Zend_Validate_Regex::NOT_MATCH => "'%value%' is not valid. Please use only alphanumeric characters or /, [, ], -, _, ' "));

        return $this->createElement('text', 'title')
                    ->setRequired(true)
                    ->addValidators(array(
                        array('StringLength', true, array(4, 60)),
                        $validatorRegEx
                    ))
                    ->setFilters(array('StringTrim'))
                    ->setDecorators(array(
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'product/viewscripts/input_title.phtml',
                        'placement'  => false
                    )
                )
            ))
            ;
    }

    private function getCategoryIdElement()
    {

        $validatorCategory = new Default_Form_Validator_Category();

        return $this->createElement('number', 'project_category_id', array())
                    ->setRequired(true)
                    ->addValidator('Digits')
                    ->addValidator($validatorCategory)
                    ->addFilter('Digits')
                    ->setDecorators(array(
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'product/viewscripts/input_cat_id.phtml',
                        'placement'  => false
                    )
                )
            ))
            ;
    }

    private function getDescriptionElement()
    {
        return $this->createElement('textarea', 'description', array('cols' => 30, 'rows' => 9))
                    ->setRequired(true)
                    ->setFilters(array('StringTrim'))
                    ->setDecorators(array(
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'product/viewscripts/input_description.phtml',
                        'placement'  => false
                    )
                )
            ))
            ;
    }

    private function getVersionElement()
    {
        return $this->createElement('text', 'version')
                    ->setRequired(false)
                    ->addValidators(array(
                        array('StringLength', false, array(0, 50)),
                    ))
                    ->setFilters(array('StringTrim'))
                    ->setDecorators(array(
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'product/viewscripts/input_version.phtml',
                        'placement'  => false
                    )
                )
            ))
            ;
    }

    private function getSmallImageElement()
    {
        return $this->createElement('hidden', 'image_small')
                    ->setFilters(array('StringTrim'))
                    ->addValidators(array(
                        array(
                            'Regex',
                            false,
                            array('/^[A-Za-z0-9.\/_-]{1,}$/i')
                        )
                    ))
                    ->setDecorators(array(
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'product/viewscripts/input_image_small.phtml',
                        'placement'  => false
                    )
                )
            ))
            ;
    }

    private function getImageUploadElement()
    {
        $modelImage = new Default_Model_DbTable_Image();

        return $this->createElement('file', 'image_small_upload')
                    ->setDisableLoadDefaultDecorators(true)
                    ->setValueDisabled(true)
                    ->setTransferAdapter(new Local_File_Transfer_Adapter_Http())
                    ->setRequired(false)
                    // Has been removed. It affects to all subforms. Please use FilesSize Validator, if it is important to check.
                    //->setMaxFileSize(2097152)
                    ->addValidator('Count', false, 1)
                    ->addValidator('Size', false, array('min' => '500B', 'max' => '2MB'))
                    ->addValidator('Extension', false, $modelImage->getAllowedFileExtension())
                    ->addValidator('ImageSize', false, array(
                    'minwidth'  => 20,
                    'maxwidth'  => 2000,
                    'minheight' => 20,
                    'maxheight' => 2000
                    ))
                    ->addValidator('MimeType', false, $modelImage->getAllowedMimeTypes())
                    ->setDecorators(array(
                array('File' => new Local_Form_Decorator_File()),
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'product/viewscripts/input_image_small_upload.phtml',
                        'placement'  => false
                    )
                )

            ))
            ;
    }

    private function getGalleryElement()
    {
        $gallerySubform = new Default_Form_GallerySubForm(array('pictures' => $this->onlineGalleryImageSources));
        $gallerySubform->setMaxGalleryPics(5);

        return $gallerySubform;
    }

    private function getEmbedCodeElement()
    {
        return $this->createElement('textarea', 'embed_code', array('cols' => 30, 'rows' => 3))
                    ->setRequired(false)
                    ->setFilters(array('StringTrim'))
                    ->setDecorators(array(
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'product/viewscripts/input_embedcode.phtml',
                        'placement'  => false
                    )
                )
            ))
            ;
    }

    private function getProjectHomepageElement()
    {
        return $this->createElement('text', 'link_1', array())
                    ->setRequired(false)
                    ->setFilters(array('StringTrim'))
                    ->addPrefixPath('Local_Validate', 'Local/Validate', Zend_Form_Element::VALIDATE)
                    ->addValidator('PartialUrl')
                    ->setDecorators(array(
                        array(
                            'ViewScript',
                            array(
                                'viewScript' => 'product/viewscripts/input_link.phtml',
                                'placement'  => false
                            )
                        )
                    ))
            ;
    }

    private function getSourceElement()
    {
        return $this->createElement('text', 'source_url', array())
                    ->setRequired(false)
                    ->setFilters(array('StringTrim'))
                    ->addPrefixPath('Local_Validate', 'Local/Validate', Zend_Form_Element::VALIDATE)
                    ->addValidator('PartialUrl')
                    ->setDecorators(array(
                        array(
                            'ViewScript',
                            array(
                                'viewScript' => 'product/viewscripts/input_source_url.phtml',
                                'placement'  => false
                            )
                        )
                    ))
            ;
    }

    private function getFacebookElement()
    {
        return $this->createElement('text', 'facebook_code', array())
                    ->setRequired(false)
                    ->setFilters(array('StringTrim'))
                    ->addPrefixPath('Local_Validate', 'Local/Validate', Zend_Form_Element::VALIDATE)
                    ->addValidator('PartialUrl')
                    ->setDecorators(array(
                        array(
                            'ViewScript',
                            array(
                                'viewScript' => 'product/viewscripts/input_facebook.phtml',
                                'placement'  => false
                            )
                        )
                    ))
            ;
    }

    private function getTwitterElement()
    {
        return $this->createElement('text', 'twitter_code', array())
                    ->setRequired(false)
                    ->setFilters(array('StringTrim'))
                    ->addPrefixPath('Local_Validate', 'Local/Validate', Zend_Form_Element::VALIDATE)
                    ->addValidator('PartialUrl')
                    ->setDecorators(array(
                        array(
                            'ViewScript',
                            array(
                                'viewScript' => 'product/viewscripts/input_twitter.phtml',
                                'placement'  => false
                            )
                        )
                    ))
            ;
    }

    private function getGoogleElement()
    {
        return $this->createElement('text', 'google_code', array())
                    ->setRequired(false)
                    ->setFilters(array('StringTrim'))
                    ->addPrefixPath('Local_Validate', 'Local/Validate', Zend_Form_Element::VALIDATE)
                    ->addValidator('PartialUrl')
                    ->setDecorators(array(
                        array(
                            'ViewScript',
                            array(
                                'viewScript' => 'product/viewscripts/input_google.phtml',
                                'placement'  => false
                            )
                        )
                    ))
            ;
    }

    private function getTagElement()
    {
        $element = new Zend_Form_Element_Multiselect('tags', array('registerInArrayValidator' => false));
        //$element = new Zend_Form_Element_Select('tags', array('multiple' => false));
        return $element->setFilters(array('StringTrim'))->setDecorators(array(
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'product/viewscripts/input_tags_multiselect.phtml',
                        'placement'  => false
                    )
                )
            ))
            ;
    }

    private function getTagUserElement()
    {
        $element = new Zend_Form_Element_Multiselect('tagsuser', array('registerInArrayValidator' => false));
        //$element = new Zend_Form_Element_Select('tagsuser', array('multiple' => false));
        return $element->setFilters(array('StringTrim'))->setDecorators(array(
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'product/viewscripts/input_tags_user.phtml',
                        'placement'  => false
                    )
                )
            ))
            ;
    }

    private function getHiddenProjectId()
    {
        return $this->createElement('hidden', 'project_id')->setFilters(array('StringTrim'))->addValidators(array('Digits'))
                    ->setDecorators(array(
                        array(
                            'ViewScript',
                            array(
                                'viewScript' => 'product/viewscripts/input_hidden.phtml',
                                'placement'  => false
                            )
                        )
                    ))
            ;
    }

    private function getSubmitElement()
    {
        $submit = $this->createElement('button', 'preview')->setDecorators(array(
                'ViewHelper'
            ))
        ;
        $submit->setLabel('Preview');
        $submit->setAttrib('type', 'submit');

        return $submit;
    }

    private function getCancelElement()
    {
        $cancel = $this->createElement('button', 'cancel')->setDecorators(array(
                'ViewHelper'
            ))
        ;
        $cancel->setLabel('Cancel');
        $cancel->setAttrib('type', 'submit');

        return $cancel;
    }

    private function getLicenseIdElement()
    {

        //$element = new Zend_Form_Element_Multiselect('project_license_id', array('registerInArrayValidator' => false));
        $element = new Zend_Form_Element_Select('license_tag_id', array('multiple' => false));
        $element->setIsArray(true);

        $tagTable = new Default_Model_DbTable_Tags();
        $options = $tagTable->fetchLicenseTagsForSelect();

        return $element->setFilters(array('StringTrim'))->setMultiOptions($options)->setDecorators(array(
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'product/viewscripts/input_select_license.phtml',
                        'placement'  => false
                    )
                )
            ))
            ;
    }

    private function getIsOriginal()
    {
        $element = new Zend_Form_Element_Checkbox('is_original');

        return $element->setOptions(array(
                'label'              => ' Product original ',
                'use_hidden_element' => false,
                'checked_value'      => 1,
                'unchecked_value'    => 0
            ));
    }
    
    private function getIsOriginalOrModification()
    {
        $element = new Zend_Form_Element_Select('is_original_or_modification', array('multiple' => false));
        $element->setIsArray(true)->setName("is_original_or_modification")
                ->setLabel(' Product Original or Modification ')
                ->setAttrib('class', 'form-control product_select_original')
                ->setAttrib('style', 'width: 175px;margin-bottom: 10px;');
        
        
        $option = array();
        $option[0] = "";
        $option[1] = "Original";
        $option[2] = "Mod";

        return $element->setFilters(array('StringTrim'))->setMultiOptions($option);
    }

    private function getIsGitlab()
    {
        $element = new Zend_Form_Element_Checkbox('is_gitlab_project');

        return $element->setOptions(array(
                'label'              => ' Git-Project ',
                'use_hidden_element' => false,
                'checked_value'      => 1,
                'unchecked_value'    => 0
            ));
    }

    private function getGitlabProjectId()
    {
        $element = new Zend_Form_Element_Select('gitlab_project_id', array('multiple' => false));
        $element->setIsArray(true);

        $gitlab = new Default_Model_Ocs_Gitlab();

        $optionArray = array();

        if ($this->member_id) {

            $memberTable = new Default_Model_Member();
            $member = $memberTable->fetchMember($this->member_id);
            $gitlab_user_id = null;
            if (!empty($member->gitlab_user_id)) {
                //get gitlab user id from db
                $gitlab_user_id = $member->gitlab_user_id;
            } else {
                //get gitlab user id from gitlab API and save in DB
                $gitUser = $gitlab->getUserWithName($member->username);

                if ($gitUser && null != $gitUser) {
                    $gitlab_user_id = $gitUser['id'];
                    $memberTableExternal = new Default_Model_DbTable_MemberExternalId();
                    $memberTableExternal->updateGitlabUserId($this->member_id, $gitlab_user_id);
                }
            }

            if ($gitlab_user_id && null != $gitlab_user_id) {
                try {
                    //now get his projects
                    $gitProjects = $gitlab->getUserProjects($gitlab_user_id);

                    foreach ($gitProjects as $proj) {
                        $optionArray[$proj['id']] = $proj['name'];
                    }
                } catch (Exception $exc) {
                    //Error getting USerProjects, 
                }

            }
        }

        return $element->setFilters(array('StringTrim'))->setMultiOptions($optionArray)->setDecorators(array(
            array(
                'ViewScript',
                array(
                    'viewScript' => 'product/viewscripts/input_gitlab_project_id.phtml',
                    'placement'  => false
                )
            )
        ));
    }

    private function getShowGitlabProjectIssues()
    {
        $element = new Zend_Form_Element_Checkbox('show_gitlab_project_issues');

        return $element->setOptions(array(
                'label'              => ' Git-Issues ',
                'use_hidden_element' => false,
                'checked_value'      => 1,
                'unchecked_value'    => 0
            ));
    }

    private function getUseGitlabProjectReadme()
    {
        $element = new Zend_Form_Element_Checkbox('use_gitlab_project_readme');

        return $element->setOptions(array(
                'label'              => 'README.md',
                'use_hidden_element' => false,
                'checked_value'      => 1,
                'unchecked_value'    => 0
            ));
    }

    public function initSubCatElement($projectCatId)
    {
        if (false === isset($projectCatId)) {
            return;
        }
        $tableSubCategory = new Default_Model_SubCategory();
        $subcategories = $tableSubCategory->fetchAllSubCategories($projectCatId);
        $this->getElement('project_subcategory_id')->setMultiOptions($subcategories);
    }

    public function initSubSubCatElement($projectSubCatId)
    {
        if (false === isset($projectCatId)) {
            return;
        }
        $modelSubSubCategories = new Default_Model_SubSubCategory();
        $subSubCategories = $modelSubSubCategories->fetchAllSubCategories($projectSubCatId);
        $this->getElement('project_subcategory_id')->setMultiOptions($subSubCategories);
    }

    /**
     * Validate the form
     *
     * @param  array $data
     * @param null   $project_id
     *
     * @return bool
     * @throws Zend_Exception
     * @throws Zend_Form_Exception
     */
    public function isValid($data, $project_id = null)
    {
        $sqlExclude = 'status > ' . Default_Model_DbTable_Project::PROJECT_DELETED;
        if (isset($project_id)) {
            $db = Zend_Registry::get('db');
            $sqlExclude .= $db->quoteInto(' AND project_id <> ?', $project_id, 'INTEGER');
        }

        /*
        $checkTitleExist = new Zend_Validate_Db_NoRecordExists(array(
            'table' => 'project',
            'field' => 'title',
            'exclude' => $sqlExclude
        ));
        $checkTitleExist->setMessage('This title already exists.', Zend_Validate_Db_NoRecordExists::ERROR_RECORD_FOUND);

        $this->getElement('title')
            ->addValidator($checkTitleExist);
		*/

        return parent::isValid($data);
    }

    /**
     * Validate a partial form
     *
     * Does not check for required flags.
     *
     * @param  array $data
     *
     * @return boolean
     */
    public function isValidPartial(array $data)
    {
        return parent::isValidPartial($data); // TODO: Change the autogenerated stub
    }

    private function getAmountElement()
    {
        return $this->createElement('number', 'amount', array())
                    ->setRequired(false)
                    ->addValidator('Digits')
                    ->addFilter('Digits')
                    ->setDecorators(array(
                        array(
                            'ViewScript',
                            array(
                                'viewScript' => 'product/viewscripts/input_amount.phtml',
                                'placement'  => false
                            )
                        )
                    ))
            ;
    }

    private function getAmountPeriodElement()
    {
        return $this->createElement('radio', 'amount_period', array())
                    ->setRequired(false)
                    ->addMultiOptions(array('yearly' => 'yearly (will run continuously each year)', 'one-time' => 'one-time'))
                    ->setValue('yearly')
                    ->setSeparator('&nbsp;')
                    ->setDecorators(array(
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'product/viewscripts/input_amount_period.phtml',
                        'placement'  => false
                    )
                )
            ))
            ;
    }

    private function getBigImageElement()
    {
        return $this->createElement('hidden', 'image_big')
                    ->setFilters(array('StringTrim'))
                    ->addValidators(array(
                        array(
                            'Regex',
                            false,
                            array('/^[A-Za-z0-9.\/_-]{1,}$/i')
                        )
                    ))
                    ->setDecorators(array(
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'product/viewscripts/input_image_big.phtml',
                        'placement'  => false
                    )
                )
            ))
            ;
    }

    private function getBigImageUploadElement()
    {
        $modelImage = new Default_Model_DbTable_Image();

        return $this->createElement('file', 'image_big_upload')->setDisableLoadDefaultDecorators(true)
                    ->setTransferAdapter(new Local_File_Transfer_Adapter_Http())->setRequired(false)->setMaxFileSize(2097152)
                    ->addValidator('Count', false, 1)->addValidator('Size', false, 2097152)->addValidator('FilesSize', false, 2000000)
                    ->addValidator('Extension', false, $modelImage->getAllowedFileExtension())->addValidator('ImageSize', false, array(
                    'minwidth'  => 100,
                    'maxwidth'  => 2000,
                    'minheight' => 100,
                    'maxheight' => 1200
                ))->addValidator('MimeType', false, $modelImage->getAllowedMimeTypes())->setDecorators(array(
                array('File' => new Local_Form_Decorator_File()),
                array(
                    'ViewScript',
                    array(
                        'viewScript' => 'product/viewscripts/input_image_big_upload.phtml',
                        'placement'  => false
                    )
                )

            ))
            ;
    }

    private function getCCAttribution()
    {
        return $this->createElement('checkbox', 'by');
    }

    private function getCCComercial()
    {
        return $this->createElement('checkbox', 'nc');
    }

    private function getCCDerivateWorks()
    {
        return $this->createElement('checkbox', 'nd');
    }

    private function getCCShareAlike()
    {
        return $this->createElement('checkbox', 'sa');
    }

    private function getCCLicense()
    {
        return $this->createElement('checkbox', 'cc_license');
    }

}