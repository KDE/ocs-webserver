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
use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Form;

/**
 * Class GallerySubForm
 *
 * @package Application\Form
 */
class GallerySubForm extends Form
{

    protected $imageRepository;
    private $onlineGalleryPictureSources = array();
    private $numberOfUploadGalleryPictures = 1;
    private $maxGalleryPics = 5;

    /**
     * Constructs a new GallerySubForm object.
     *
     * @param ImageRepository $imageRepository
     * @param null|array      $options
     */
    public function __construct(ImageRepository $imageRepository, $options)
    {
        $this->imageRepository = $imageRepository;

        if (isset($options['pictures'])) {
            $this->onlineGalleryPictureSources = $options['pictures'];
        }

        if (isset($options['nr_upload_pics'])) {
            $this->numberOfUploadGalleryPictures = $options['nr_upload_pics'];
        }

        parent::__construct($options);

        $this->init();
    }

    /**
     * Returns the label of this sub-form.
     *
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
     *
     * @return bool
     */
    public function isRequired()
    {
        return false;
    }

    /**
     * Sets the image sources of the existing online pictures in the gallery.
     * ATTENTION: This method triggers the reinitialization. Would be better to add the pictures with the option
     * 'pictures' in the constructor
     *
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
        //SubForm for already uploaded - online - pictures
        $subFormOnline = new Form('online_picture');

        $i = 0;
        foreach ($this->onlineGalleryPictureSources as $onlineGalleryImageSrc) {
            $onlineGalleryPicture = new Hidden(
                '' . $i, array('id' => 'gallery_picture_online_' . $i)
            );
            $onlineGalleryPicture->setValue($onlineGalleryImageSrc);
            $subFormOnline->add($onlineGalleryPicture);
            $i++;
        }

        //Sub-Form for pics that should be uploaded
        $subFormUpload = new Form('upload');

        $uploadGalleryPicture = new File(
            'upload_picture', array(
                                'validators' => array(
                                    array('Size', true, array('max' => '5242880')),
                                    array('Extension', true, $this->imageRepository->getAllowedFileExtension()),
                                    array('MimeType', true, $this->imageRepository->getAllowedMimeTypes()),
                                ),
                                'filters'    => array(
                                    array(
                                        'Rename',
                                        array(
                                            'target'    => IMAGES_UPLOAD_PATH . 'tmp/',
                                            'overwrite' => true,
                                            'randomize' => true,
                                        ),
                                    ),
                                ),
                            )
        );
        $subFormUpload->add($uploadGalleryPicture);

        //Adding Subforms to the form
        $this->add($subFormOnline)->add($subFormUpload);
    }

    public function getNumberOfUploadGalleryPictures()
    {
        return $this->numberOfUploadGalleryPictures;
    }

    /**
     * Sets the number of displayed upload elements.
     * ATTENTION: This method triggers the reinitialization. Would be better to add the pictures with the option
     * 'pictures' in the constructor
     *
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

    public function isValid()
    {
        $valid = parent::isValid();

        //Validates, if the number of pictures exceed the specified number
        //Minus one because one of the uploaded elements is always null       
        $cntOnlinePicture = 0;
        foreach ($this->getElements() as $el) {
            if ($el->getValue()) {
                $cntOnlinePicture++;
            }
        }
        if ($cntOnlinePicture + count($this->upload->upload_picture->getValue()) > $this->getMaxGalleryPics()) {
            $this->isValid = false;
            $this->setMessages(['The maximum number of allowed gallery pictures was exceeded']);
            $valid = false;
        }

        return $valid;
    }

}