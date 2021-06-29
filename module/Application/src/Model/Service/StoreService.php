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

namespace Application\Model\Service;


use Application\Model\Service\Interfaces\StoreServiceInterface;
use Laminas\Cache\Storage\Adapter\AbstractAdapter;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;
use RuntimeException;

class StoreService implements StoreServiceInterface
{

    /**
     * @var Adapter
     */
    private $db_adapter;
    /**
     * @var AbstractAdapter
     */
    private $cache;

    public function __construct(Adapter $dbAdapter, AbstractAdapter $cache)
    {
        $this->db_adapter = $dbAdapter;
        $this->cache = $cache;
    }

    /**
     * @param int  $store_id
     * @param bool $onlyActive
     *
     * @return null|array
     */
    public function getTagsAsIdForStore($store_id, $onlyActive = true)
    {
        $sql = "SELECT `tag_id` FROM `config_store_tag` WHERE `store_id` = :store_id AND `is_active` = :active ORDER BY `tag_id`;";
        $params = array('store_id' => $store_id, 'active' => ($onlyActive ? 1 : 0));

        $resultSet = $this->readFromDb($store_id, $sql, $params);

        return ($resultSet->count() > 0) ? $resultSet->toArray() : null;
    }

    /**
     * @param       $store_id
     * @param       $sql
     * @param array $params
     *
     * @return ResultSet
     */
    public function readFromDb($store_id, $sql, array $params)
    {
        $statement = $this->db_adapter->driver->createStatement($sql);
        $statement->prepare();
        $result = $statement->execute($params);

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet();
            $resultSet->initialize($result);
        } else {
            throw new RuntimeException(
                sprintf(
                    'Failed retrieving ' . $store_id . ' with identifier "%s"; unknown database error.', $sql
                )
            );
        }

        return $resultSet;
    }

    public function getPackageTagsForStore($store_id, $onlyActive = true)
    {

        $sql = "
                SELECT `t`.`tag_id`, `t`.`tag_name` 
                FROM `config_store_tag` `c` , `tag` `t`
                WHERE `c`.`tag_id` = `t`.`tag_id`
                AND  `c`.`store_id` = :store_id AND `c`.`is_active` = :active
             ";
        $params = array('store_id' => $store_id, 'active' => ($onlyActive ? 1 : 0));

        $resultSet = $this->readFromDb($store_id, $sql, $params);

        return ($resultSet->count() > 0) ? $resultSet->toArray() : null;
    }

    /**
     * @param int  $store_id
     * @param bool $onlyActive
     *
     * @return null|array
     */
    public function getTagGroupsAsIdForStore($store_id, $onlyActive = true)
    {

        $sql = "SELECT `tag_group_id` FROM `config_store_tag_group` WHERE `store_id` = :store_id AND `is_active` = :active ORDER BY `tag_group_id`;";
        $params = array('store_id' => $store_id, 'active' => ($onlyActive ? 1 : 0));

        $resultSet = $this->readFromDb($store_id, $sql, $params);

        return ($resultSet->count() > 0) ? array_column($resultSet->toArray(), 'tag_group_id') : null;
    }

    public function getCategoriesAsIdForStore($store_id)
    {
        $sql = "
                SELECT
                    `config_store_category`.`project_category_id`
                FROM
                    `config_store`
                JOIN
                    `config_store_category` ON `config_store`.`store_id` = `config_store_category`.`store_id`
                JOIN
                    `project_category` ON `project_category`.`project_category_id` = `config_store_category`.`project_category_id`
                WHERE `config_store`.`store_id` = :store_id
                ORDER BY `config_store_category`.`order`,`project_category`.`title`;
        ";
        $params = array('store_id' => $store_id);

        $resultSet = $this->readFromDb($store_id, $sql, $params);
        $returnArray = $resultSet->toArray();

        return count($returnArray) > 0 ? array_column($returnArray, 'project_category_id') : $returnArray;
    }

}