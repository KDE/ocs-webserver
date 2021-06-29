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

namespace Application\Form;

use Laminas\InputFilter\FileInput;

/**
 * Class SettingsPictureForm
 *
 * @package Application\Form
 */
class SettingsPictureForm extends SettingsForm
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Define form name
        parent::__construct('settingsPictureForm');

        // Set POST method for this form
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '/settings/picture');

        $this->addElements();
        $this->addInputFilter();
    }

    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements()
    {
        // Add "profile_image_url" field
        $this->add(
            [
                'type'       => 'hidden',
                'name'       => 'profile_image_url',
                'attributes' => [
                    'id' => 'profile_image_url',
                ],
            ]
        );

        // Add "profile_image_url" field
        $this->add(
            [
                'type'       => 'hidden',
                'name'       => 'profile_img_src',
                'attributes' => [
                    'id' => 'profile_img_src',
                ],
            ]
        );

        // Add "profile_picture_upload" field
        $this->add(
            [
                'type'       => 'file',
                'name'       => 'profile_picture_upload',
                'attributes' => [
                    'id' => 'profile_picture_upload',
                ],
                'options'    => [
                    'label' => 'Profile Picture',
                ],
            ]
        );

        // Add the submit button
        $this->add(
            [
                'type'       => 'submit',
                'name'       => 'submit',
                'attributes' => [
                    'value' => 'Save & Update',
                    'id'    => 'submitbutton',
                ],
            ]
        );
    }

    /**
     * This method creates input filter (used for form filtering/validation).
     */
    private function addInputFilter()
    {
        $inputFilter = $this->getInputFilter();
        // Add validation rules for the "profile_picture_upload" field	 
        $inputFilter->add(
            [
                'type'                                           => FileInput::class,
                'name'                                           => 'profile_picture_upload',
                'required'                                       => false,
                /*
                'filters'  => [
                    [
                        'name' => 'FileRenameUpload',
                        'options' => [
                            'target'=> IMAGES_UPLOAD_PATH . '/tmp',
                            'useUploadName'=>true,
                            'useUploadExtension'=>true,
                            'overwrite'=>true,
                            'randomize'=>false
                        ]
                    ]
                ], */ 'validators' => [
                ['name' => 'FileUploadFile'],
                [
                    'name'    => 'FileMimeType',
                    'options' => [
                        'mimeType' => ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'application/x-empty'],
                    ],
                ],
                ['name' => 'FileIsImage'],
                [
                    'name'    => 'FileImageSize',
                    'options' => [
                        'minWidth'  => 20,
                        'minHeight' => 20,
                        'maxWidth'  => 1024,
                        'maxHeight' => 1024,
                    ],
                ],
            ],
            ]
        );
    }
}
