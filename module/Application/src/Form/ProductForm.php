<?php

namespace Application\Form;

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

use Application\Form\Validators\IpAddressRange;
use Application\Form\Validators\LocalHostname;
use Application\Form\Validators\WallpaperSites;
use Application\Model\Repository\ImageRepository;
use Application\Model\Repository\MemberExternalIdRepository;
use Application\Model\Repository\ProjectCategoryRepository;
use Application\Model\Repository\TagsRepository;
use Application\Model\Service\MemberService;
use Application\Model\Service\Ocs\Gitlab;
use Application\Model\Service\ProjectCategoryService;
use Exception;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Element\Url;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Laminas\Validator\Regex;
use Library\Filter\HtmlPurify;

class ProductForm extends Form
{

    protected $viewScripts;
    protected $imageRepository;
    protected $tagsRepository;
    protected $gitlabService;
    protected $memberService;
    protected $memberExternalIdRepository;
    protected $projectCategoryService;
    protected $projectCategoryRepository;
    protected $onlineGalleryImageSources = array();
    private $member_id = null;
    private $onlineGalleryPictureSources = array();
    private $numberOfUploadGalleryPictures = 1;
    private $maxGalleryPics = 5;

    public function __construct(
        ImageRepository $imageRepository,
        TagsRepository $tagsRepository,
        Gitlab $gitlabService,
        MemberService $memberService,
        MemberExternalIdRepository $memberExternalIdRepository,
        ProjectCategoryService $projectCategoryService,
        ProjectCategoryRepository $projectCategoryRepository,
        $options = null
    ) {
        $this->imageRepository = $imageRepository;
        $this->tagsRepository = $tagsRepository;
        $this->gitlabService = $gitlabService;
        $this->memberService = $memberService;
        $this->memberExternalIdRepository = $memberExternalIdRepository;
        $this->projectCategoryService = $projectCategoryService;
        $this->projectCategoryRepository = $projectCategoryRepository;

        $this->viewScripts = array();

        // Define form name
        parent::__construct('product-form');

        if (isset($options['pictures'])) {
            $this->onlineGalleryImageSources = $options['pictures'];
        }
        if (isset($options['member_id'])) {
            $this->member_id = $options['member_id'];
        }

        // not necessary when called via factory ....
        //$this->init();
    }

    public function isValid()
    {
        $valid = parent::isValid();

        return $valid;
    }

    public function init()
    {
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->add($this->getTitleElement());
        $this->add($this->getCategoryIdElement());
        $this->add($this->getDescriptionElement());
        $this->add($this->getVersionElement());
        $this->add($this->getSmallImageElement());
        $this->add($this->getImageUploadElement());
        $this->add($this->getGalleryElement());
        $this->add($this->getEmbedCodeElement());
        $this->add($this->getProjectHomepageElement());
        $this->add($this->getSourceElement());
        $this->add($this->getFacebookElement());
        $this->add($this->getTwitterElement());
        $this->add($this->getGoogleElement());
        $this->add($this->getTagElement());
        $this->add($this->getTagUserElement());
        $this->add($this->getHiddenProjectId());
        $this->add($this->getSubmitElement());
        $this->add($this->getCancelElement());
        $this->add($this->getLicenseIdElement());
        //$this->add($this->getIsOriginal());
        $this->add($this->getIsOriginalOrModification());
        $this->add($this->getIsGitlab());
        $this->add($this->getGitlabProjectId());
        $this->add($this->getShowGitlabProjectIssues());
        $this->add($this->getUseGitlabProjectReadme());

        $this->addInputFilter();
    }

    private function getTitleElement()
    {
        $this->viewScripts['title'] = 'viewscripts/input_title.phtml';

        return [
            'type'    => 'text',
            'name'    => 'title',
            'options' => [
                'label'    => 'Product Name (4 letters min.)',  // Text label
                'required' => true,
            ],
        ];
    }

    private function getCategoryIdElement()
    {
        $this->viewScripts['project_category_id'] = 'viewscripts/input_cat_id.phtml';

        return new Hidden(
            'project_category_id', array(
                                     'required' => true,
                                 )
        );
    }

    private function getDescriptionElement()
    {
        $this->viewScripts['description'] = 'viewscripts/input_description.phtml';

        return new Textarea(
            'description', array(
                             'cols'     => 30,
                             'rows'     => 9,
                             'required' => true,
                             'label'    => '* Product Description',
                         )
        );
    }

    private function getVersionElement()
    {
        $this->viewScripts['version'] = 'viewscripts/input_version.phtml';

        return new Text(
            'version', array(
                         'label' => 'Version',
                     )
        );
    }

    private function getSmallImageElement()
    {
        $this->viewScripts['image_small'] = 'viewscripts/input_image_small.phtml';

        return new Hidden('image_small');
    }

    private function getImageUploadElement()
    {
        $this->viewScripts['image_small_upload'] = 'viewscripts/input_image_small_upload.phtml';

        return new File(
            'image_small_upload', array(
                                    'valueDisabled'   => true,
                                    'maxFileSize'     => '2MB',
                                    'label'           => 'Product Logo (min. 20x20, max. 2000x2000, max. 2MB)',
                                    'transferAdapter' => 'http',
                                )
        );
    }

    private function getGalleryElement()
    {
//        $gallerySubform = new GallerySubForm($this->imageRepository, array('pictures' => $this->onlineGalleryImageSources));
//        $gallerySubform->setMaxGalleryPics(5);
//
//        return $gallerySubform;

        //SubForm for already uploaded - online - pictures
        $gallery = new Fieldset('gallery');

        $i = 0;
        foreach ($this->onlineGalleryPictureSources as $onlineGalleryImageSrc) {
            $onlineGalleryPicture = new Hidden(
                '' . $i, array('id' => 'gallery_picture_online_' . $i)
            );
            $onlineGalleryPicture->setValue($onlineGalleryImageSrc);
            $gallery->add($onlineGalleryPicture);
            $i++;
        }

        $uploadGalleryPicture = new File('upload_picture');
        $gallery->add($uploadGalleryPicture);

        return $gallery;
    }

    private function getEmbedCodeElement()
    {
        $this->viewScripts['embed_code'] = 'viewscripts/input_embedcode.phtml';

        return new Textarea(
            'embed_code', array(
                            'cols'     => 30,
                            'rows'     => 3,
                            'required' => false,
                            'label'    => 'HTML or Embed Media Code (Youtube, Vimeo, Soundcloud etc.)',

                        )
        );

    }

    private function getProjectHomepageElement()
    {
        $this->viewScripts['link_1'] = 'viewscripts/input_link.phtml';

        return new Url(
            'link_1', array(
                        'label' => 'Link to your product homepage',
                    )
        );

    }

    private function getSourceElement()
    {
        $this->viewScripts['source_url'] = 'viewscripts/input_source_url.phtml';

        return new Url(
            'source_url', array(
                            'label' => 'Link to Source/Code *(needed for plings depending on product category). Link to original if your product is unmodified and based on someone elses work. If it is modified, you need to link to a repository with FULLY EXTRACTED source code, NOT an archive. If Link is not valid, product can be excluded or removed. ',
                        )
        );
    }

    private function getFacebookElement()
    {
        $this->viewScripts['facebook_code'] = 'viewscripts/input_facebook.phtml';

        return new Url(
            'facebook_code', array(
                               'label' => 'Your Product on Facebook',
                           )
        );

    }

    private function getTwitterElement()
    {
        $this->viewScripts['twitter_code'] = 'viewscripts/input_twitter.phtml';

        return new Url(
            'twitter_code', array(
                              'label' => 'Your Product on Twitter',
                          )
        );

    }

    private function getGoogleElement()
    {
        $this->viewScripts['google_code'] = 'viewscripts/input_google.phtml';

        return new Url(
            'google_code', array(
                             'label' => 'Your Product on Google',
                         )
        );
    }

    private function getTagElement()
    {
        $this->viewScripts['tags'] = 'viewscripts/input_tags_multiselect.phtml';

        return new Select(
            'tags', array(
                      'registerInArrayValidator' => false,
                      'required'                 => false,
                      'label'                    => 'Tags',
                  )
        );

    }

    private function getTagUserElement()
    {
        $this->viewScripts['tagsuser'] = 'viewscripts/input_tags_user.phtml';

        return new Select(
            'tagsuser', array(
                          'registerInArrayValidator'  => false,
                          'disable_inarray_validator' => true,
                          'required'                  => false,
                          'label'                     => 'Tags',
                      )
        );

    }

    private function getHiddenProjectId()
    {
        $this->viewScripts['project_id'] = 'viewscripts/input_hidden.phtml';

        return new Hidden(
            'project_id', array(
                            array('Digits'),
                        )
        );

    }

    private function getSubmitElement()
    {
        return [
            'type'    => 'button',
            'name'    => 'preview',
            'options' => [
                'label' => 'Preview',
                'type'  => 'submit',
            ],
        ];
    }

    private function getCancelElement()
    {
        return [
            'type'    => 'button',
            'name'    => 'cancel',
            'options' => [
                'label' => 'Cancel',
                'type'  => 'submit',
            ],
        ];
    }

    private function getLicenseIdElement()
    {

        $this->viewScripts['license_tag_id'] = 'viewscripts/input_select_license.phtml';

        $element = new Select('license_tag_id', array('multiple' => false));
        $element->setName("license_tag_id")
                ->setLabel(' License *a valid License is needed for receiving plings. Please pay attention to original license, if based on someone elses work. ');

        $tagTable = $this->tagsRepository;
        $options = $tagTable->fetchLicenseTagsForSelect();
        $element->setValueOptions($options);

        return $element;
    }

    private function getIsOriginalOrModification()
    {
        $element = new Select('is_original_or_modification', array('multiple' => false));
        $element->setName("is_original_or_modification")->setLabel(' Product Original or Modification ')
                ->setAttribute('class', 'form-control product_select_original')
                ->setAttribute('style', 'width: 175px;margin-bottom: 10px;');


        $option = array();
        $option[0] = "";
        $option[1] = "Original";
        $option[2] = "Mod";

        return $element->setValueOptions($option);
    }

    private function getIsGitlab()
    {
        $element = new Checkbox('is_gitlab_project');
        $element->setAttribute('id', 'is_gitlab_project');

        return $element->setOptions(
            array(
                'label'              => ' Git-Project ',
                'use_hidden_element' => true,
                'checked_value'      => 1,
                'required'           => false,
                'unchecked_value'    => 0,
            )
        );
    }

    private function getGitlabProjectId()
    {
        $element = new Select(
            'gitlab_project_id', array(
                                   'multiple' => false,
                                   'label'    => 'gitlab-ProjectId',
                                   'required' => false,
                                   'id'       => 'gitlab_project_id',
                                   'class'    => 'form-control gitlab_project_id',
                               )
        );
        $element->setAttribute('class', 'form-control gitlab_project_id');
        $element->setAttribute('id', 'gitlab_project_id');
        //$element->setIsArray(true);

        $gitlab = $this->gitlabService;

        $optionArray = array();

        if ($this->member_id) {

            $memberTable = $this->memberService;
            $member = $memberTable->fetchMemberData($this->member_id);
            $gitlab_user_id = null;
            if (!empty($member->gitlab_user_id)) {
                //get gitlab user id from db
                $gitlab_user_id = $member->gitlab_user_id;
            } else {
                //get gitlab user id from gitlab API and save in DB
                $gitUser = (array)$gitlab->getUserWithName($member->username);

                if ($gitUser && !empty($gitUser)) {
                    $gitlab_user_id = $gitUser['id'];
                    $memberTableExternal = $this->memberExternalIdRepository;
                    $memberTableExternal->updateGitlabUserId($this->member_id, $gitlab_user_id);
                }
            }

            if ($gitlab_user_id && null != $gitlab_user_id) {
                try {
                    //now get his projects
                    $gitProjects = $gitlab->getUserProjects($gitlab_user_id);

                    if (count($gitProjects) > 0) {
                        $optionArray['0'] = '';
                        foreach ($gitProjects as $proj) {
                            $optionArray[$proj->id] = $proj->name;
                        }
                    }
                } catch (Exception $exc) {
                    //Error getting USerProjects,
                }

            }
        }

        return $element->setValueOptions($optionArray);
    }

    private function getShowGitlabProjectIssues()
    {
        $element = new Checkbox('show_gitlab_project_issues');
        $element->setAttribute('id', 'show_gitlab_project_issues');

        return $element->setOptions(
            array(
                'label'              => ' Git-Issues ',
                'use_hidden_element' => true,
                'checked_value'      => 1,
                'required'           => false,
                'unchecked_value'    => 0,
                'id'                 => 'show_gitlab_project_issues',
            )
        );
    }

    private function getUseGitlabProjectReadme()
    {
        $element = new Checkbox('use_gitlab_project_readme');
        $element->setAttribute('id', 'use_gitlab_project_readme');

        return $element->setOptions(
            array(
                'label'              => 'README.md',
                'use_hidden_element' => true,
                'checked_value'      => 1,
                'unchecked_value'    => 0,
                'required'           => false,
                'id'                 => 'use_gitlab_project_readme',
            )
        );
    }

    /**
     * This method creates input filter (used for form filtering/validation).
     */
    public function addInputFilter()
    {
        // Create default input filter with all fields and everything is allowed.
        $inputFilter = $this->getInputFilter();

        $filter = $inputFilter->get('tags');
        $filter->setRequired(false);

        $filter = $inputFilter->get('is_gitlab_project');
        $filter->setRequired(false);

        $filter = $inputFilter->get('gitlab_project_id');
        $filter->setRequired(false);

        $filter = $inputFilter->get('show_gitlab_project_issues');
        $filter->setRequired(false);

        $filter = $inputFilter->get('use_gitlab_project_readme');
        $filter->setRequired(false);

        // Add input for "license_tag_id" field
        $inputFilter->add(
            [
                'name'       => 'license_tag_id',
                'required'   => false,
                'filters'    => [
                    ['name' => 'Digits'],
                ],
                'validators' => [
                    ['name' => 'Digits'],
                ],
            ]
        );

        // Add input for "tagsuser" field
        $inputFilter->add(
            [
                'name'                     => 'tagsuser',
                'required'                 => false,
                'registerInArrayValidator' => false,
                'filters'                  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripNewlines'],
                ],
            ]
        );

        $inputFilter->add(
            [
                'name'       => 'title',
                'required'   => true,
                'filters'    => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripNewlines'],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 4,
                            'max' => 60,
                        ],
                    ],
                    [
                        'name'    => 'regex',
                        'options' => [
                            'pattern'  => "/^[ \/\[\]\.\-_A-Za-z0-9']{1,}$/iu",
                            'messages' => array(
                                Regex::NOT_MATCH => "'%value%' is not valid. Please use only alphanumeric characters or /, [, ], -, _, ' ",
                            ),
                        ],
                    ],
                ],
            ]
        );

        $inputFilter->add(
            [
                'name'       => 'version',
                'required'   => false,
                'filters'    => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripNewlines'],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'max' => 50,
                        ],
                    ],
                ],
            ]
        );


        $validatorCategory = new Validators\Category($this->projectCategoryRepository);
        $inputFilter->add(
            [
                'name'       => 'project_category_id',
                'required'   => true,
                'filters'    => [
                    ['name' => 'Digits'],
                ],
                'validators' => [
                    [
                        'name' => 'Digits',
                    ],
                    $validatorCategory,
                ],
            ]
        );

        $inputFilter->add(
            [
                'name'     => 'description',
                'required' => true,
                'filters'  => [
                    [
                        'name'    => HtmlPurify::class,
                        'options' => [
                            'schema' => HtmlPurify::ALLOW_NOTHING,
                        ],
                    ],
                ],
            ]
        );

        $inputFilter->add(
            [
                'name'     => 'embed_code',
                'required' => false,
                'filters'  => [
                    [
                        'name'    => HtmlPurify::class,
                        'options' => [
                            'schema' => HtmlPurify::ALLOW_EMBED,
                        ],
                    ],
                ],
            ]
        );

        $inputFilter->add(
            [
                'name'     => 'project_id',
                'required' => false,
                'filters'  => [
                    ['name' => 'Digits'],
                ],
            ]
        );

        $inputFilter->add(
            [
                'name'       => 'google_code',
                'required'   => false,
                'validators' => [],
                'filters'    => [
                    ['name' => 'StringTrim'],
                ],
            ]
        );

        $inputFilter->add(
            [
                'name'       => 'twitter_code',
                'required'   => false,
                'validators' => [],
                'filters'    => [
                    ['name' => 'StringTrim'],
                ],
            ]
        );

        $inputFilter->add(
            [
                'name'       => 'facebook_code',
                'required'   => false,
                'validators' => [],
                'filters'    => [
                    ['name' => 'StringTrim'],
                ],
            ]
        );

        $inputFilter->add(
            [
                'name'       => 'source_url',
                'required'   => false,
                'validators' => [
                    [
                        'name'                   => LocalHostname::class,
                        'break_chain_on_failure' => true,
                        'priority'               => 30,
                    ],
                    [
                        'name'                   => IpAddressRange::class,
                        'break_chain_on_failure' => true,
                        'priority'               => 20,
                    ],
                    [
                        'name'                   => WallpaperSites::class,
                        'break_chain_on_failure' => true,
                        'priority'               => 10,
                    ],
                ],
                'filters'    => [
                    ['name' => 'StringTrim'],
                ],
            ]
        );

        $inputFilter->add(
            [
                'name'       => 'link_1',
                'required'   => false,
                'validators' => [],
                'filters'    => [
                    ['name' => 'StringTrim'],
                ],
            ]
        );

        $inputFilter->add(
            [
                'name'       => 'online_picture',
                'required'   => false,
                'filters'    => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripNewlines'],
                ],
                'validators' => [
                    [
                        'name'    => \Application\Form\Validators\OnlinePicture::class,
                        'options' => [
                            'pattern'  => "/^[\/\.A-Za-z0-9]*$/iu",
                            'messages' => array(
                                Regex::NOT_MATCH => "'%value%' is not valid. Please use only alphanumeric characters or /, [, ], -, _, ' ",
                            ),
                        ],
                    ],
                ],
            ]
        );

        $modelImage = $this->imageRepository;
        //@formatter:off
        $imageInput = new \Laminas\InputFilter\FileInput('image_small_upload');
        $imageInput->setRequired(false);
        $imageInput->getValidatorChain()
//                   ->attachByName('FileCount', ['max' => 1], true)
                   ->attachByName('FileSize', ['min' => '500B', 'max' => '2MB'], true)
                   ->attachByName('FileImageSize', [
                                        'minwidth'  => 20,
                                        'maxwidth'  => 2000,
                                        'minheight' => 20,
                                        'maxheight' => 2000,
                                    ], true)
                   ->attachByName('FileExtension', ['extension' => $modelImage->getAllowedFileExtension()], true)
                   ->attachByName('FileMimeType', ['mimetype' => $modelImage->getAllowedMimeTypes()], true);
        $imageInput->getFilterChain()
                   ->attachByName('FileRenameUpload', [
                                        'target'    => IMAGES_UPLOAD_PATH . '/tmp/',
                                        'overwrite' => true,
                                        'randomize' => true,
                                    ]);
        $inputFilter->add($imageInput);

        $galleryInput = new \Laminas\InputFilter\FileInput('upload_picture');
        $galleryInput->setRequired(false);
        $galleryInput->getValidatorChain()
//                     ->attachByName('FileCount', ['max' => $this->maxGalleryPics], true)
                     ->attachByName('FileSize', ['max' => '5MB'], true)
                     ->attachByName('FileExtension', ['extension' => $modelImage->getAllowedFileExtension()], true)
                     ->attachByName('FileMimeType', ['mimetype' => $modelImage->getAllowedMimeTypes()], true);
        $galleryInput->getFilterChain()
                     ->attachByName('FileRenameUpload', [
                                  'target'    => IMAGES_UPLOAD_PATH . '/tmp/',
                                  'overwrite' => true,
                                  'randomize' => true,
                              ]);
        $inputFilter->add($galleryInput);
        //@formatter:on

        $inputFilter->add(
            [
                'name'     => 'gitlab_project_id',
                'required' => false,
                'filters'  => [
                    ['name' => 'Digits'],
                ],
            ]
        );

    }

    public function getViewScriptsArray()
    {
        return $this->viewScripts;
    }

    /**
     * @return mixed|null
     */
    public function getMemberId()
    {
        return $this->member_id;
    }

    /**
     * @param mixed|null $member_id
     */
    public function setMemberId($member_id)
    {
        $this->member_id = $member_id;
    }

    /**
     * @return int
     */
    public function getMaxGalleryPics()
    {
        return $this->maxGalleryPics;
    }

    /**
     * @param int $maxGalleryPics
     */
    public function setMaxGalleryPics($maxGalleryPics)
    {
        $this->maxGalleryPics = $maxGalleryPics;
    }

    /**
     * @return array|mixed
     */
    public function getOnlineGalleryImageSources()
    {
        return $this->onlineGalleryImageSources;
    }

    /**
     * @param $sources array
     */
    public function setOnlineGalleryImageSources($sources)
    {
        $this->onlineGalleryImageSources = $sources;

    }

    private function getIsOriginal()
    {
        $element = new Checkbox('is_original');

        return $element->setOptions(
            array(
                'label'              => ' Product original ',
                'use_hidden_element' => false,
                'checked_value'      => 1,
                'unchecked_value'    => 0,
            )
        );
    }

}