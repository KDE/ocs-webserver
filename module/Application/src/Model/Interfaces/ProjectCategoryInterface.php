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

use Application\Model\Entity\ProjectCategoryData;
use Application\Model\Repository\ProjectCategoryRepository;

interface ProjectCategoryInterface extends BaseInterface
{
    public function setStatus($status, $id);

    public function setDelete($id);

    public function fetchActive($nodeId, $clearCache = false);

    public function fetchActiveOrder($nodeId);

    public function findCategory($nodeId);

    public function setCategoryDeleted($id, $updateChildren = true);

    public function fetchRoot();

    public function addNewElement($data);

    public function appendNewElement($title);

    public function fetchTree(
        $isActive = false,
        $withRoot = true,
        $depth = null
    );

    public function fetchTreeForJTable($cat_id);

    public function fetchTreeWithParentId(
        $isActive = true,
        $depth = null
    );

    public function fetchTreeWithParentIdAndTags(
        $isActive = true,
        $depth = null
    );

    public function fetchTreeWithParentIdAndTagGroups(
        $isActive = true,
        $depth = null
    );

    public function fetchTreeWithParentIdAndSections(
        $isActive = true,
        $depth = null
    );

    /**
     * @param integer $cat_id
     *
     * @return array
     */
    public function fetchTreeForJTableStores($cat_id);

    public function fetchTreeForJTableSection($cat_id);

    public function fetchTreeForCategoryStores($cat_id);

    public function fetchParentForId($data);

    public function findPreviousSibling($data);

    public function findNextSibling($data);

    public function findPreviousElement($data);

    public function findNextElement($data);

    public function fetchChildTree($nodeId, $options = array());

    public function fetchChildElements($nodeId, $isActive = true);

    public function fetchChildIds($nodeId, $isActive = true);

    public function fetchImmediateChildrenIds($nodeId, $orderBy = ProjectCategoryRepository::ORDERED_HIERARCHIC);

    public function fetchMainCategories($returnAmount = 25, $fetchLimit = 25);

    public function fetchMainCatIdsOrdered();

    public function fetchMainCatsOrdered();

    public function fetchSubCatIds($cat_id, $orderBy);

    public function fetchRandomCategories($returnAmount = 5, $fetchLimit = 25);

    public function fetchElement($nodeId);

    public function moveTo($node, $newLeftPosition);

    public function moveToParent($currentNodeId, $newParentNodeId, $position = 'top');

    public function fetchMainCategoryForProduct($productId);

    public function countSubCategories($cat_id);

    public function fetchImmediateChildren($nodeId, $orderBy = 'lft');

    public function fetchMainCatForSelect($orderBy);

    public function fetchAncestorsAsId($catId);

    public function fetchCategoriesForForm($valueCatId);

    public function fetchCategoryTreeWithTags($store_id = null, $tags = null);

    public function fetchTreeForView($store_id = null);

    public function fetchImmediateChildrenNew($nodeId, $orderBy = 'lft');

    public function fetchMainCatForSelectNew($orderBy);

    public function fetchCategoriesForFormNew($valueCatId);

    /**
     * @param integer $cat_id
     *
     * @return ProjectCategoryData
     */
    public function readCategoryData($cat_id);

    public function fetchCatNames();

}