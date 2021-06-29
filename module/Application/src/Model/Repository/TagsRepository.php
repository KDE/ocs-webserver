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

use Application\Model\Entity\Tags;
use Application\Model\Interfaces\TagsInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\Adapter\AdapterInterface;

class TagsRepository extends BaseRepository implements TagsInterface
{
    const TAG_TYPE_PROJECT = 1;
    const TAG_TYPE_FILE = 3;
    const TAG_GROUP_USER = 5;
    const TAG_GROUP_CATEGORY = 6;
    const TAG_GROUP_LICENSE = 7;
    const TAG_GROUP_PACKAGETYPE = 8;
    const TAG_GROUP_ARCHITECTURE = 9;
    const TAG_GROUP_GHNS_EXCLUDED = 10;
    const TAG_GHNS_EXCLUDED_ID = 1529;
    protected $cache;

    public function __construct(
        AdapterInterface $db,
        StorageInterface $storage
    ) {
        parent::__construct($db);
        $this->_name = "tag";
        $this->_key = "tag_id";
        $this->_prototype = Tags::class;
        $this->cache = $storage;
    }

    /**
     * @param string $tags
     *
     * @return array
     */
    public function storeTags($tags)
    {
        $arrayTags = explode(',', $tags);

        $resultIds = array();
        foreach ($arrayTags as $tag) {
            $resultRow = $this->fetchAllRows(['tag_name' => $tag])->current();
            if (empty($resultRow)) {
                $resultIds[] = $this->insert(['tag_name' => $tag]);
            } else {
                $resultIds[] = $resultRow['tag_id'];
            }
        }

        return $resultIds;
    }

    /**
     * @param string $tags
     *
     * @return array
     */
    public function storeTagsUser($tags)
    {
        $arrayTags = explode(',', strtolower($tags));
        $resultIds = array();
        foreach ($arrayTags as $tag) {
            if (strlen(trim($tag)) == 0) {
                continue;
            }
            $resultRow = $this->fetchAllRows(['tag_name' => $tag])->current();
            if (empty($resultRow)) {
                $tagId = $this->insert(['tag_name' => $tag]);
                $resultIds[] = $tagId;
                $sql = "SELECT `tag_group_item_id` FROM `tag_group_item` WHERE `tag_group_id` = :group_id AND `tag_id` = :tag_id";
                $resultSet = $this->fetchRow($sql, array('group_id' => self::TAG_GROUP_USER, 'tag_id' => $tagId));
                if (empty($resultSet)) {
                    $this->insertTable(
                        'tag_group_item', array(
                                            'tag_group_id' => self::TAG_GROUP_USER,
                                            'tag_id'       => $tagId,
                                        )
                    );
                }
            } else {
                $resultIds[] = $resultRow['tag_id'];

                $sql = "SELECT `tag_group_item_id` FROM `tag_group_item` WHERE `tag_group_id` = :group_id AND `tag_id` = :tag_id";
                $resultSet = $this->fetchRow(
                    $sql, array(
                            'group_id' => self::TAG_GROUP_USER,
                            'tag_id'   => $resultRow['tag_id'],
                        )
                );
                if (empty($resultSet)) {
                    $this->insertTable(
                        'tag_group_item', array(
                                            'tag_group_id' => self::TAG_GROUP_USER,
                                            'tag_id'       => $resultRow['tag_id'],
                                        )
                    );
                }
            }
        }

        return $resultIds;
    }

    /**
     * @return array
     */
    public function fetchArchitectureTagsForSelect()
    {
        return $this->fetchForGroupForSelect(self::TAG_GROUP_ARCHITECTURE);
    }

    /**
     * @param int|array $groupId
     * @param bool      $withGroup
     *
     * @return array
     */
    public function fetchForGroupForSelect($groupId, $withGroup = false)
    {
        $str = is_array($groupId) ? implode(',', $groupId) : $groupId;
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_x2' . md5($str . $withGroup);

        if (!($tags = $cache->getItem($cacheName))) {
            $inQuery = '?';
            if (is_array($groupId)) {
                $inQuery = implode(',', array_fill(0, count($groupId), '?'));
            }

            $sql = "
                SELECT t.*,case when tg.group_display_name IS NULL OR LENGTH(tg.group_display_name) = 0 then tg.group_name ELSE tg.group_display_name END AS group_name  
                FROM tag AS t
                JOIN tag_group_item AS g on g.tag_id = t.tag_id
                JOIN tag_group AS tg ON tg.group_id = g.tag_group_id
                WHERE g.tag_group_id IN ($inQuery)
                and is_active = 1
                ORDER BY t.tag_fullname
                ";

           
            $tagsList = $this->fetchAll($sql, $groupId);
           
            if ($withGroup) {
                $tags['header'] = $tagsList[0]['group_name'];
            }

            foreach ($tagsList as $tag) {                
                 $tags[$tag['tag_id']] = $tag['tag_fullname'];                
            }
            
            if (count($tags) == 0) {
                $tags = array();
            }
            $cache->setItem($cacheName, $tags);
        }

        return $tags;
    }

    /**
     * @return array
     */
    public function fetchPackagetypeTagsForSelect()
    {
        return $this->fetchForGroupForSelect(self::TAG_GROUP_PACKAGETYPE);
    }

    /**
     * @return array
     */
    public function fetchLicenseTagsForSelect()
    {
        return $this->fetchForGroupForSelect(self::TAG_GROUP_LICENSE);
    }

    /**
     * @return array
     */
    public function fetchPackagetypeTagsAsJsonArray()
    {
        return $this->fetchForGroupAsJsonArray(self::TAG_GROUP_PACKAGETYPE);
    }

    /**
     * @param int|array $groupId
     *
     * @return array
     */
    public function fetchForGroupAsJsonArray($groupId)
    {
        $str = is_array($groupId) ? implode(',', $groupId) : $groupId;
        $cache = $this->cache;
        $cacheName = __FUNCTION__ . '_' . md5($str);

        if (!($tags = $cache->getItem($cacheName))) {
            $inQuery = '?';
            if (is_array($groupId)) {
                $inQuery = implode(',', array_fill(0, count($groupId), '?'));
            }

            $sql = "
                SELECT t.* FROM tag t
                JOIN tag_group_item g on g.tag_id = t.tag_id
                WHERE g.tag_group_id IN ($inQuery)
                ORDER BY t.tag_fullname
                ";

            $tagsList = $this->fetchAll($sql, $groupId);

            $tags = "{";
            $tags .= "'':'',";
            foreach ($tagsList as $tag) {
                $tags .= "'" . $tag['tag_id'] . "':'" . $tag['tag_fullname'] . "',";
            }
            $tags .= "}";
            $cache->setItem($cacheName, $tags);
        }

        return $tags;
    }

    /**
     * @param int $projectId
     *
     * @return array
     */
    public function fetchLicenseTagsForProject($projectId)
    {
        return $this->fetchTagsForProject($projectId, self::TAG_GROUP_LICENSE);
    }

    /**
     * @param int       $projectId Description
     * @param int|array $groupId
     *
     * @return array
     */
    public function fetchTagsForProject($projectId, $groupId)
    {
        $typeId = self::TAG_TYPE_PROJECT;
        $sql = "
                SELECT `to`.*, `t`.`tag_fullname`, `t`.`tag_name` FROM `tag_object` `to`
                JOIN `tag` `t` ON `t`.`tag_id` = `to`.`tag_id`
                JOIN `tag_group_item` `g` ON `g`.`tag_id` = `t`.`tag_id` AND `to`.`tag_group_id`=`g`.`tag_group_id`
                WHERE `g`.`tag_group_id` = :groupId
                AND `to`.`is_deleted` = 0
                AND `to`.`tag_type_id` = :typeId 
                AND `to`.`tag_object_id` = :projectId
                ";

        return $this->fetchAll($sql, ['groupId' => $groupId, 'typeId' => $typeId, 'projectId' => $projectId]);
    }

    /**
     * @param int $projectId
     *
     * @return array
     */
    public function fetchArchitectureTagsForProject($projectId)
    {
        return $this->fetchTagsForProject($projectId, self::TAG_GROUP_ARCHITECTURE);
    }

    /**
     * @param int $projectId
     *
     * @return array
     */
    public function fetchPackagetypeTagsForProject($projectId)
    {
        return $this->fetchTagsForProject($projectId, self::TAG_GROUP_PACKAGETYPE);
    }

    public function fetchTagByName($tag_name)
    {
        $resultRows = $this->fetchAllRows(array('tag_name' => $tag_name))->toArray();

        return array_pop($resultRows);
    }

}