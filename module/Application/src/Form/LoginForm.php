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

/**
 * Class LoginForm
 * This form is used to collect user's login, password and 'Remember Me' flag.
 *
 * @package Application\Form
 */
class LoginForm extends Form
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        // Define form name
        parent::__construct('login-form');

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
                'type'    => 'text',
                'name'    => 'email',
                'options' => [
                    'label' => 'Your E-mail',
                ],
            ]
        );

        // Add "password" field
        $this->add(
            [
                'type'    => 'password',
                'name'    => 'password',
                'options' => [
                    'label' => 'Password',
                ],
            ]
        );

        // Add "remember_me" field
        $this->add(
            [
                'type'    => 'checkbox',
                'name'    => 'remember_me',
                'options' => [
                    'label' => 'Remember me',
                ],
            ]
        );

        // Add "redirect_url" field
        $this->add(
            [
                'type' => 'hidden',
                'name' => 'redirect_url',
            ]
        );

        // Add the CSRF field
        $this->add(
            [
                'type'    => 'csrf',
                'name'    => 'csrf',
                'options' => [
                    'csrf_options' => [
                        'timeout' => 600,
                    ],
                ],
            ]
        );

        // Add the Submit button
        $this->add(
            [
                'type'       => 'submit',
                'name'       => 'submit',
                'attributes' => [
                    'value' => 'Sign in',
                    'id'    => 'submit',
                ],
            ]
        );
    }

    /**
     * This method creates input filter (used for form filtering/validation).
     */
    private function addInputFilter()
    {
        // Create main input filter
        $inputFilter = $this->getInputFilter();

        // Add input for "email" field
        $inputFilter->add(
            [
                'name'       => 'email',
                'required'   => true,
                'filters'    => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripNewlines'],
                ],
                'validators' => [
//                [
//                    'name'    => 'EmailAddress',
//                    'options' => [
//                        'allow'      => \Laminas\Validator\Hostname::ALLOW_DNS,
//                        'useMxCheck' => false,
//                    ],
//                ],
                ],
            ]
        );

        // Add input for "password" field
        $inputFilter->add(
            [
                'name'       => 'password',
                'required'   => true,
                'filters'    => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 6,
                            'max' => 64,
                        ],
                    ],
                ],
            ]
        );

        // Add input for "remember_me" field
        $inputFilter->add(
            [
                'name'       => 'remember_me',
                'required'   => false,
                'filters'    => [],
                'validators' => [
                    [
                        'name'    => 'InArray',
                        'options' => [
                            'haystack' => [0, 1],
                        ],
                    ],
                ],
            ]
        );

        // Add input for "redirect_url" field
        $inputFilter->add(
            [
                'name'       => 'redirect_url',
                'required'   => false,
                'filters'    => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 2048,
                        ],
                    ],
                ],
            ]
        );
    }
}

