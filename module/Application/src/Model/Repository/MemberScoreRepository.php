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

use Application\Model\Entity\MemberScore;
use Application\Model\Interfaces\MemberScoreInterface;
use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
use Laminas\Db\Adapter\AdapterInterface;


class MemberScoreRepository extends BaseRepository implements MemberScoreInterface
{

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "member_score";
        $this->_key = "member_score_id";
        $this->_prototype = MemberScore::class;
    }

    /**
     * @param int $member_id
     *
     * @return array
     */
    public function fetchScore($member_id)
    {
        $cache_name = __FUNCTION__ . '_' . $member_id;
        if ($result = $this->readCache($cache_name)) {
            return $result;
        }

        $sql = "
                SELECT
                    `p`.*,
                 (SELECT `value` FROM `member_score_factors` `f` WHERE `f`.`factor_id` = 1) AS `factor_prod`,
                 (SELECT `value` FROM `member_score_factors` `f` WHERE `f`.`factor_id` = 2) AS `factor_pling`,
                 (SELECT `value` FROM `member_score_factors` `f` WHERE `f`.`factor_id` = 3) AS `factor_like`,
                 (SELECT `value` FROM `member_score_factors` `f` WHERE `f`.`factor_id` = 4) AS `factor_comment`,
                 (SELECT `value` FROM `member_score_factors` `f` WHERE `f`.`factor_id` = 5) AS `factor_year`,
                 (SELECT `value` FROM `member_score_factors` `f` WHERE `f`.`factor_id` = 6) AS `factor_report_prod_spam`,
                 (SELECT `value` FROM `member_score_factors` `f` WHERE `f`.`factor_id` = 7) AS `factor_report_prod_fraud`

                 FROM
                     `member_score` `p`            
                 WHERE
                     `member_id` = :member_id               
                ;                  
               ";

        $result = $this->fetchRow($sql, array('member_id' => $member_id));
        $this->writeCache($cache_name, $result, 600);

        return $result;
    }

    public function fetchTopUsers($limit = 100)
    {
        $sql = "
                    SELECT  
                    `s`.*
                    ,`m`.`profile_image_url`
                    ,`m`.`username`
                    FROM `member_score` `s`
                    INNER JOIN `member` `m` ON `s`.`member_id` = `m`.`member_id`
                    ORDER BY `s`.`score` DESC             
            ";
        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        return $this->fetchAll($sql, null, false);
    }

}
