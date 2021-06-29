<?php
/**
 *  ocs-webserver
 *
 *  Copyright 2016 by pling GmbH.
 *
 *    This file is part of ocs-webserver.
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **/

namespace Application\View\Helper;

use Application\Model\Interfaces\PploadFilesInterface;
use Laminas\View\Helper\AbstractHelper;

class ProjectFiles extends AbstractHelper
{
    /**
     * Constructor
     *
     */
    private $pploadFilesRepository;

    public function __construct(PploadFilesInterface $pploadFilesRepository)
    {
        $this->pploadFilesRepository = $pploadFilesRepository;
    }

    public function __invoke($ppload_collection_id)
    {

        $countFiles = $this->pploadFilesRepository->fetchFilesCntForProject($ppload_collection_id);
        $filesInfos = array();
        $filesInfos['fileCount'] = $countFiles;

        return $filesInfos;
    }
}