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

use Laminas\Db\ResultSet\ResultSet;

interface ConfigStoreInterface extends BaseInterface
{
    /**
     * @param $id
     *
     * @return mixed
     */
    public function fetchHostnamesForJTable($id = null);

    /**
     * @return ResultSet ArrayObject
     */
    public function queryDomainConfigIdList();

    /**
     * @param bool $clearCache
     *
     * @return array
     */
    public function fetchAllStoresAndCategories($clearCache = false);

    /**
     * @return array
     */
    public function fetchDomainConfigIdList();

    /**
     * @return array
     */
    public function fetchDomainsStoreNameList();

    /**
     * @return array
     */
    public function fetchDomainObjects();

    /**
     * @param $name
     *
     * @return array
     */
    public function fetchDomainObjectsByName($name);

    /**
     * @param false $clearCache
     *
     * @return mixed
     */
    public function fetchAllStoresConfigArray($clearCache = false);

    /**
     * @param false $clearCache
     *
     * @return mixed
     */
    public function fetchAllStoresConfigByIdArray($clearCache = false);

    /**
     * @param       $store_id
     * @param false $clearCache
     *
     * @return mixed
     */
    public function fetchConfigForStore($store_id, $clearCache = false);

    /**
     * @return array
     */
    public function fetchDefaultStoreId();

}