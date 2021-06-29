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
 * Class ResendConfirmMailForm
 * This form is used for resending registration confirmation to users mail address.
 *
 * @package Application\Form
 */
class ResendConfirmMailForm extends Form
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        // Define form name
        parent::__construct('resend-form');

        // Set POST method for this form
        $this->setAttribute('method', 'post');

        $this->addElements();
    }

    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements()
    {
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
                    'value' => 'Resend',
                    'id'    => 'submit',
                ],
            ]
        );
    }

}

