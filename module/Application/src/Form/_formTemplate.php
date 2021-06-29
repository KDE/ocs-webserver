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

use Laminas\Form\Form;
use Laminas\InputFilter\FileInput;
use Laminas\Validator\Hostname;

/**
 * Class _formTemplate
 *
 * @package Application\Form
 */
abstract class _formTemplate extends Form
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Define form name
        parent::__construct(__CLASS__);

        // Set POST method for this form
        $this->setAttribute('method', 'post');

        $this->addElements();
        $this->addInputFilter();
    }

    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements()
    {
        // Add "email" field
        $this->add(
            [
                'type'       => 'text',
                'name'       => 'user_email',
                'attributes' => [
                    'id' => 'email',
                ],
                'options'    => [
                    'label' => 'Add email address',
                ],
            ]
        );

        // Add "file" field
        $this->add(
            [
                'type'       => 'file',
                'name'       => 'file',
                'attributes' => [
                    'id' => 'file',
                ],
                'options'    => [
                    'label' => 'Image file',
                ],
            ]
        );

        // Add "payment_method" field
        $this->add(
            [
                'type'       => 'select',
                'name'       => 'payment_method',
                'attributes' => [
                    'id' => 'payment_method',
                ],
                'options'    => [
                    'label'         => 'Payment Method',
                    'empty_option'  => '-- Please select --',
                    'value_options' => [
                        'credit_card'  => 'Credit Card',
                        'bank_account' => 'Bank Account',
                        'cash'         => 'Cash',
                    ],
                ],
            ]
        );

        // Add the submit button
        $this->add(
            [
                'type'       => 'submit',
                'name'       => 'submit',
                'attributes' => [
                    'value' => 'Save &amp; Update',
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
        $inputFilter->add(
            [
                'name'       => 'user_email',
                'required'   => true,
                'filters'    => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                    ['name' => 'StripNewlines'],
                ],
                'validators' => [
                    [
                        'name'    => 'EmailAddress',
                        'options' => [
                            'allow'      => Hostname::ALLOW_DNS,
                            'useMxCheck' => false,
                            'domain'     => true,
                        ],
                    ],
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 6,
                            'max' => 30,
                        ],
                    ],

                ],
            ]
        );

        // Add validation rules for the "file" field	 
        $inputFilter->add(
            [
                'type'       => FileInput::class,
                'name'       => 'file',
                'required'   => true,
                'validators' => [
                    ['name' => 'FileUploadFile'],
                    [
                        'name'    => 'FileMimeType',
                        'options' => [
                            'mimeType' => ['image/jpeg', 'image/png'],
                        ],
                    ],
                    ['name' => 'FileIsImage'],
                    [
                        'name'    => 'FileImageSize',
                        'options' => [
                            'minWidth'  => 128,
                            'minHeight' => 128,
                            'maxWidth'  => 4096,
                            'maxHeight' => 4096,
                        ],
                    ],
                ],
                'filters'    => [
                    [
                        'name'    => 'FileRenameUpload',
                        'options' => [
                            'target'             => './data/upload',
                            'useUploadName'      => true,
                            'useUploadExtension' => true,
                            'overwrite'          => true,
                            'randomize'          => false,
                        ],
                    ],
                ],
            ]
        );
        // InArray
        $inputFilter->add(
            [
                'name'       => 'payment_method',
                'required'   => true,
                'filters'    => [],
                'validators' => [
                    [
                        'name'    => 'InArray',
                        'options' => [
                            'haystack' => ['credit_card', 'bank_account', 'cash'],
                        ],
                    ],
                ],
            ]
        );
    }

}