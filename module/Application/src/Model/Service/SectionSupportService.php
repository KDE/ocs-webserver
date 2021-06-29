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
 * */

namespace Application\Model\Service;

use Application\Model\Repository\SectionRepository;
use Application\Model\Service\Interfaces\SectionSupportServiceInterface;
use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;

class SectionSupportService extends BaseService implements SectionSupportServiceInterface
{

    protected $db;
    protected $cache;
    private $sectionRepository;

    public function __construct(
        AdapterInterface $db
    ) {
        $this->db = $db;
        $this->cache = $GLOBALS['ocs_cache'];
        $this->sectionRepository = new SectionRepository($db, $this->cache);
    }

    public function fetchAffiliatesForProject($project_id)
    {

        $sql = "
                    SELECT 
                    `f`.`project_id`
                    ,`m`.`member_id`
                    ,`s`.`active_time`
                    ,`m`.`profile_image_url`
                    ,`m`.`created_at` AS `member_created_at`
                    ,`m`.`username`
                    FROM `section_support_paypements` `p`
                    INNER JOIN `section_support` `f` ON `f`.`section_support_id` = `p`.`section_support_id`
                    INNER JOIN `support` `s` ON `s`.`id` = `f`.`support_id`
                    INNER JOIN `member` `m` ON `s`.`member_id` = `m`.`member_id` AND `m`.`is_active`=1 AND `m`.`is_deleted`=0 
                    WHERE  `f`.`project_id` = :project_id 
                    AND `p`.`yearmonth` = date_format((now()),'%Y%m')
                    ORDER BY `s`.`active_time` DESC
         ";

        return $this->sectionRepository->fetchAll($sql, array('project_id' => (int)$project_id));
    }

    public function fetchAffiliatesCntForProject($project_id)
    {

        $sql = "
                    SELECT 
                   count(1) AS `count`
                    FROM `section_support_paypements` `p`
                    INNER JOIN `section_support` `f` ON `f`.`section_support_id` = `p`.`section_support_id`
                    INNER JOIN `support` `s` ON `s`.`id` = `f`.`support_id`
                    INNER JOIN `member` `m` ON `s`.`member_id` = `m`.`member_id` AND `m`.`is_active`=1 AND `m`.`is_deleted`=0 
                    WHERE  `f`.`project_id` = :project_id 
                    AND `p`.`yearmonth` = date_format((now()),'%Y%m')
                   
         ";
        $resultSet = $this->sectionRepository->fetchRow($sql, array('project_id' => (int)$project_id));

        return $resultSet['count'];
    }

    public function isMemberAffiliateForProject($project_id, $member_id)
    {

        $cacheName = __FUNCTION__ . md5(serialize($project_id) . '' . serialize($member_id));
        $cache = $this->cache;

        $result = $cache->getItem($cacheName);

        if ($result) {
            return $result;
        }

        $isAffiliate = false;

        $sql_object = "SELECT 1
                FROM `section_support_paypements` `p`
                    INNER JOIN `section_support` `f` ON `f`.`section_support_id` = `p`.`section_support_id`
                    INNER JOIN `support` `s` ON `s`.`id` = `f`.`support_id`
                    INNER JOIN `member` `m` ON `s`.`member_id` = `m`.`member_id` AND `m`.`is_active`=1 AND `m`.`is_deleted`=0 
                WHERE  `f`.`project_id` = :project_id 
                AND `p`.`yearmonth` = date_format((now()),'%Y%m')
                AND `m`.`member_id` = :member_id";
        $r = $this->sectionRepository->fetchRow(
            $sql_object, array(
                           'project_id' => (int)$project_id,
                           'member_id'  => (int)$member_id,
                       )
        );
        if ($r) {
            $isAffiliate = true;
        }

        $cache->setItem($cacheName, $isAffiliate);

        return $isAffiliate;
    }

    public function isMemberAffiliateForMember($member_id, $affiliate_member_id)
    {

        $cacheName = __FUNCTION__ . md5(serialize($member_id) . '' . serialize($affiliate_member_id));
        $cache = $this->cache;

        $result = $cache->getItem($cacheName);

        if ($result) {
            return $result;
        }

        $isAffiliate = false;

        $sql_object = "SELECT 1
                FROM `section_support_paypements` `pm`
                INNER JOIN `section_support` `f` ON `f`.`section_support_id` = `pm`.`section_support_id`
                INNER JOIN `project` `p` ON `p`.`project_id` = `f`.`project_id`
                INNER JOIN `support` `s` ON `s`.`id` = `f`.`support_id`
                INNER JOIN `member` `m` ON `s`.`member_id` = `m`.`member_id` AND `m`.`is_active`=1 AND `m`.`is_deleted`=0 
                INNER JOIN `member` `m2` ON `p`.`member_id` = `m2`.`member_id` AND `m2`.`is_active`=1 AND `m2`.`is_deleted`=0 
                WHERE  `p`.`member_id` = :member_id
                AND `m`.`member_id` = :affiliate_member_id
                AND `pm`.`yearmonth` = date_format((now()),'%Y%m')
";
        $r = $this->sectionRepository->fetchRow(
            $sql_object, array(
                           'affiliate_member_id' => (int)$affiliate_member_id,
                           'member_id'           => (int)$member_id,
                       )
        );
        if ($r) {
            $isAffiliate = true;
        }

        $cache->setItem($cacheName, $isAffiliate);

        return $isAffiliate;
    }

    public function wasMemberAffiliateForMember($member_id, $affiliate_member_id)
    {

        $cacheName = __FUNCTION__ . md5(serialize($member_id) . '' . serialize($affiliate_member_id));
        $cache = $this->cache;

        $result = $cache->getItem($cacheName);

        if ($result) {
            return $result;
        }

        $isAffiliate = false;

        $sql_object = "SELECT 1
                FROM `section_support` `f`
                INNER JOIN `project` `p` ON `p`.`project_id` = `f`.`project_id`
                INNER JOIN `support` `s` ON `s`.`id` = `f`.`support_id`
                INNER JOIN `member` `m` ON `s`.`member_id` = `m`.`member_id` AND `m`.`is_active`=1 AND `m`.`is_deleted`=0 
                INNER JOIN `member` `m2` ON `p`.`member_id` = `m2`.`member_id` AND `m2`.`is_active`=1 AND `m2`.`is_deleted`=0 
                WHERE  `p`.`member_id` = :member_id
                AND `m`.`member_id` = :affiliate_member_id
                AND `s`.`status_id` = 99";
        $r = $this->sectionRepository->fetchRow(
            $sql_object, array(
                           'affiliate_member_id' => (int)$affiliate_member_id,
                           'member_id'           => (int)$member_id,
                       )
        );
        if ($r) {
            $isAffiliate = true;
        }

        $cache->setItem($cacheName, $isAffiliate);

        return $isAffiliate;
    }

    /**
     * @param int $member_id
     *
     * @return array|ResultSet|mixed|null
     */
    public function fetchAffiliatesForMember($member_id)
    {
        $cacheName = __FUNCTION__ . '_' . $member_id;
        if ($result = $this->readCache($cacheName)) {
            return $result;
        }

        $isAffiliate = false;

        $sql_object = "SELECT DISTINCT 
                    `p`.`member_id`
                    ,`s`.`active_time`
                    ,`m`.`profile_image_url`
                    ,`m`.`created_at` AS `member_created_at`
                    ,`m`.`username`
                    FROM `section_support` `f`
                    INNER JOIN `support` `s` ON `s`.`id` = `f`.`support_id`
                    INNER JOIN `project` `p` ON `p`.`project_id` = `f`.`project_id`
                    INNER JOIN `member` `m` ON `s`.`member_id` = `m`.`member_id` AND `m`.`is_active`=1 AND `m`.`is_deleted`=0 
                    WHERE  `p`.`member_id` = :member_id 
                    AND `s`.`status_id` = 2
                    ORDER BY `s`.`active_time` DESC";
        $r = $this->sectionRepository->fetchAll($sql_object, array('member_id' => (int)$member_id));
        $this->writeCache($cacheName, $r, 600);

        return $r;
    }

}
