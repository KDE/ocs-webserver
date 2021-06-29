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

/**
 * Class SettingsProfileForm
 *
 * @package Application\Form
 */
class SettingsProfileForm extends SettingsForm
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Define form name
        parent::__construct('settingsProfileForm');

        // Set POST method for this form
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '/settings/saveprofile');

        $this->addElements();
        $this->addInputFilter();
    }

    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements()
    {
        // Add "username" field
        $this->add(
            [
                'type'       => 'hidden',
                'name'       => 'username',
                'attributes' => [
                    'id'       => 'username',
                    'readonly' => true,
                ],
                'options'    => [
                    'label' => 'Username',
                ],
            ]
        );
        // Add "firstname" field
        $this->add(
            [
                'type'       => 'text',
                'name'       => 'firstname',
                'attributes' => [
                    'id' => 'firstname',
                ],
                'options'    => [
                    'label' => 'First Name:',
                ],
            ]
        );

        // Add "lastname" field
        $this->add(
            [
                'type'       => 'text',
                'name'       => 'lastname',
                'attributes' => [
                    'id' => 'lastname',
                ],
                'options'    => [
                    'label' => 'Last Name:',
                ],
            ]
        );

        // Add "city" field
        $this->add(
            [
                'type'       => 'text',
                'name'       => 'city',
                'attributes' => [
                    'id' => 'city',
                ],
                'options'    => [
                    'label' => 'City:',
                ],
            ]
        );

        // Add "country" field
        $this->add(
            [
                'type'       => 'text',
                'name'       => 'country',
                'attributes' => [
                    'id' => 'country',
                ],
                'options'    => [
                    'label' => 'Country:',
                ],
            ]
        );

        // Add "aboutme" field
        $this->add(
            [
                'type'       => 'textarea',
                'name'       => 'aboutme',
                'attributes' => [
                    'id'    => 'aboutme',
                    'class' => 'about',
                ],
                'options'    => [
                    'label' => 'About me:',
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
                'name'       => 'username',
                'required'   => false,
                'filters'    => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 4,
                            'max' => 35,
                        ],
                    ],

                ],
            ]
        );

        $inputFilter->add(
            [
                'name'     => 'firstname',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
            ]
        );

        $inputFilter->add(
            [
                'name'     => 'lastname',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
            ]
        );
        $inputFilter->add(
            [
                'name'     => 'city',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
            ]
        );
        $inputFilter->add(
            [
                'name'     => 'country',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
            ]
        );

    }

}