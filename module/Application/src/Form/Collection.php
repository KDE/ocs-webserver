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

namespace Application\Form;

use Application\Model\Repository\ImageRepository;
use Application\Model\Repository\ProjectCategoryRepository;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;
use Laminas\Validator\File\Extension;
use Laminas\Validator\File\ImageSize;
use Laminas\Validator\File\MimeType;
use Laminas\Validator\File\Size;
use Laminas\Validator\Regex;

/**
 * Class Collection
 *
 * @package Application\Form
 */
class Collection extends Form
{

    protected $viewScripts;

    protected $onlineGalleryImageSources = array();
    protected $imageRepository;
    protected $projectCategoryRepository;
    private $member_id = null;

    public function __construct(
        ImageRepository $imageRepository,
        ProjectCategoryRepository $projectCategoryRepository,
        $options = null
    ) {
        $this->imageRepository = $imageRepository;
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

        $this->init();
    }

    public function init()
    {
        //$this->setAction('');
        $this->setAttribute('enctype', 'multipart/form-data');

        //@formatter:off
        $this->add($this->getTitleElement())
             ->add($this->getCategoryIdElement())
             ->add($this->getDescriptionElement())
             ->add($this->getSmallImageElement())
             ->add($this->getImageUploadElement())
             ->add($this->getTagUserElement())
             ->add($this->getHiddenProjectId())
             ->add($this->getSubmitElement())
             ->add($this->getCancelElement());
        //@formatter:on

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
                                     'required' => false,
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
                             'label'    => '* Collection Description',
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
                                    'label'           => 'Collection Logo (min. 20x20, max. 2000x2000, max. 2MB)',
                                    'transferAdapter' => 'http',
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

    /**
     * This method creates input filter (used for form filtering/validation).
     */
    private function addInputFilter()
    {
        $this->prepare();  // this is another new thing and necessary

        // Create main input filter
        $inputFilter = $this->getInputFilter();

        $filter = $inputFilter->get('tagsuser');
        $filter->setRequired(false);

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

        $validatorCategory = new Validators\Category($this->projectCategoryRepository);
        $inputFilter->add(
            [
                'name'       => 'project_category_id',
                'required'   => false,
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
                    ['name' => 'StringTrim'],
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

        $modelImage = $this->imageRepository;

        $inputFilter->add(
            [
                'name'            => 'image_small_upload',
                'required'        => false,
                'maxFileSize'     => '2MB',
                'transferAdapter' => 'http',
                'validators'      => [
                    //new \Laminas\Validator\File\Count(1),
                    new Size(
                        array(
                            'min' => '500B',
                            'max' => '2MB',
                        )
                    ),
                    new Extension($modelImage->getAllowedFileExtension(), true),
                    new ImageSize(
                        array(
                            'minwidth'  => 20,
                            'maxwidth'  => 2000,
                            'minheight' => 20,
                            'maxheight' => 2000,
                        )
                    ),
                    new MimeType($modelImage->getAllowedMimeTypes()),
                ],
                'filters'         => [
                    ['name' => 'StringTrim'],
                ],
            ]
        );

    }

    public function getViewScriptsArray()
    {
        return $this->viewScripts;
    }

    /**
     * @param $sources array
     */
    public function setOnlineGalleryImageSources($sources)
    {
        $this->onlineGalleryImageSources = $sources;
        $this->init();
    }

}