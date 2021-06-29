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

use Laminas\Filter\StringTrim;
use Laminas\Validator\Regex;

/**
 * Class SettingsGithubForm
 *
 * @package Application\Form
 */
class SettingsGithubForm extends SettingsForm
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Define form name
        parent::__construct('settingsGithub');

        // Set POST method for this form
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '/settings/github');

        $this->addElements();
        $this->addInputFilter();
    }

    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements()
    {
        $this->add(
            [
                'type'       => 'text',
                'name'       => 'link_github',
                'attributes' => [
                    'id' => 'link_github',
                ],
                'options'    => [
                    'label' => 'GitHub Profile:',
                ],
            ]
        );

        $this->add(
            [
                'type'       => 'text',
                'name'       => 'token_github',
                'attributes' => [
                    'id' => 'token_github',
                ],
                'options'    => [
                    'label' => 'GitHub Access Token:',
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
                'name'       => 'link_github',
                'required'   => true,
                'filters'    => [
                    ['name' => StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => 'Regex',
                        'options' => [
                            'pattern'  => '/^\w+-?\w+(?!-)$/',
                            'messages' => array(
                                Regex::ERROROUS  => 'There was an internal error while validate your input.',
                                Regex::NOT_MATCH => "'%value%' does not match against pattern for valid GitHub username.",
                            ),
                        ],
                    ],
                ],
            ]
        );
        $inputFilter->add(
            [
                'name'       => 'token_github',
                'required'   => false,
                'filters'    => [
                    ['name' => StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => 'Regex',
                        'options' => [
                            'pattern'  => '/^\w+-?\w+(?!-)$/',
                            'messages' => array(
                                Regex::ERROROUS  => 'There was an internal error while validate your input.',
                                Regex::NOT_MATCH => "'%value%' does not match against pattern for valid GitHub username.",
                            ),
                        ],
                    ],
                ],
            ]
        );
    }
}