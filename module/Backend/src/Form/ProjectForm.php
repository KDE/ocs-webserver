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

namespace Backend\Form;

use Laminas\Form\Form;

class ProjectForm extends Form
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Define form name
        parent::__construct('jtable-edit-form');
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

    }

    /**
     * This method creates input filter (used for form filtering/validation).
     */
    private function addInputFilter()
    {
        $inputFilter = $this->getInputFilter();
        $inputFilter->add(
            [
                'name'    => 'project_id',
                'filters' => [['name' => 'StringTrim'], ['name' => 'ToInt'],],

            ]
        );
        $inputFilter->add(
            [
                'name'    => 'member_id',
                'filters' => [['name' => 'StringTrim'], ['name' => 'ToInt'],],

            ]
        );
        $inputFilter->add(
            [
                'name'    => 'project_category_id',
                'filters' => [['name' => 'StringTrim'], ['name' => 'ToInt'],],

            ]
        );
        $inputFilter->add(
            [
                'name'    => 'status',
                'filters' => [['name' => 'StringTrim'], ['name' => 'ToInt'],],

            ]
        );
        $inputFilter->add(
            [
                'name'    => 'pid',
                'filters' => [['name' => 'StringTrim'], ['name' => 'ToInt'],],

            ]
        );
        $inputFilter->add(
            [
                'name'    => 'type_id',
                'filters' => [['name' => 'StringTrim'], ['name' => 'ToInt'],],

            ]
        );
        $inputFilter->add(
            [
                'name'    => 'creator_id',
                'filters' => [['name' => 'StringTrim'],],

            ]
        );
        $inputFilter->add(
            [
                'name'    => 'validated',
                'filters' => [['name' => 'StringTrim'],],

            ]
        );
        $inputFilter->add(
            [
                'name'    => 'featured',
                'filters' => [['name' => 'StringTrim'],],

            ]
        );
        $inputFilter->add(
            [
                'name'    => 'amount',
                'filters' => [['name' => 'StringTrim'],],

            ]
        );
        $inputFilter->add(
            [
                'name'    => 'spam_checked',
                'filters' => [['name' => 'StringTrim'],],

            ]
        );
        $inputFilter->add(
            [
                'name'    => 'claimable',
                'filters' => [['name' => 'StringTrim'],],

            ]
        );
        $inputFilter->add(
            [
                'name'    => 'claimed_by_member',
                'filters' => [['name' => 'StringTrim'],],

            ]
        );
        $inputFilter->add(
            [
                'name'    => 'title',
                'filters' => [['name' => 'StringTrim'],],

            ]
        );
        $inputFilter->add(
            [
                'name'    => 'description',
                'filters' => [['name' => 'StringTrim'],],

            ]
        );

    }

}