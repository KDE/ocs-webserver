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
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

class ProjectCcLicense implements InputFilterAwareInterface
{
    // attributes
    public $license_id;
    public $project_id;
    public $by;
    public $nc;
    public $nd;
    public $sa;

    public function exchangeArray(array $data)
    {
        $this->license_id = !empty($data['license_id']) ? $data['license_id'] : null;
        $this->project_id = !empty($data['project_id']) ? $data['project_id'] : null;
        $this->by = !empty($data['by']) ? $data['by'] : null;
        $this->nc = !empty($data['nc']) ? $data['nc'] : null;
        $this->nd = !empty($data['nd']) ? $data['nd'] : null;
        $this->sa = !empty($data['sa']) ? $data['sa'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'license_id' => $this->license_id,
            'project_id' => $this->project_id,
            'by'         => $this->by,
            'nc'         => $this->nc,
            'nd'         => $this->nd,
            'sa'         => $this->sa,

        ];
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(
            sprintf(
                '%s does not allow injection of an alternate input filter', __CLASS__
            )
        );
    }

    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();
        //-----------------> hier come inputfiler
        $this->inputFilter = $inputFilter;

        return $this->inputFilter;
    }
}