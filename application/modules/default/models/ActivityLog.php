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
class Default_Model_ActivityLog extends Default_Model_DbTable_ActivityLog
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
    const PROJECT_ITEM_PLINGED = 30;
    const PROJECT_COMMENT_CREATED = 40;
    const PROJECT_COMMENT_UPDATED = 41;
    const PROJECT_COMMENT_DELETED = 42;
    const PROJECT_COMMENT_REPLY = 43;
    const PROJECT_FOLLOWED = 50;
    const PROJECT_UNFOLLOWED = 51;
    const PROJECT_SHARED = 52;
    const PROJECT_RATED_HIGHER = 60;
    const PROJECT_RATED_LOWER = 61;
    const PROJECT_FILES_CREATED = 200;
    const PROJECT_FILES_UPDATED = 210;
    const PROJECT_FILES_DELETED = 220;
    const MEMBER_JOINED = 100;
    const MEMBER_UPDATED = 101;
    const MEMBER_DELETED = 102;
    const MEMBER_EDITED = 107;
    const MEMBER_FOLLOWED = 150;
    const MEMBER_UNFOLLOWED = 151;
    const MEMBER_SHARED = 152;

    protected static $referenceType = array(
        0 => 'project',
        1 => 'project',
        2 => 'project',
        3 => 'project',
        4 => 'project',
        7 => 'project',
        8 => 'project',
        9 => 'project',
        10 => 'update',
        11 => 'update',
        12 => 'update',
        13 => 'update',
        14 => 'update',
        17 => 'update',
        18 => 'update',
        19 => 'update',
        20 => 'pling',
        30 => 'pling',
        40 => 'comment',
        41 => 'comment',
        42 => 'comment',
        43 => 'comment',
        50 => 'project',
        51 => 'project',
        52 => 'project',
        60 => 'project',
        61 => 'project',
        100 => 'member',
        101 => 'member',
        102 => 'member',
        103 => 'undefined',
        104 => 'undefined',
        107 => 'member',
        108 => 'undefined',
        109 => 'undefined',
        150 => 'member',
        151 => 'member',
        152 => 'member',
        200 => 'project',
        210 => 'project',
        220 => 'project'
    );

    /**
     * @param int $id
     * @return mixed
     * @deprecated
     */
    public function fetchAllByMemberId($id)
    {

        $q = $this->select()
            ->where('member_id = ?', $id);

        return $q->query()->fetch();
    }

    /**
     * @param int $limit
     * @return array
     * @deprecated
     */
    public function fetchLastActivities($limit)
    {
        //TODO: database bottleneck
        $sel = $this->select()->setIntegrityCheck(false)->from($this->_name, array(
            '*',
            'project_2.project_id as obj_project_id',
            'project_2.image_small as obj_image_small',
            'project_2.title as obj_title',
            'project_2.description as obj_description',
            'project_2.type_id as obj_type_id',
            'project.project_id as prj_project_id',
            'project.image_small as prj_image_small',
            'daysAgo' => 'datediff(NOW(),activity_log.time)',
            'hoursAgo' => 'TIMESTAMPDIFF(HOUR,activity_log.time,NOW())',
            'minsAgo' => 'TIMESTAMPDIFF(MINUTE,activity_log.time,NOW())'
        ))
            ->join('member', 'member.member_id = activity_log.member_id')
            ->joinInner('activity_log_types', 'activity_log_types.activity_log_type_id = activity_log.activity_type_id')
            ->joinInner('project', 'project.project_id = activity_log.project_id')
            ->where('project.status = ?', Default_Model_Project::PROJECT_ACTIVE)
            ->joinInner('project', 'project_2.project_id = activity_log.object_id')
            ->where('project_2.status = ?', Default_Model_Project::PROJECT_ACTIVE)
            ->limit($limit)
            ->order('time desc');

        $logArr = $this->fetchAll($sel)->toArray();

        return $logArr;
    }

    /**
     * @param int $member_id
     * @param int $limit
     * @return array
     * @deprecated
     */
    public function fetchLastActivitiesForMember($member_id, $limit = null)
    {
        $sel = $this->select()->setIntegrityCheck(false)->from($this->_name, array(
            '*',
            'project_2.project_id as obj_project_id',
            'project_2.image_small as obj_image_small',
            'project_2.title as obj_title',
            'project_2.description as obj_description',
            'project_2.type_id as obj_type_id',
            'project.project_id as prj_project_id',
            'project.image_small as prj_image_small',
            'daysAgo' => 'datediff(NOW(),activity_log.time)',
            'hoursAgo' => 'TIMESTAMPDIFF(HOUR,activity_log.time,NOW())',
            'minsAgo' => 'TIMESTAMPDIFF(MINUTE,activity_log.time,NOW())'
        ))
            ->join('member', 'member.member_id = activity_log.member_id')
            ->joinInner('activity_log_types', 'activity_log_types.activity_log_type_id = activity_log.activity_type_id')
            ->joinInner('project', 'project.project_id = activity_log.project_id')
            ->where('project.status = ?', Default_Model_Project::PROJECT_ACTIVE)
            ->where('activity_log.member_id = ?', $member_id)
            ->joinInner('project', 'project_2.project_id = activity_log.object_id')
            ->where('project_2.status = ?', Default_Model_Project::PROJECT_ACTIVE)
            ->order('time desc');

        if (null !== $limit) {
            $sel->limit($limit);
        }

        $logArr = $this->fetchAll($sel)->toArray();

        return $logArr;
    }

    /**
     * @param int $member_id
     * @param int $limit
     * @return array
     * @deprecated
     */
    public function fetchLastActivitiesForGroup($member_id, $limit = null)
    {
        $sel = $this->select()->setIntegrityCheck(false)->from($this->_name, array(
            '*',
            'project_2.project_id as obj_project_id',
            'project_2.image_small as obj_image_small',
            'project_2.title as obj_title',
            'project_2.description as obj_description',
            'project_2.type_id as obj_type_id',
            'project.project_id as prj_project_id',
            'project.image_small as prj_image_small',
            'daysAgo' => 'datediff(NOW(),activity_log.time)',
            'hoursAgo' => 'TIMESTAMPDIFF(HOUR,activity_log.time,NOW())',
            'minsAgo' => 'TIMESTAMPDIFF(MINUTE,activity_log.time,NOW())'
        ))
            #->join( 'member', 'member.member_id = activity_log.member_id')
            ->joinInner('activity_log_types', 'activity_log_types.activity_log_type_id = activity_log.activity_type_id')
            ->joinInner('project', 'project.project_id = activity_log.project_id')
            ->where('project.status = ?', Default_Model_Project::PROJECT_ACTIVE)
            ->where('activity_log.member_id = ?', $member_id)
            ->join('member', 'member.member_id = project.creator_id')
            ->joinInner('project', 'project_2.project_id = activity_log.object_id')
            ->where('project_2.status = ?', Default_Model_Project::PROJECT_ACTIVE)
            ->order('time desc');

        if (null !== $limit) {
            $sel->limit($limit);
        }

        $logArr = $this->fetchAll($sel)->toArray();

        return $logArr;
    }

    /**
     * @param array $memberArray
     * @param null $limit
     * @return array
     */
    public function fetchActivitiesOfFollowedMembers($memberArray, $limit = null)
    {
        $logArr = array();
        if (count($memberArray) > 0) {
            $membersString = '';
            foreach ($memberArray as $row) {
                $membersString .= $row ['member_id'] . ',';
            }
            $membersString = substr($membersString, 0, -1);

            $sel = $this->select()->setIntegrityCheck(false)->from($this->_name, array(
                '*',
                'member_2.username',
                'member_2.profile_image_url as creator_profile_image_url',
                'project.member_id as project_member_id',
                'member.username as project_username',
                'project_2.project_id as obj_project_id',
                'project_2.image_small as obj_image_small',
                'project_2.title as obj_title',
                'project_2.description as obj_description',
                'project_2.type_id as obj_type_id',
                'project.project_id as prj_project_id',
                'project.image_small as prj_image_small',
                'daysAgo' => 'datediff(NOW(),activity_log.time)',
                'hoursAgo' => 'TIMESTAMPDIFF(HOUR,activity_log.time,NOW())',
                'minsAgo' => 'TIMESTAMPDIFF(MINUTE,activity_log.time,NOW())'
            ))
                ->joinInner('activity_log_types',
                    'activity_log_types.activity_log_type_id = activity_log.activity_type_id')
                ->joinInner('project', 'project.project_id = activity_log.project_id')
                ->where('project.status = ?', Default_Model_Project::PROJECT_ACTIVE)
                ->joinInner('project', 'project_2.project_id = activity_log.object_id')
                ->where('project_2.status = ?', Default_Model_Project::PROJECT_ACTIVE)
                ->joinInner('member', 'member.member_id = project.member_id')
                ->joinInner('member', 'member_2.member_id = activity_log.member_id')
                ->where('activity_log.member_id in (' . $membersString . ')')
                ->where('activity_log.activity_type_id in (0,10,20)')
                ->order('activity_log.time desc');

            if (null !== $limit) {
                $sel->limit($limit);
            }
            $logArr = $this->fetchAll($sel)->toArray();

        } else {

        }

        return $logArr;
    }

    /**
     * @param array $projectArray
     * @param int|null $limit
     * @return array
     */
    public function fetchActivitiesOfFollowedProjects($projectArray, $limit = 10)
    {
        $logArr = array();

        if (count($projectArray) == 0) {
            return $logArr;
        }
        $projectsString = implode(',', array_map(function ($element) {
            return $element['project_id'];
        }, $projectArray));

        $sel = $this->select()->setIntegrityCheck(false)->from($this->_name, array(
            '*',
            'daysAgo' => 'datediff(NOW(),activity_log.time)',
            'hoursAgo' => 'TIMESTAMPDIFF(HOUR,activity_log.time,NOW())',
            'minsAgo' => 'TIMESTAMPDIFF(MINUTE,activity_log.time,NOW())'
        ))
            ->joinInner(array('project_base' => 'project'), 'project_base.project_id = activity_log.project_id')
            ->joinLeft(array('log_types' => 'activity_log_types'),
                'log_types.activity_log_type_id = activity_log.activity_type_id',
                array('type_text'))
            ->where('project_base.status = ?', Default_Model_Project::PROJECT_ACTIVE)
            ->where('activity_log.project_id in (' . $projectsString . ')')
            ->where('activity_log.activity_type_id in (0,10)')
            ->order('activity_log.time desc')
            ->limit($limit);

        $logArr = $this->fetchAll($sel)->toArray();

        return $logArr;
    }

    /**
     * @param int $objectId
     * @param int $userId
     * @param int $activity_type_id
     * @param array|mixed $data
     */
    public function writeActivityLog($objectId, $userId, $activity_type_id, $data)
    {
        $projectId = $objectId;

        //is it an item?
        if (isset($data['type_id']) && $data['type_id'] == Default_Model_Project::PROJECT_TYPE_UPDATE) {
            $projectId = $data['pid'];
        }
        $object_text = (strlen($data['description']) < 150) ? $data['description'] : mb_substr($data['description'], 0,
                145, 'UTF-8') . ' ... ';

        $newLog = array(
            'member_id' => $userId,
            'project_id' => $projectId,
            'object_id' => $objectId,
            'object_ref' => self::$referenceType[$activity_type_id],
            'object_title' => $data['title'],
            'object_text' => $object_text,
            'object_img' => $data['image_small'],
            'activity_type_id' => $activity_type_id
        );

        try {
            $this->insert($newLog);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ERROR write activity log - ' . print_r($e, true));
        }
    }

    /**
     * @param int $objectId
     * @param int $projectId
     * @param int $userId
     * @param int $activity_type_id
     * @param array|mixed $data array with ([type_id], [pid], description, title, image_small)
     */
    public static function logActivity($objectId, $projectId, $userId, $activity_type_id, $data)
    {
        // cutting description text if necessary
        $object_text = (strlen($data['description']) < 150) ? $data['description'] : mb_substr($data['description'], 0,
                145, 'UTF-8') . ' ... ';

        $newEntry = array(
            'member_id' => $userId,
            'project_id' => $projectId,
            'object_id' => $objectId,
            'object_ref' => self::$referenceType[$activity_type_id],
            'object_title' => $data['title'],
            'object_text' => $object_text,
            'object_img' => $data['image_small'] ? $data['image_small'] : null,
            'activity_type' => $activity_type_id
        );

        $sql = "
            INSERT INTO activity_log
            SET member_id = :member_id, 
                project_id = :project_id, 
                object_id = :object_id, 
                object_ref = :object_ref,
                object_title = :object_title,
                object_text = :object_text,
                object_img = :object_img,
                activity_type_id = :activity_type
                ;
        ";

        try {
            Zend_Db_Table::getDefaultAdapter()->query($sql, $newEntry);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->err(__METHOD__ . ' - ERROR write activity log - ' . print_r($e, true));
        }
    }

}
