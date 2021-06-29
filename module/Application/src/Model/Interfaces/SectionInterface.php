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

namespace Application\Model\Interfaces;


use Application\Model\Entity\Section;
use Application\Model\Repository\Zend_Cache_Exception;
use Application\Model\Repository\Zend_Db_Select_Exception;
use Application\Model\Repository\Zend_Exception;

interface SectionInterface extends BaseInterface
{
    public function save(Section $obj);

    public function fetchAllSections();

    /**
     * @param null $id
     *
     * @return array
     */
    public function fetchNamesForJTable($id = null);

    /**
     * @return array
     */
    public function fetchAllSectionsAndCategories($clearCache = false);

    public function deleteId($dataId);

    public function delete($where);

    /**
     * @param bool $clearCache
     *
     * @return array
     */
    public function fetchAllSectionsArray($clearCache = false);

    /**
     * @param bool $clearCache
     *
     * @return array
     */
    public function fetchAllSectionByIdArray($clearCache = false);
}