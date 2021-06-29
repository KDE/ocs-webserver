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

namespace Application\Model\Service;

use Application\Model\Service\Interfaces\DwhdataServiceInterface;
use Laminas\Db\Adapter\Adapter;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;

class DwhdataService extends BaseService implements DwhdataServiceInterface
{
    private $db;

    public function __construct(Adapter $dbAdapter)
    {
        $this->db = $dbAdapter;
    }

    public function getDownloadhistory($member_id)
    {
        $sql = "SELECT 
             `m`.`member_id`
             ,`m`.`collection_id`
             ,`m`.`project_id`
             ,`m`.`file_id`
             ,`m`.`user_id`
             ,`m`.`downloaded_timestamp`
             ,`m`.`downloaded_ip`
             ,`p`.`project_category_id`
             ,(SELECT `c`.`title` FROM `category` `c` WHERE `p`.`project_category_id` = `c`.`project_category_id`) AS `catTitle`
             ,`p`.`title`                               
             ,`p`.`laplace_score`
             ,`p`.`image_small`
             ,`p`.`count_likes`
             ,`p`.`count_dislikes`
             ,`f`.`name` AS `file_name`
             ,`f`.`type` AS `file_type`
             ,`f`.`size` AS `file_size`
             ,`f`.`ocs_compatible` AS `file_ocs_compatible`
             ,`f`.`downloaded_count` AS `file_downloaded_count`
             ,`f`.`active` AS `file_active`
             ,(SELECT max(`d`.`downloaded_timestamp`) FROM `dwh`.`member_dl_history` `d` WHERE `m`.`project_id` = `d`.`project_id` AND `d`.`user_id` = `m`.`user_id`) AS `max_downloaded_timestamp`
             FROM `dwh`.`member_dl_history` `m`
             JOIN `dwh`.`project` `p` ON `p`.`project_id` = `m`.`project_id`
             JOIN `dwh`.`files` `f` ON `m`.`file_id` = `f`.`id`
             WHERE `m`.`user_id` = :member_id
             ORDER BY `m`.`project_id`, `m`.`downloaded_timestamp` DESC
                     ";
        $result = $this->db->query($sql, array("member_id" => $member_id));

        return new Paginator(new ArrayAdapter($result->toArray()));
    }
}