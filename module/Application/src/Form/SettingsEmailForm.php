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

use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;
use Laminas\Filter\StringToLower;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripNewlines;
use Laminas\Filter\StripTags;
use Laminas\Validator\Db\NoRecordExists;
use Laminas\Validator\Hostname;
use Laminas\Validator\Regex;

/**
 * Class SettingsEmailForm
 *
 * @package Application\Form
 */
class SettingsEmailForm extends SettingsForm
{
    private $validation_rules;

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Define form name
        parent::__construct('settingsEmailForm');

        $this->validation_rules = $GLOBALS['ocs_config']->settings->validation->rules;

        // Set POST method for this form
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '/settings/addemail');

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

        // Add the submit button
        $this->add(
            [
                'type'       => 'submit',
                'name'       => 'submit',
                'attributes' => [
                    'value' => 'Add',
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
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                    ['name' => StringToLower::class],
                    ['name' => StripNewlines::class],
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
//                    [
//                        'name'    => 'StringLength',
//                        'options' => [
//                            'min' => 6,
//                            'max' => 30,
//                        ],
//                    ],
                    [
                        'name'    => 'Laminas\Validator\Db\NoRecordExists',
                        'options' => [
                            'table'    => 'member_email',
                            'field'    => 'email_address',
                            'adapter'  => GlobalAdapterFeature::getStaticAdapter(),
                            'exclude'  => array('field' => 'email_deleted', 'value' => 1),
                            'messages' => array(
                                NoRecordExists::ERROR_RECORD_FOUND => 'Email address already exists',
                            ),
                        ],
                    ],
                ],
            ]
        );
    }
}