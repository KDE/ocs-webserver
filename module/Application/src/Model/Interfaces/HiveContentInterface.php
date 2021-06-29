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


interface HiveContentInterface extends BaseInterface
{
    /**
     * @param int $category_id
     *
     * @return int Num of rows
     **/
    public function fetchCountProjectsForCategory($category_id);

    /**
     * @param int $category_id
     *
     * @return int Num of rows
     **/
    public function fetchCountAllProjectsForCategory($category_id);

    /**
     * @param int  $category_id Hive-Dat-Id
     * @param int  $startIndex  Default 0
     * @param int  $limit       Default 5
     * @param bool $alsoDeleted Default false
     *
     * @return int Num of rows
     */
    public function fetchAllProjectsForCategory($category_id, $startIndex = 0, $limit = 5, $alsoDeleted = false);

    /**
     * @return int Num of rows
     **/
    public function fetchCountProjects();

    /**
     * @return array
     */
    public function fetchOcsCategories();

    /**
     * @return array
     */
    public function fetchHiveCategories();

    /**
     * @param $cat_id
     *
     * @return array
     */
    public function fetchHiveCategory($cat_id);

    /**
     * @param $cat_id
     *
     * @return array
     */
    public function fetchOcsCategory($cat_id);
}