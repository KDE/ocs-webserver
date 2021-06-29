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
use Laminas\Validator\Hostname;

/**
 * Class PasswordResetForm
 * This form is used to collect user's E-mail address (used to recover password).
 *
 * @package Application\Form
 */
class PasswordResetForm extends Form
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        // Define form name
        parent::__construct('password-reset-form');

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
                'type'    => 'email',
                'name'    => 'email',
                'options' => [
                    'label' => 'Your E-mail',
                ],
            ]
        );

        // Add the CAPTCHA field
        $this->add(
            [
                'type'    => 'captcha',
                'name'    => 'captcha',
                'options' => [
                    'label'   => 'Human check',
                    'captcha' => [
                        'class'          => 'Image',
                        'imgDir'         => 'public/img/captcha',
                        'suffix'         => '.png',
                        'imgUrl'         => '/img/captcha/',
                        'imgAlt'         => 'CAPTCHA Image',
                        'font'           => './data/font/thorne_shaded.ttf',
                        'fsize'          => 24,
                        'width'          => 350,
                        'height'         => 100,
                        'expiration'     => 600,
                        'dotNoiseLevel'  => 40,
                        'lineNoiseLevel' => 3,
                    ],
                ],
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
                    'value' => 'Reset Password',
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
                ],
                'validators' => [
                    [
                        'name'    => 'EmailAddress',
                        'options' => [
                            'allow'      => Hostname::ALLOW_DNS,
                            'useMxCheck' => false,
                        ],
                    ],
                ],
            ]
        );
    }
}
