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
 *
 */

namespace Application\Form;

use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;
use Laminas\Form\Form;
use Laminas\Validator\Db\NoRecordExists;
use Laminas\Validator\Hostname;
use Laminas\Validator\Identical;
use Laminas\Validator\Regex;

/**
 * Class RegisterForm
 * This form is used to collect user's login, password and 'Remember Me' flag.
 *
 * @package Application\Form
 */
class RegisterForm extends Form
{
    private $validation_rules;

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Define form name
        parent::__construct('login-form');

        $this->validation_rules = $GLOBALS['ocs_config']->settings->validation->rules;

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
        // Add "username" field
        $this->add(
            [
                'type'    => 'text',
                'name'    => 'username',
                'options' => [
                    'label' => 'Your Username',
                ],
            ]
        );

        // Add "email" field
        $this->add(
            [
                'type'    => 'text',
                'name'    => 'mail',
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

        // Add "password" field
        $this->add(
            [
                'type'    => 'password',
                'name'    => 'password-confirm',
                'options' => [
                    'label' => 'Password Confirm',
                ],
            ]
        );

        // Add "redirect_url" field
        $this->add(
            [
                'type' => 'hidden',
                'name' => 'redirect',
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

        // Add the RECAPTCHA field
        $this->add(
            [
                'type'       => 'captcha',
                'name'       => 'recaptcha',
                'attributes' => [],
                'options'    => [
                    'label'   => 'Human check',
                    'captcha' => [
                        'class'      => 'ReCaptcha',
                        'secret_key' => $GLOBALS['ocs_config']['recaptcha']['secretkey'],
                        'site_key'   => $GLOBALS['ocs_config']['recaptcha']['sitekey'],
                        //                    'size' => 'a',
                        //                    'theme' => 'b',
                        //                    'type' => 'c',
                        //                    'tabindex' => 'd',
                        //                    'callback' => 'e',
                        //                    'expired-callback' => 'f',
                        //                    'hl' => 'g',
                        //                    'noscript' => 'h',
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

        // Add input for "username" field
        $inputFilter->add(
            [
                'name'       => 'username',
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
                            'max' => 20,
                        ],
                    ],
                    [
                        'name'    => 'regex',
                        'options' => [
//                        'pattern' => '/^(?=.{4,20}$)(?![-])(?!.*[-]{2})[a-z0-9-]+(?<![-])$/',
                            'pattern'  => $this->validation_rules->username,
                            'messages' => array(
                                Regex::NOT_MATCH => 'Username should consist of letters [a-z] or numbers [0-9] or minus [-].',
                            ),
                        ],
                    ],
                    [
                        'name'    => 'Laminas\Validator\Db\NoRecordExists',
                        'options' => array(
                            'table'    => 'member',
                            'field'    => 'username',
                            'adapter'  => GlobalAdapterFeature::getStaticAdapter(),
                            //'exclude' => $this->excludeFields,
                            'messages' => array(
                                NoRecordExists::ERROR_RECORD_FOUND => 'Username already exists',
                            ),
                        ),
                    ],
                ],
            ]
        );

        // Add input for "email" field
        $inputFilter->add(
            [
                'name'       => 'mail',
                'required'   => true,
                'filters'    => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StringToLower'],
                    ['name' => 'StripNewlines'],
                ],
                'validators' => [
                    [
                        'name'    => 'regex',
                        'options' => [
//                            'pattern'  => '/^([a-zA-Z0-9_\-+\.]+)?@.*$/',
                            'pattern'  => $this->validation_rules->email,
                            'messages' => array(
                                Regex::NOT_MATCH => 'Email address should consist of letters [a-z] or numbers [0-9] or underscore [_], minus [-], plus [+], period [.].',
                            ),
                        ],
                    ],
                    [
                        'name'    => 'EmailAddress',
                        'options' => [
                            'allow'      => Hostname::ALLOW_DNS,
                            'useMxCheck' => false,
                            'domain'     => true,
                        ],
                    ],
                    [
                        'name'    => 'Laminas\Validator\Db\NoRecordExists',
                        'options' => array(
                            'table'    => 'member',
                            'field'    => 'mail',
                            'adapter'  => GlobalAdapterFeature::getStaticAdapter(),
                            //'exclude' => $this->excludeFields,
                            'messages' => array(
                                NoRecordExists::ERROR_RECORD_FOUND => 'Email address already exists',
                            ),
                        ),
                    ],
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
                    [
                        'name'    => 'Identical',
                        'options' => [
                            'token'    => 'password-confirm',
                            'messages' => array(
                                Identical::NOT_SAME => 'The two given passwords do not match',
                            ),
                        ],
                    ],
                ],
            ]
        );

        // Add input for "redirect_url" field
        $inputFilter->add(
            [
                'name'       => 'redirect',
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
