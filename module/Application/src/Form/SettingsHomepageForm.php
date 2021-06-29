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

/**
 * Class SettingsHomepageForm
 *
 * @package Application\Form
 */
class SettingsHomepageForm extends SettingsForm
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Define form name
        parent::__construct('settingsHomepageForm');

        // Set POST method for this form
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '/settings/homepage');

        $this->addElements();
        $this->addInputFilter();
    }

    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements()
    {
        // Add "link_website" field
        $this->add(
            [
                'type'       => 'url',
                'name'       => 'link_website',
                'attributes' => [
                    'id' => 'link_website',
                ],
                'options'    => [
                    'label' => 'Website:',
                ],
            ]
        );

        $this->add(
            [
                'type'    => 'Laminas\Form\Element\Csrf',
                'name'    => 'csrf',
                'options' => [
                    'csrf_options' => array(
                        'timeout' => 600,
                    ),
                ],
            ]
        );

        $this->add(
            [
                'type'       => 'text',
                'name'       => 'html_verifier',
                'attributes' => [
                    'id' => 'html_verifier',
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
                'name'     => 'link_website',
                'required' => true,
                'filters'  => [
                    ['name' => StringTrim::class],
                ],
            ]
        );
    }
}