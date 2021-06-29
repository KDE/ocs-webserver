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

use Application\Model\Interfaces\MemberInterface;
use Application\Validator\OldPasswordConfirm;

/**
 * Class SettingsPasswordForm
 *
 * @package Application\Form
 */
class SettingsPasswordForm extends SettingsForm
{

    /**
     * Constructor.
     */
    private $memberRepository;

    public function __construct(MemberInterface $memberRepository)
    {
        $this->memberRepository = $memberRepository;
        // Define form name
        parent::__construct('settingsPasswordForm');

        // Set POST method for this form
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '/settings/password');

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
                'type'       => 'password',
                'name'       => 'passwordOld',
                'attributes' => [
                    'id' => 'passwordOld',
                ],
                'options'    => [
                    'label' => 'Enter old Password:',
                ],
            ]
        );
        $this->add(
            [
                'type'       => 'password',
                'name'       => 'password1',
                'attributes' => [
                    'id' => 'password1',
                ],
                'options'    => [
                    'label' => 'Enter new Password:',
                ],
            ]
        );
        $this->add(
            [
                'type'       => 'password',
                'name'       => 'password2',
                'attributes' => [
                    'id' => 'password2',
                ],
                'options'    => [
                    'label' => 'Re-enter new Password:',
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
                'name'       => 'passwordOld',
                'required'   => true,
                'validators' => [
                    [
                        'name'    => OldPasswordConfirm::class,
                        'options' => [
                            'memberRepository' => $this->memberRepository,
                        ],
                    ],
                ],
            ]
        );
        $inputFilter->add(
            [
                'name'       => 'password1',
                'required'   => true,
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

        $inputFilter->add(
            [
                'name'       => 'password2',
                'required'   => true,
                'validators' => [
                    [
                        'name'    => 'Identical',
                        'options' => [
                            'token' => 'password1',
                        ],
                    ],
                ],
            ]
        );

    }
}