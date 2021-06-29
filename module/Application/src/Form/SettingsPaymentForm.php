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

use Laminas\Validator\Hostname;

/**
 * Class SettingsPaymentForm
 *
 * @package Application\Form
 */
class SettingsPaymentForm extends SettingsForm
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Define form name
        parent::__construct('settingsPaymentForm');

        // Set POST method for this form
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '/settings/payment');

        $this->addElements();
        $this->addInputFilter();
    }

    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements()
    {
        // Add "paypal_mail" field
        $this->add(
            [
                'type'       => 'text',
                'name'       => 'paypal_mail',
                'attributes' => [
                    'id' => 'paypal_mail',
                ],
                'options'    => [
                    'label' => 'Paypal: Email Adress',
                ],
            ]
        );

        // Add "wallet_address" field
        $this->add(
            [
                'type'       => 'text',
                'name'       => 'wallet_address',
                'attributes' => [
                    'id' => 'wallet_address',
                ],
                'options'    => [
                    'label' => 'Bitcoin: Your Public Wallet Address',
                ],
            ]
        );

        // Add "dwolla_id" field
        $this->add(
            [
                'type'       => 'text',
                'name'       => 'dwolla_id',
                'attributes' => [
                    'id' => 'dwolla_id',
                ],
                'options'    => [
                    'label' => 'Dwolla: User ID (xxx-xxx-xxxx)',
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
                'name'       => 'paypal_mail',
                'required'   => false,
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

        $inputFilter->add(
            [
                'name'          => 'wallet_address',
                'required'      => false,
                'filters'       => [
                    ['name' => 'StringTrim'],
                ],
                'validators'    => [
                    [
                        'name'    => 'Regex',
                        'options' => [
                            'pattern' => '/^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$/',
                        ],
                    ],
                ],
                'error_message' => 'The Bitcoin Address is not valid.',
            ]
        );
    }
}