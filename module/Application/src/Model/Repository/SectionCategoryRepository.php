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

namespace Application\Model\Repository;

use Application\Model\Entity\SectionCategory;
use Application\Model\Interfaces\SectionCategoryInterface;
use Laminas\Db\Adapter\AdapterInterface;

class SectionCategoryRepository extends BaseRepository implements SectionCategoryInterface
{

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "section_category";
        $this->_key = "section_category_id";
        $this->_prototype = SectionCategory::class;
    }

    /**
     * @param int $dataId
     */
    public function deleteId($dataId)
    {
        $this->deleteReal($dataId);
    }

    /**
     * @param $sectionId
     *
     * @return void
     * @deprecated never used
     */
    public function fetchAllCategoriesForSection($sectionId)
    {
    }

    /**
     * @param int|array $listCatId
     *
     * @return array
     * @deprecated never used
     */
    public function fetchSectionForCatdId($listCatId)
    {
    }

    /**
    *@deprecated
    */
    public function fetchCatIdsForSection($section_id)
    {

    }

    public function updateSectionPerCategory($cat_id, $section_id = null)
    {
        $sql = "DELETE FROM `section_category` WHERE `project_category_id`=:cat_id";
        $this->db->query($sql, array('cat_id' => $cat_id));

        if (!empty($section_id)) {
            $this->insert(['project_category_id' => $cat_id, 'section_id' => $section_id]);
        }

    }

}