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

namespace Application\Model\Entity;

use DomainException;
use Laminas\Filter\StringToLower;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripNewlines;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToInt;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;

class SolrResultEntity implements InputFilterAwareInterface
{
    private $inputFilter;

    public function exchangeArray(array $data)
    {

    }

    public function getArrayCopy()
    {
        return [

        ];
    }

    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();
        // projectSearchText
        $inputFilter->add(
            [
                'name'       => 'projectSearchText',
                'required'   => false,
                'filters'    => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                    ['name' => StripNewlines::class],
                ],
                'validators' => [
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 3,
                            'max'      => 100,
                        ],
                    ],
                ],
            ]
        );
        $inputFilter->add(
            [
                'name'       => 'search',
                'required'   => false,
                'filters'    => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                    ['name' => StripNewlines::class],
                ],
                'validators' => [
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 3,
                            'max'      => 100,
                        ],
                    ],
                ],
            ]
        );
        // search field
        $inputFilter->add(
            [
                'name'       => 'f',
                'required'   => false,
                'filters'    => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 3,
                            'max'      => 100,
                        ],
                    ],
                ],
            ]
        );
        // page
        $inputFilter->add(
            [
                'name'     => 'page',
                'required' => false,
                'filters'  => [
                    ['name' => ToInt::class],
                ],
            ]
        );

        // project category id
        $inputFilter->add(
            [
                'name'     => 'pci',
                'required' => false,
                'filters'  => [
                    ['name' => ToInt::class],
                ],
            ]
        );
        // score
        $inputFilter->add(
            [
                'name'     => 'ls',
                'required' => false,
                'filters'  => [
                    ['name' => ToInt::class],
                ],
            ]
        );
        // tags
        $inputFilter->add(
            [
                'name'       => 't',
                'required'   => false,
                'allowEmpty' => true,
                'filters'    => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 3,
                            'max'      => 100,
                        ],
                    ],
                ],
            ]
        );

        // package
        $inputFilter->add(
            [
                'name'       => 'pkg',
                'required'   => false,
                'allowEmpty' => true,
                'filters'    => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 3,
                            'max'      => 100,
                        ],
                    ],
                ],
            ]
        );

        // package
        $inputFilter->add(
            [
                'name'       => 'lic',
                'required'   => false,
                'allowEmpty' => true,
                'filters'    => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 3,
                            'max'      => 100,
                        ],
                    ],
                ],
            ]
        );

        // package
        $inputFilter->add(
            [
                'name'       => 'arch',
                'required'   => false,
                'allowEmpty' => true,
                'filters'    => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 3,
                            'max'      => 100,
                        ],
                    ],
                ],
            ]
        );
        $this->inputFilter = $inputFilter;

        return $this->inputFilter;
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(
            sprintf(
                '%s does not allow injection of an alternate input filter', __CLASS__
            )
        );
    }
}