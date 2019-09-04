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
class Default_Form_GallerySubForm extends Zend_Form_SubForm
{

    private $onlineGalleryPictureSources = array();

    private $numberOfUploadGalleryPictures = 1;

    private $maxGalleryPics = 5;

    /**
     * Constructs a new GallerySubForm object.
     * @param null|array $options
     */
    public function __construct($options = null)
    {
        if (isset($options['pictures'])) {
            $this->onlineGalleryPictureSources = $options['pictures'];
        }

        if (isset($options['nr_upload_pics'])) {
            $this->numberOfUploadGalleryPictures = $options['nr_upload_pics'];
        }

        parent::__construct($options);
    }

    /**
     * Returns the label of this sub-form.
     * @return string
     */
    public function getLabel()
    {
        return 'Product Gallery (max. ' . $this->getMaxGalleryPics() . ' pictures)';
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
     * Method if the sub-form should be displayed as required. Returns always false.
     * @return bool
     */
    public function isRequired()
    {
        return false;
    }

    /**
     * Sets the image sources of the existing online pictures in the gallery.
     * ATTENTION: This method triggers the reinitialization. Would be better to add the pictures with the option 'pictures' in the constructor
     * @param $onlineGalleryPictureSources
     */
    public function setOnlineGalleryImageSources($onlineGalleryPictureSources)
    {
        $this->onlineGalleryPictureSources = $onlineGalleryPictureSources;
        $this->init();
    }

    /**
     * Initializes the GalleryPicture-SubForm
     */
    public function init()
    {
        $this->addPrefixPath('Default_Form_Decorator', APPLICATION_PATH . '/modules/default/forms/decorators/',Zend_Form::DECORATOR);
        $this->addElementPrefixPath('Default_Form_Decorator', APPLICATION_PATH . '/modules/default/forms/decorators/',Zend_Form::DECORATOR);

        //General setups
        $this->setDisableLoadDefaultDecorators(true);
        $this->setDecorators(
            array(
                'FormElements',
                'Gallery',
                'GalleryError',
                array('HtmlTag', array('tag' => 'div', 'class' => 'field relative')),
                'Label'
            ))
             ->setDecorators(
                 array(
                     'FormElements',
                     'Gallery',
                     'GalleryError',
                     array(
                         'ViewScript',
                         array(
                             'viewScript' => 'product/viewscripts/subform_gallery.phtml',
                             'placement'  => false
                         )
                     )
                 ))
             ->setElementsBelongTo('gallery')
             ->setIsArray(false)
             ->setElementsBelongTo(null);

        //SubForm for already uploaded - online - pictures
        $subFormOnline = new Zend_Form_SubForm();
        $subFormOnline->setDisableLoadDefaultDecorators(true)
                      ->setDecorators(array(
                          'FormElements'
                      ));

        $i = 0;
        foreach ($this->onlineGalleryPictureSources as $onlineGalleryImageSrc) {

            $onlineGalleryPicture = $this->createElement('hidden', '' . $i,
                array('id' => 'gallery_picture_online_' . $i))
                                         ->setDisableLoadDefaultDecorators(true)
                                         ->setValue($onlineGalleryImageSrc)
                                         ->setDecorators(
                                             array(
                                                 'ViewHelper',
                                                 array('GalleryPicture', array('type' => 0))
                                             ));

            $subFormOnline->addElement($onlineGalleryPicture);
            $i++;
        }


        //Sub-Form for pics that should be uploaded
        $subFormUpload = new Zend_Form_SubForm();
        $subFormUpload->setDisableLoadDefaultDecorators(true)
                      ->setDecorators(array(
                          'FormElements'
                      ));


        $imageTable = new Default_Model_DbTable_Image();
        /** @var Zend_Form_Element_File $uploadGalleryPicture */
        $uploadGalleryPicture = $subFormUpload->createElement('file', 'upload_picture');
        $uploadGalleryPicture->addPrefixPath('Local_File_Transfer_Adapter', 'Local/File/Transfer/Adapter',Zend_Form_Element_File::TRANSFER_ADAPTER);
        $uploadGalleryPicture->setTransferAdapter('HttpMediaType');
        $uploadGalleryPicture->setDisableLoadDefaultDecorators(true)
                             ->setRequired(false)
                             ->setDecorators(
                                 array(
                                     array('File' => new Local_Form_Decorator_File()),
                                     array('GalleryPicture', array('type' => 1))
                                 ))
                             ->setAttrib('class', 'gallery-picture')
                             ->setAttrib('onchange', 'ProductGallery.previewImage(this);')
                             ->addValidator('Count', true, 5)
                             ->setMaxFileSize('29360128') //This setting affects the entire form, so here we also need to add the size of the logo.
                             ->addValidator('Size', true, array('max' => '5242880')) //max size of single uploaded file
                             ->addValidator('FilesSize', true, array('max' => '27262976')) //max size of all uploaded files
                             ->addValidator('Extension', true, $imageTable->getAllowedFileExtension())
                             ->addValidator('MimeType', true, $imageTable->getAllowedMimeTypes())
                             ->setMultiFile($this->getNumberOfUploadGalleryPictures())
                             ->addFilter('Rename',
                                 array(
                                     'target'    => IMAGES_UPLOAD_PATH . 'tmp/',
                                     'overwrite' => true,
                                     'randomize' => true
                                 ))
                             ->setIsArray(true);

        $subFormUpload->addElement($uploadGalleryPicture);

        //Adding Subforms to the form
        $this->addSubForm($subFormOnline, 'online_picture')
             ->addSubForm($subFormUpload, 'upload');
    }

    public function getNumberOfUploadGalleryPictures()
    {
        return $this->numberOfUploadGalleryPictures;
    }

    /**
     * Sets the number of displayed upload elements.
     * ATTENTION: This method triggers the reinitialization. Would be better to add the pictures with the option 'pictures' in the constructor
     * @param $number
     */
    public function setNumberOfUploadGalleryPictures($number)
    {
        $this->numberOfUploadGalleryPictures = $number;
        $this->init();
    }

    public function getOnlineGalleryImageSources()
    {
        return $this->onlineGalleryPictureSources;
    }

    public function isValid($data)
    {
        $valid = parent::isValid($data);
        //Validates, if the number of pictures exceed the specified number
        //Minus one because one of the uploaded elements is always null       

        $cntOnlinePicture = 0;
        foreach ($this->online_picture->getElements() as $el) {
            if ($el->getValue()) {
                $cntOnlinePicture++;
            }
        }
        if ($cntOnlinePicture + count($this->upload->upload_picture->getValue()) > $this->getMaxGalleryPics()) {
            $this->markAsError();
            $this->addErrorMessage('projects.edit.gallery.max_number_files_exceeded');
            $valid = false;
        }

        return $valid;
    }

}