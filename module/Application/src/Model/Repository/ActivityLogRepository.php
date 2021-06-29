<?php /** @noinspection PhpUnused */

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

use Application\Model\Entity\ActivityLog;
use Application\Model\Interfaces\ActivityLogInterface;
use Application\Model\Traits\DbAdapterAwareTrait;
use Exception;
use Laminas\Db\Adapter;
use Laminas\Db\Adapter\AdapterAwareInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;

class ActivityLogRepository extends BaseRepository implements AdapterAwareInterface, ActivityLogInterface
{

    const PROJECT_CREATED = 0;
    const PROJECT_UPDATED = 1;
    const PROJECT_DELETED = 2;
    const PROJECT_STOPPED = 3;
    const PROJECT_STARTED = 4;
    const PROJECT_EDITED = 7;
    const PROJECT_PUBLISHED = 8;
    const PROJECT_UNPUBLISHED = 9;
    const PROJECT_ITEM_CREATED = 10;
    const PROJECT_ITEM_UPDATED = 11;
    const PROJECT_ITEM_DELETED = 12;
    const PROJECT_ITEM_STOPPED = 13;
    const PROJECT_ITEM_STARTED = 14;
    const PROJECT_ITEM_EDITED = 17;
    const PROJECT_ITEM_PUBLISHED = 18;
    const PROJECT_ITEM_UNPUBLISHED = 19;
    const PROJECT_PLINGED = 20;
    const PROJECT_DISPLINGED = 21;
    const PROJECT_ITEM_PLINGED = 30;
    const PROJECT_ITEM_DISPLINGED = 31;
    const PROJECT_COMMENT_CREATED = 40;
    const PROJECT_COMMENT_UPDATED = 41;
    const PROJECT_COMMENT_DELETED = 42;
    const PROJECT_COMMENT_REPLY = 43;
    const PROJECT_FOLLOWED = 50;
    const PROJECT_UNFOLLOWED = 51;
    const PROJECT_SHARED = 52;
    const PROJECT_PLINGED_2 = 53;
    const PROJECT_DISPLINGED_2 = 54;
    const PROJECT_RATED_HIGHER = 60;
    const PROJECT_RATED_LOWER = 61;
    const PROJECT_FILES_CREATED = 200;
    const PROJECT_FILES_UPDATED = 210;
    const PROJECT_FILES_DELETED = 220;
    const PROJECT_LICENSE_CHANGED = 70;
    const MEMBER_JOINED = 100;
    const MEMBER_UPDATED = 101;
    const MEMBER_DELETED = 102;
    const MEMBER_EDITED = 107;
    const MEMBER_FOLLOWED = 150;
    const MEMBER_UNFOLLOWED = 151;
    const MEMBER_SHARED = 152;

    const BACKEND_LOGIN = 302;
    const BACKEND_LOGOUT = 304;
    const BACKEND_PROJECT_DELETE = 310;
    const BACKEND_PROJECT_FEATURE = 312;
    const BACKEND_PROJECT_GHNS_EXCLUDED = 314;
    const BACKEND_PROJECT_CAT_CHANGE = 316;
    const BACKEND_PROJECT_PLING_EXCLUDED = 318;
    const BACKEND_USER_PLING_EXCLUDED = 319;
    const BACKEND_USER_DELETE = 320;
    const BACKEND_USER_UNDELETE = 321;
    const BACKEND_PROJECT_DANGEROUS = 322;
    const BACKEND_PROJECT_DEPRECATED = 323;

    //internal system logs
    const MEMBER_EMAIL_CONFIRMED = 401;
    const MEMBER_EMAIL_CHANGED = 402;
    const MEMBER_PAYPAL_CHANGED = 410;

    protected static $referenceType = array(
        0   => 'project',
        1   => 'project',
        2   => 'project',
        3   => 'project',
        4   => 'project',
        7   => 'project',
        8   => 'project',
        9   => 'project',
        10  => 'update',
        11  => 'update',
        12  => 'update',
        13  => 'update',
        14  => 'update',
        17  => 'update',
        18  => 'update',
        19  => 'update',
        20  => 'pling',
        30  => 'pling',
        40  => 'comment',
        41  => 'comment',
        42  => 'comment',
        43  => 'comment',
        50  => 'project',
        51  => 'project',
        52  => 'project',
        53  => 'project',
        54  => 'project',
        60  => 'project',
        61  => 'project',
        70  => 'project',
        100 => 'member',
        101 => 'member',
        102 => 'member',
        104 => 'undefined',
        107 => 'member',
        108 => 'undefined',
        109 => 'undefined',
        150 => 'member',
        151 => 'member',
        152 => 'member',
        200 => 'project',
        210 => 'project',
        220 => 'project',
        302 => 'backend',
        304 => 'backend',
        310 => 'backend',
        312 => 'backend',
        314 => 'backend',
        316 => 'backend',
        318 => 'backend',
        319 => 'backend',
        320 => 'backend',
        321 => 'backend',
        322 => 'backend',
        323 => 'backend',
        401 => 'member',
        402 => 'member_email',
        410 => 'member',
    );

    use DbAdapterAwareTrait;

    public function __construct(AdapterInterface $db)
    {
        $this->db = $db;
        $this->_name = "activity_log";
        $this->_key = "activity_log_id";
        $this->_prototype = ActivityLog::class;
    }

    /**
     * @param int   $objectId
     * @param int   $projectId
     * @param int   $userId
     * @param int   $activity_type_id
     * @param array $data array with ([type_id], [pid], description, title, image_small)
     */
    public static function logActivity($objectId, $projectId, $userId, $activity_type_id, $data = array())
    {
        // cutting description text if necessary
        if (isset($data['description'])) {
            $object_text = (strlen($data['description']) < 150) ? $data['description'] : mb_substr(
                                                                                             $data['description'], 0, 145, 'UTF-8'
                                                                                         ) . ' ... ';
        }

        $newEntry = array(
            'member_id'     => $userId,
            'project_id'    => $projectId,
            'object_id'     => $objectId,
            'object_ref'    => self::$referenceType[$activity_type_id],
            'object_title'  => isset($data['title']) ? $data['title'] : null,
            'object_text'   => isset($object_text) ? $object_text : null,
            'object_img'    => false === empty($data['image_small']) ? $data['image_small'] : null,
            'activity_type' => $activity_type_id,
        );
        $sql = "
            INSERT INTO `activity_log`
            SET `member_id` = :member_id, 
                `project_id` = :project_id, 
                `object_id` = :object_id, 
                `object_ref` = :object_ref,
                `object_title` = :object_title,
                `object_text` = :object_text,
                `object_img` = :object_img,
                `activity_type_id` = :activity_type
                ;
        ";
        try {
            $adapter = GlobalAdapterFeature::getStaticAdapter();
            $query = $adapter->query($sql, Adapter\Adapter::QUERY_MODE_PREPARE);
            $result = $query->execute($newEntry);
        } catch (Exception $e) {
            error_log(__METHOD__ . ' - ERROR write activity log - ' . print_r($e, true));
        }
    }

    /**
     * @param int         $objectId
     * @param int         $userId
     * @param int         $activity_type_id
     * @param array|mixed $data
     *
     * @throws Exception
     */
    public function writeActivityLog($objectId, $userId, $activity_type_id, $data)
    {
        $projectId = $objectId;

        //is it an item?
        if (isset($data['type_id']) && $data['type_id'] == ProjectRepository::PROJECT_TYPE_UPDATE) {
            $projectId = $data['pid'];
        }
        $object_text = (strlen($data['description']) < 150) ? $data['description'] : mb_substr(
                                                                                         $data['description'], 0, 145, 'UTF-8'
                                                                                     ) . ' ... ';

        $newLog = array(
            'member_id'        => $userId,
            'project_id'       => $projectId,
            'object_id'        => $objectId,
            'object_ref'       => self::$referenceType[$activity_type_id],
            'object_title'     => $data['title'],
            'object_text'      => $object_text,
            'object_img'       => $data['image_small'],
            'activity_type_id' => $activity_type_id,
        );

        try {
            $sql = new Sql(GlobalAdapterFeature::getStaticAdapter());
            $insert = $sql->insert('activity_log')->values($newLog);
            $statement = $sql->prepareStatementForSqlObject($insert);
            $statement->execute();
        } catch (Exception $e) {
            error_log(__METHOD__ . ' - ERROR write activity log - ' . $e->getMessage());
        }
    }

}
