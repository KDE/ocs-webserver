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

namespace Application\Model\Service\Interfaces;

interface ProjectCategoryServiceInterface
{
    const ORDERED_HIERARCHIC = 'lft';

    /**
     * @return mixed|null
     */
    public static function fetchCatIdsForCurrentStore();

    /**
     * @param null $store_id
     *
     * @return array
     */
    public function fetchTreeForView($store_id = null);

    /**
     * @param null $store_id
     *
     * @return array
     */
    public function fetchTreeForViewForProjectFavourites($store_id = null, $member_id = null);

    /**
     * @param null $store_id
     *
     * @return array
     */
    public function fetchTreeForViewForProjectTagGroupTags($store_id = null, $storeTagFilter = null, $tagFilter = null);

    /**
     * @param int|null $store_id If not set, the tree for the current store will be returned
     * @param bool     $clearCache
     *
     * @return array
     * @deprecated use fetchTreeForView
     */
    public function fetchCategoryTreeForStore($store_id = null, $clearCache = false);

    /**
     * @param null $section_id
     * @param bool $clearCache
     *
     * @return array
     * @deprecated use fetchTreeForView
     */
    public function fetchCategoryTreeForSection($section_id = null, $clearCache = false);

    /**
     * @param bool $clearCache
     *
     * @return array|false|mixed
     *
     * @deprecated use fetchTreeForView
     */
    public function fetchCategoryTreeCurrentStore($clearCache = false);

    /**
     * @return array
     */
    public function fetchCatNamesForCurrentStore();

    /**
     * @return array
     */
    public function fetchCatNamesForID($list_cat_id);

    /**
     * @return array
     */
    public function fetchCatNames();

    /**
     * @param int    $category_id
     * @param string $orderBy
     *
     * @return array
     */
    public function fetchAllSubCategories($category_id, $orderBy = self::ORDERED_HIERARCHIC);
}