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
 * Class SettingsSocialAccountsForm
 *
 * @package Application\Form
 */
class SettingsSocialAccountsForm extends SettingsForm
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Define form name
        parent::__construct('settingsConnectedAccounts');

        // Set POST method for this form
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '/settings/accounts');

        $this->addElements();
        $this->addInputFilter();
    }

    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements()
    {
        // Add "link_facebook" field
        $this->add(
            [
                'type'       => 'url',
                'name'       => 'link_facebook',
                'attributes' => [
                    'id'      => 'link_facebook',
                    'class'   => 'inputUrl',
                    'pattern' => 'https?://.+',
                ],
                'options'    => [
                    'label' => 'Facebook Profile:',
                ],
            ]
        );

        // Add "link_twitter" field
        $this->add(
            [
                'type'       => 'url',
                'name'       => 'link_twitter',
                'attributes' => [
                    'id'      => 'link_twitter',
                    'class'   => 'inputUrl',
                    'pattern' => 'https?://.+',
                ],
                'options'    => [
                    'label' => 'Twitter Profile:',
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
                'name'     => 'link_facebook',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
            ]
        );
        $inputFilter->add(
            [
                'name'     => 'link_twitter',
                'required' => false,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
            ]
        );
    }

}