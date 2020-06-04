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
class Default_Model_Project extends Default_Model_DbTable_Project
{

    const FILTER_NAME_PROJECT_ID_NOT_IN = 'project_id_not_in';
    const FILTER_NAME_RANKING = 'ranking';
    const FILTER_NAME_CATEGORY = 'category';
    const FILTER_NAME_TAG = 'tag';
    const FILTER_NAME_ORIGINAL = 'original';
    const FILTER_NAME_FAVORITE = 'favorite';
    const FILTER_NAME_MEMBER = 'member';
    const FILTER_NAME_ORDER = 'order';
    const FILTER_NAME_LOCATION = 'location';

    const ITEM_TYPE_DUMMY = 0;
    const ITEM_TYPE_PRODUCT = 1;
    const ITEM_TYPE_UPDATE = 2;

    const TAG_LICENCE_GID = 7;
    const TAG_TYPE_ID = 1;

    const TAG_ISORIGINAL = 'original-product';

    /**
     * @param int $status
     * @param int $id
     *
     * @throws Exception
     */
    public function setStatus($status, $id)
    {
        if (false === in_array($status, $this->_allowedStatusTypes)) {
            throw new Exception('Wrong value for project status.');
        }
        $updateValues = array(
            'status'     => $status,
            'changed_at' => new Zend_Db_Expr('Now()')
        );

        if (self::PROJECT_DELETED == $status) {
            $updateValues['deleted_at'] = new Zend_Db_Expr('NOW()');
        }

        $this->update($updateValues, $this->_db->quoteInto('project_id=?', $id, 'INTEGER'));
    }

    /**
     * @param int $member_id
     * @param int $id
     */
    public function setClaimedByMember($member_id, $id)
    {
        $updateValues = array(
            'claimed_by_member' => $member_id,
            'changed_at'        => new Zend_Db_Expr('Now()')
        );

        $this->update($updateValues, $this->_db->quoteInto('project_id=?', $id, 'INTEGER'));
    }

    /**
     * @param int $id
     */
    public function resetClaimedByMember($id)
    {
        $updateValues = array(
            'claimed_by_member' => new Zend_Db_Expr('NULL'),
            'changed_at'        => new Zend_Db_Expr('Now()')
        );

        $this->update($updateValues, $this->_db->quoteInto('project_id=?', $id, 'INTEGER'));
    }

    /**
     * @param int $id
     */
    public function transferClaimToMember($id)
    {
        $project = $this->fetchProductInfo($id);

        //Update ppload
        $pploadFiles = new Default_Model_DbTable_PploadFiles();
        $updateValues = array(
            'owner_id'         => $project->claimed_by_member
        );
        $pploadFiles->update($updateValues, "collection_id = ".$project->ppload_collection_id);

        $pploadCollection = new Default_Model_DbTable_PploadCollections();
        $updateValues = array(
            'owner_id'         => $project->claimed_by_member
        );
        $pploadCollection->update($updateValues, "id = ".$project->ppload_collection_id);

        //And prohect
        $updateValues = array(
            'member_id'         => new Zend_Db_Expr('claimed_by_member'),
            'claimable'         => new Zend_Db_Expr('NULL'),
            'claimed_by_member' => new Zend_Db_Expr('NULL')
        );

        $this->update($updateValues, $this->_db->quoteInto('project_id=? and claimable = 1', $id, 'INTEGER'));

    }

    /**
     * @param int $project_id
     * @param     $member_id
     *
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     */
    public function setInActive($project_id, $member_id)
    {
        $project_id = (int)$project_id;
        $updateValues = array(
            'status'     => self::PROJECT_INACTIVE,
            'deleted_at' => new Zend_Db_Expr('Now()')
        );

        $this->update($updateValues, 'status > 40 AND project_id=' . $project_id);

        $this->setInActiveForUpdates($project_id);
        $this->setDeletedForComments($member_id,$project_id);
    }

    /**
     * @param int $id
     */
    protected function setInActiveForUpdates($id)
    {
        $id = (int)$id;
        $updateValues = array(
            'status'     => self::PROJECT_INACTIVE,
            'changed_at' => new Zend_Db_Expr('Now()')
        );

        $this->update($updateValues, 'status > 40 AND pid=' . $id);
    }

    /**
     * @param int $member_id
     * @param int $id
     *
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     */
    private function setDeletedForComments($member_id, $id)
    {
        $modelComments = new Default_Model_ProjectComments();
        $modelComments->setAllCommentsForProjectDeleted($member_id, $id);
    }

    /**
     * @param int $id
     *
     * @return mixed
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchActiveBySourcePk($id)
    {
        $q = $this->select()->where('status = ?', self::PROJECT_ACTIVE)->where('source_pk = ?', (int)$id)
                  ->where('source_type = "project"')
        ;

        return $q->query()->fetch();
    }

    /**
     * @param int  $member_id
     * @param bool $onlyActiveProjects
     *
     * @return mixed
     */
    public function countAllProjectsForMember($member_id, $onlyActiveProjects = false)
    {
        $q = $this->select()->from($this, array('countAll' => new Zend_Db_Expr('count(*)')))->setIntegrityCheck(false)
                  ->where('project.status >= ?', ($onlyActiveProjects ? self::PROJECT_ACTIVE : self::PROJECT_INACTIVE))
                  ->where('project.member_id = ?', $member_id, 'INTEGER')->where('project.type_id = ?', self::PROJECT_TYPE_STANDARD)
        ;
        $resultSet = $q->query()->fetchAll();

        return $resultSet[0]['countAll'];
    }

    /**
     * @param int  $member_id
     * @param bool $onlyActiveProjects
     * @param      $catids
     *
     * @return mixed
     * @throws Zend_Cache_Exception
     * @throws Zend_Db_Statement_Exception
     */
    public function countAllProjectsForMemberCatFilter($member_id, $onlyActiveProjects = false, $catids = null)
    {
        $q = $this->select()->from($this, array('countAll' => new Zend_Db_Expr('count(*)')))->setIntegrityCheck(false)
                  ->where('project.status >= ?', ($onlyActiveProjects ? self::PROJECT_ACTIVE : self::PROJECT_INACTIVE))
                  ->where('project.member_id = ?', $member_id, 'INTEGER')->where('project.type_id = ?', self::PROJECT_TYPE_STANDARD)
        ;
        if (isset($catids)) {
            $q->where('project_category_id in (' . $this->_getCatIds($catids) . ')');
        }
        $resultSet = $q->query()->fetchAll();

        return $resultSet[0]['countAll'];
    }

    /**
     * @param $catids
     *
     * @return string
     * @throws Zend_Cache_Exception
     * @throws Zend_Db_Statement_Exception
     */
    protected function _getCatIds($catids)
    {
        $sqlwhereCat = "";
        $sqlwhereSubCat = "";

        $idCategory = explode(',', $catids);
        if (false === is_array($idCategory)) {
            $idCategory = array($idCategory);
        }

        $sqlwhereCat .= implode(',', $idCategory);

        $modelCategory = new Default_Model_DbTable_ProjectCategory();
        $subCategories = $modelCategory->fetchChildElements($idCategory);

        if (count($subCategories) > 0) {
            foreach ($subCategories as $element) {
                $sqlwhereSubCat .= "{$element['project_category_id']},";
            }
        }

        return $sqlwhereSubCat . $sqlwhereCat;
    }

    /**
     * By default it will show all projects for a member included the unpublished elements.
     *
     * @param int      $member_id
     * @param int|null $limit
     * @param int|null $offset
     * @param bool     $onlyActiveProjects
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchAllProjectsForMember($member_id, $limit = null, $offset = null, $onlyActiveProjects = false)
    {
        $q = $this->select()->from($this, array(
            '*',
            'project_validated'  => 'project.validated',
            'project_uuid'       => 'project.uuid',
            'project_status'     => 'project.status',
            'project_created_at' => 'project.created_at',
            'project_changed_at' => 'project.changed_at',
            'member_type'        => 'member.type',
            'project_member_id'  => 'member_id',
            'laplace_score'      => new Zend_Db_Expr('laplace_score(count_likes,count_dislikes)'),
            'catTitle'           => new Zend_Db_Expr('(SELECT title FROM project_category WHERE project_category_id = project.project_category_id)')
        ))->setIntegrityCheck(false)->join('member', 'project.member_id = member.member_id', array('username'))
                  ->where('project.status >= ?', ($onlyActiveProjects ? self::PROJECT_ACTIVE : self::PROJECT_INACTIVE))
                  ->where('project.member_id = ?', $member_id, 'INTEGER')->where('project.type_id = ?', self::PROJECT_TYPE_STANDARD)
                  ->order('project_changed_at DESC')
        ;
        if (isset($limit)) {
            $q->limit($limit, $offset);
        }

        return $this->generateRowSet($q->query()->fetchAll());
    }


    /**
     * @param array $data
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    protected function generateRowSet($data)
    {
        $classRowSet = $this->getRowsetClass();

        return new $classRowSet(array(
            'table'    => $this,
            'rowClass' => $this->getRowClass(),
            'stored'   => true,
            'data'     => $data
        ));
    }

    /**
     * By default it will show all projects for a member included the unpublished elements.
     *
     * @param int      $member_id
     * @param int|null $limit
     * @param int|null $offset
     * @param bool     $onlyActiveProjects
     *
     * @param null     $catids
     *
     * @return Zend_Db_Table_Rowset_Abstract
     * @throws Zend_Cache_Exception
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchAllProjectsForMemberCatFilter(
        $member_id,
        $limit = null,
        $offset = null,
        $onlyActiveProjects = false,
        $catids = null
    ) {
        $q = $this->select()->from($this, array(
            '*',
            'project_validated'  => 'project.validated',
            'project_uuid'       => 'project.uuid',
            'project_status'     => 'project.status',
            'project_created_at' => 'project.created_at',
            'project_changed_at' => 'project.changed_at',
            'member_type'        => 'member.type',
            'project_member_id'  => 'member_id',
            'laplace_score'      => new Zend_Db_Expr('laplace_score(count_likes,count_dislikes)'),
            'catTitle'           => new Zend_Db_Expr('(SELECT title FROM project_category WHERE project_category_id = project.project_category_id)')
        ))->setIntegrityCheck(false)->join('member', 'project.member_id = member.member_id', array('username'))
                  ->where('project.status >= ?', ($onlyActiveProjects ? self::PROJECT_ACTIVE : self::PROJECT_INACTIVE))
                  ->where('project.member_id = ?', $member_id, 'INTEGER')->where('project.type_id = ?', self::PROJECT_TYPE_STANDARD)
                  ->order('project_changed_at DESC')
        ;

        if (isset($catids)) {
            $q->where('project_category_id in (' . $this->_getCatIds($catids) . ')');
        }

        if (isset($limit)) {
            $q->limit($limit, $offset);
        }

        return $this->generateRowSet($q->query()->fetchAll());
    }

    /**
     * @param $collection_id
     *
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function fetchProductForCollectionId($collection_id)
    {
        $sql = '
                SELECT
                  `p`.*
                FROM `project` AS `p`
                WHERE
                  `p`.`ppload_collection_id` = :collectionId
                  AND `p`.`status` >= :projectStatus AND `p`.`type_id` = :typeId
        ';
        $result = $this->_db->fetchRow($sql, array(
            'collectionId'  => $collection_id,
            'projectStatus' => self::PROJECT_INACTIVE,
            'typeId'        => self::PROJECT_TYPE_STANDARD
        ));

        if ($result) {
            return $this->generateRowClass($result);
        } else {
            return null;
        }
    }

    /**
     * @param int $project_id
     *
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function fetchProductInfo($project_id)
    {
        $sql = '
                SELECT
                  `p`.*,
                  `p`.`validated` AS `project_validated`,
                  `p`.`uuid` AS `project_uuid`,
                  `p`.`status` AS `project_status`,
                  `p`.`created_at` AS `project_created_at`,
                  `p`.`major_updated_at` AS `project_major_updated_at`,
                  `p`.`changed_at` AS `project_changed_at`,
                  `p`.`member_id` AS `project_member_id`,
                  `p`.`source_pk` AS `project_source_pk`,
                  `p`.`version` AS `project_version`,
                  `pc`.`title` AS `cat_title`,
                  `m`.`username`,
                  `m`.`avatar`,
                  `m`.`profile_image_url`,
                  `m`.`roleId`,
                  `m`.`mail`,
                  `m`.`paypal_mail`,
                  `m`.`dwolla_id`,
                  IFNULL(pr.score_with_pling, 500) AS laplace_score,
                 `view_reported_projects`.`amount_reports` AS `amount_reports`,
                (SELECT `tag`.`tag_fullname` FROM `tag_object`, `tag` WHERE `tag_object`.`tag_id`=`tag`.`tag_id` AND `tag_object_id` = `p`.`project_id` AND `tag_object`.`is_deleted`=0 AND `tag_group_id` = :tag_licence_gid AND `tag_type_id` = :tag_type_id  ORDER BY `tag_object`.`tag_created` DESC LIMIT 1)
                                AS `project_license_title`
                FROM `project` AS `p`
                  JOIN `member` AS `m` ON `p`.`member_id` = `m`.`member_id` AND `m`.`is_active` = 1 AND `m`.`is_deleted` = 0
                  JOIN `project_category` AS `pc` ON `p`.`project_category_id` = `pc`.`project_category_id`
                  LEFT join  stat_rating_project AS pr  ON p.project_id = pr.project_id
                  LEFT JOIN `view_reported_projects` ON ((`view_reported_projects`.`project_id` = `p`.`project_id`))
                WHERE
                  `p`.`project_id` = :projectId
                  AND `p`.`status` >= :projectStatus AND (`p`.`type_id` = :typeIdStd OR `p`.`type_id` = :typeIdColl)
        ';
        $result = $this->_db->fetchRow($sql, array(
            'projectId'       => $project_id,
            'projectStatus'   => self::PROJECT_INACTIVE,
            'typeIdStd'       => self::PROJECT_TYPE_STANDARD,
            'typeIdColl'      => self::PROJECT_TYPE_COLLECTION,
            'tag_licence_gid' => self::TAG_LICENCE_GID,
            'tag_type_id'     => self::TAG_TYPE_ID

        ));

        if ($result) {
            return $this->generateRowClass($result);
        } else {
            return null;
        }
    }

    /**
     * @param $project_id
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchProjectUpdates($project_id)
    {
        $projectSel = $this->select()->setIntegrityCheck(false)->from($this->_name)
                           ->join('member', 'project.member_id = member.member_id', array('*'))
                           ->where('project.pid=?', $project_id, 'INTEGER')->where('project.status>?', self::PROJECT_INACTIVE)
                           ->where('project.type_id=?', self::PROJECT_TYPE_UPDATE)->order('RAND()')
        ;

        return $this->fetchAll($projectSel);
    }

    /**
     * @param $project_id
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchAllProjectUpdates($project_id)
    {
        $projectSel = $this->select()->setIntegrityCheck(false)->from($this->_name)->where('project.pid=?', $project_id, 'INTEGER')
                           ->where('project.status>?', self::PROJECT_INACTIVE)->where('project.type_id=?', self::PROJECT_TYPE_UPDATE)
        ;

        return $this->fetchAll($projectSel);
    }

    /**
     * @param     $project
     * @param int $count
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchSimilarProjects($project, $count = 10)
    {
        $count = (int)$count;
        $sql = "
                SELECT *
                FROM `stat_projects` AS `p`
                WHERE `p`.`project_category_id` = :cat_id AND `project_id` <> :project_id
                ORDER BY `p`.`changed_at` DESC
                LIMIT {$count}
        ";

        $result = $this->_db->fetchAll($sql, array(
                'cat_id'     => $project->project_category_id,
                'project_id' => $project->project_id
            ));

        return $this->generateRowSet($result);
    }

    /**
     * @param Zend_Db_Table_Row $project
     * @param int               $count
     *
     * @return Zend_Db_Table_Rowset_Abstract
     * @throws Zend_Exception
     */
    public function fetchMoreProjects($project, $count = 6)
    {
        $q = $this->select()->from(array('project' => 'stat_projects'), array(
            'project_id',
            'image_small',
            'title',
            'catTitle' => 'cat_title',
            'changed_at'
        ))->setIntegrityCheck(false)
          ->where('project.status = ?', self::PROJECT_ACTIVE)
          ->where('project.member_id = ?', $project->member_id, 'INTEGER')
          ->where('project.project_id != ?', $project->project_id, 'INTEGER')
          ->where('project.type_id = ?', self::PROJECT_TYPE_STANDARD)
          ->where('project.amount_reports is null')
          ->where('project.project_category_id = ?', $project->project_category_id, 'INTEGER')
          ->limit($count)
          ->order('project.project_created_at DESC')
        ;

        $tagFilter  = Zend_Registry::isRegistered('config_store_tags') ? Zend_Registry::get('config_store_tags') : null;

        if ($tagFilter) {
            $q = $this->generateTagFilter($q, array(self::FILTER_NAME_TAG => $tagFilter));
        }

        $result = $this->fetchAll($q);

        return $result;
    }

    /**
     * @param Zend_Db_Select $statement
     * @param array          $filterArrayValue
     *
     * @return Zend_Db_Select
     */
    protected function generateTagFilter(Zend_Db_Select $statement, $filterArrayValue)
    {
        if (false == isset($filterArrayValue[self::FILTER_NAME_TAG])) {
            return $statement;
        }

        $filter = $filterArrayValue[self::FILTER_NAME_TAG];

        if (is_array($filter)) {

            $tagList = $filter;
            //build where statement für projects
            $selectAnd = $this->select()->from(array('project' => 'stat_projects'));

            foreach($tagList as $item) {
                #and
                $selectAnd->where('find_in_set(?, tag_ids)', $item);
            }
            $statement->where(implode(' ', $selectAnd->getPart('where')));

            /*
            $statement->join(array(
                'tags' => new Zend_Db_Expr('(SELECT DISTINCT project_id FROM stat_project_tagids WHERE tag_id in ('
                    . implode(',', $filter) . '))')
            ), 'project.project_id = tags.project_id', array());
             *
             */

        } else {
            $statement->where('find_in_set(?, tag_ids)', $filter);
        }

        return $statement;
    }

    /**
     * @param     $project
     * @param int $count
     *
     * @return Zend_Db_Table_Rowset_Abstract
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     * @todo improve processing speed
     */
    public function fetchMoreProjectsOfOtherUsr($project, $count = 8)
    {
        $sql = "
                SELECT count(1) AS `count`
                FROM `stat_projects`
                WHERE `status` = :current_status
                  AND `member_id` <> :current_member_id
                  AND `project_category_id` = :category_id
                  AND `type_id` = :project_type
        ";

        $result = $this->_db->query($sql, array(
            'current_status'    => self::PROJECT_ACTIVE,
            'current_member_id' => $project->member_id,
            'category_id'       => $project->project_category_id,
            'project_type'      => self::PROJECT_TYPE_STANDARD
        ))->fetch()
        ;

        if ($result['count'] > $count) {
            $offset = rand(0, $result['count'] - $count);
        } else {
            $offset = 0;
        }

        $q = $this->select()->from(array('project' => 'stat_projects'), array(
            'project_id',
            'image_small',
            'title',
            'catTitle' => 'cat_title',
            'changed_at'
        ))->setIntegrityCheck(false)->where('status = ?', self::PROJECT_ACTIVE)
                  ->where('member_id != ?', $project->member_id, 'INTEGER')->where('type_id = ?', 1)
                  ->where('amount_reports is null')
                  ->where('project_category_id = ?', $project->project_category_id, 'INTEGER')->limit($count, $offset)
                  ->order('project_created_at DESC')
        ;

        $tagFilter  = Zend_Registry::isRegistered('config_store_tags') ? Zend_Registry::get('config_store_tags') : null;

        if ($tagFilter) {
            $q = $this->generateTagFilter($q, array(self::FILTER_NAME_TAG => $tagFilter));
        }

        $result = $this->fetchAll($q);

        return $result;
    }

    /**
     * @param int $project_id
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchProjectSupporter($project_id)
    {
        $plingTable = new Default_Model_DbTable_Plings();

        return $plingTable->getSupporterForProjectId($project_id);
    }

    /**
     * @param int $project_id
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchProjectSupporterWithPlings($project_id)
    {
        $plingTable = new Default_Model_DbTable_Plings();

        return $plingTable->getSupporterWithPlingsForProjectId($project_id);
    }

    /**
     * @param $projectId
     * @param $sources
     */
    public function updateGalleryPictures($projectId, $sources)
    {
        $galleryPictureTable = new Default_Model_DbTable_ProjectGalleryPicture();
        $galleryPictureTable->clean($projectId);
        $galleryPictureTable->insertAll($projectId, $sources);
    }

    /**
     * @param $projectId
     *
     * @return array
     */
    public function getGalleryPictureSources($projectId)
    {
        $galleryPictureTable = new Default_Model_DbTable_ProjectGalleryPicture();
        $stmt = $galleryPictureTable->select()->where('project_id = ?', $projectId)->order(array('sequence'));

        $pics = array();
        foreach ($galleryPictureTable->fetchAll($stmt) as $pictureRow) {
            $pics[] = $pictureRow['picture_src'];
        }

        return $pics;
    }

    /**
     * @param int $project_id
     *
     * @return array
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchProjectViews($project_id)
    {
        $sql = "
                SELECT
                    `project_id`,
                    `count_views`,
                    `count_visitor`,
                    `last_view`
                FROM
                    `stat_page_views_mv`
                WHERE `project_id` = ?
                ";
        $database = Zend_Db_Table::getDefaultAdapter();
        $sql = $database->quoteInto($sql, $project_id, 'INTEGER', 1);
        $resultSet = $database->query($sql)->fetchAll();

        if (count($resultSet) > 0) {
            $result = $resultSet[0]['count_views'];
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * @param int $member_id
     *
     * @return int
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchOverallPageViewsByMember($member_id)
    {
        $sql = "
                SELECT sum(`stat`.`amount`) AS `page_views`
                FROM `project`
                JOIN (SELECT `project_id`, count(`project_id`) AS `amount` FROM `stat_page_views` GROUP BY `project_id`) AS `stat` ON `stat`.`project_id` = `project`.`project_id`
                WHERE `project`.`member_id` = :member_id AND `project`.`status` = :project_status
                GROUP BY `member_id`
              ";

        $result = $this->_db->query($sql, array('member_id' => $member_id, 'project_status' => self::PROJECT_ACTIVE));
        if ($result->rowCount() > 0) {
            $row = $result->fetch();

            return $row['page_views'];
        } else {
            return 0;
        }
    }

    /**
     * @return array
     * @throws Zend_Db_Statement_Exception
     */
    public function getStatsForNewProjects()
    {
        $sql = "
                SELECT
                    DATE_FORMAT(`time`, '%M %D') AS `projectdate`,
                    count(1) AS `daycount`
                FROM
                    `activity_log`
                WHERE
                    `activity_type_id` = 0
                GROUP BY DATE_FORMAT(`time`, '%Y%M%D')
                ORDER BY `time` DESC
                LIMIT 14
                ;";
        $database = Zend_Db_Table::getDefaultAdapter();
        $resultSet = $database->query($sql)->fetchAll();

        return $resultSet;
    }

    /**
     * @param int     $idCategory
     * @param int|null $limit
     *
     * @return Zend_Db_Table_Rowset_Abstract
     * @throws Zend_Cache_Exception
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchProductsByCategory($idCategory, $limit = null)
    {
        $select =
            $this->select()->setIntegrityCheck(false)->from($this->_name)->where('project.project_category_id in (?)', $idCategory)
                 ->where('project.status = ?', self::PROJECT_ACTIVE)->where('project.type_id = ?', self::PROJECT_TYPE_STANDARD)
                 ->joinLeft(array(
                     'pling_amount' => new Zend_Db_Expr('(SELECT
                project_id as plinged_project_id, SUM(amount) AS sumAmount, count(1) as countPlings
            FROM
                plings
            where status_id >= 2
            group by project_id
            order by sumAmount DESC)')
                 ), 'pling_amount.plinged_project_id = project.project_id')
                 ->joinLeft('project_category', 'project_category.project_category_id = project.project_category_id',
                     array('cat_title' => 'title'))->order('pling_amount.sumAmount DESC')
        ;
        if (false === is_null($limit)) {
            $select->limit($limit);
        }

        $modelCategory = new Default_Model_DbTable_ProjectCategory();
        $subCategories = $modelCategory->fetchChildElements($idCategory);

        if (count($subCategories) > 0) {
            $sqlwhere = '';
            foreach ($subCategories as $element) {
                $sqlwhere .= "{$element['project_category_id']},";
            }
            $sqlwhere = substr($sqlwhere, 0, -1);
            if (!empty($sqlwhere)) {
                $sqlwhere = explode(',', $sqlwhere);
            }

            $select->orWhere('project.project_category_id in (?)', $sqlwhere);
        }

        return $this->fetchAll($select);
    }

    /**
     * @param int|array $idCategory id of a category or an array of id's
     * @param bool      $withSubCat if was set true it will also count products in sub categories
     * @param null      $store_id
     *
     * @return int count of products in given category
     * @throws Zend_Exception
     * @deprecated
     */
    public function countProductsInCategory($idCategory = null, $withSubCat = true, $store_id = null)
    {
        if (empty($idCategory)) {
            throw new Zend_Exception('idCategory param was not set');
        }

        if (false == is_array($idCategory)) {
            $idCategory = array($idCategory);
        }

        if (isset($store_id)) {
            $configurations = Zend_Registry::get('application_store_config_id_list');
            $store_config = isset($configurations[$store_id]) ? $configurations[$store_id] : null;
        } else {
            $store_config = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;
        }
        $tagFilter  = Zend_Registry::isRegistered('config_store_tags') ? Zend_Registry::get('config_store_tags') : null;

        $cacheName = __FUNCTION__ . '_' . md5(serialize($idCategory) . $withSubCat . serialize($tagFilter));
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');

        if (false !== ($resultSet = $cache->load($cacheName))) {
            return (int)$resultSet[0]['count_active_projects'];
        }

        $select = $this->select()->setIntegrityCheck(false)->from('stat_projects', array('count_active_projects' => 'COUNT(1)'))
                       ->where('status = ? ', self::PROJECT_ACTIVE)->where('type_id = ?', self::PROJECT_TYPE_STANDARD)
        ;

        $select = $this->generateTagFilter($select, array(self::FILTER_NAME_TAG => $tagFilter));

        if ($withSubCat) {
            $modelCategory = new Default_Model_DbTable_ProjectCategory();
            $subCategories = $modelCategory->fetchChildIds($idCategory);
            $inCategories = implode(',', array_unique(array_merge($idCategory, $subCategories)));
        } else {
            $inCategories = implode(',', $idCategory);
        }

        $select->where('project_category_id in (' . $inCategories . ')');
        $resultSet = $this->fetchAll($select)->toArray();

        $cache->save($resultSet, $cacheName, array(), 60);

        return (int)$resultSet[0]['count_active_projects'];
    }

    /**
     * @param int|array $idCategory
     *
     * @return int
     * @throws Zend_Exception
     */
    public function countActiveMembersForCategory($idCategory)
    {

        $cacheName = __FUNCTION__ . md5(serialize($idCategory));
        $cache = Zend_Registry::get('cache');

        $result = $cache->load($cacheName);

        if ($result) {
            return (int)$result['count_active_members'];
        }

        $sqlwhereCat = "";
        $sqlwhereSubCat = "";

        if (false === is_array($idCategory)) {
            $idCategory = array($idCategory);
        }
        $sqlwhereCat .= implode(',', $idCategory);

        $modelCategory = new Default_Model_DbTable_ProjectCategory();
        $subCategories = $modelCategory->fetchChildElements($idCategory);

        if (count($subCategories) > 0) {
            foreach ($subCategories as $element) {
                $sqlwhereSubCat .= "{$element['project_category_id']},";
            }
        }

        $selectWhere = 'AND p.project_category_id in (' . $sqlwhereSubCat . $sqlwhereCat . ')';

        $sql = "SELECT count(1) AS `count_active_members` FROM (
                    SELECT count(1) AS `count_active_projects` FROM `project` `p`
                    WHERE `p`.`status` = 100
                    AND `p`.`type_id` = 1
                    {$selectWhere} GROUP BY p.member_id
                ) AS `A`;";

        $result = $this->_db->fetchRow($sql);
        $cache->save($result, $cacheName);

        return (int)$result['count_active_members'];
    }

    /**
     * @param int $project_id
     *
     * @return bool
     */
    public function isProjectFeatured($project_id)
    {
        $sql_object =
            "SELECT `project_id` FROM `project` WHERE `project_id`= :project_id AND  `status` = 100 AND `type_id` = 1 AND `featured` = 1";
        $r = $this->getAdapter()->fetchRow($sql_object, array('project_id' => $project_id));
        if ($r) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * @param int $project_id
     *
     * @return bool
     */
    public function isProjectClone($project_id)
    {
        $sql_object =
            "SELECT c.project_clone_id FROM project_clone c
                WHERE c.is_valid = 1
                AND c.is_deleted = 0
                AND c.project_id_parent IS NOT NULL
                AND c.project_id = :project_id";
        $r = $this->getAdapter()->fetchRow($sql_object, array('project_id' => $project_id));
        if ($r) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @param bool $in_current_store
     *
     * @return int
     * @throws Zend_Exception
     */
    public function fetchTotalProjectsCount($in_current_store = false)
    {
        $sql = "SELECT count(1) AS `total_project_count` FROM `stat_projects`";
        if ($in_current_store) {
            $store_tags = Zend_Registry::isRegistered('config_store_tags') ? Zend_Registry::get('config_store_tags') : null;
            /*
            if ($store_tags) {
                $sql .= ' JOIN (SELECT DISTINCT project_id FROM stat_project_tagids WHERE tag_id in (' . implode(',', $store_tags)
                    . ')) AS tags ON stat_projects.project_id = tags.project_id';
            }
             *
             */

            $info = new Default_Model_Info();
            $activeCategories = $info->getActiveCategoriesForCurrentHost();
            $sql .= ' WHERE project_category_id IN (' . implode(',', $activeCategories) . ')';

            //Store Tag Filter
            if ($store_tags) {
                $tagList = $store_tags;
                //build where statement für projects
                $sql .= " AND (";

                if(!is_array($tagList)) {
                    $tagList = array($tagList);
                }

                foreach($tagList as $item) {
                    #and
                    $sql .= ' find_in_set('.$item.', tag_ids) AND ';
                }
                $sql .= ' 1=1)';;
            }

        }
        $result = $this->_db->fetchRow($sql);

        return (int)$result['total_project_count'];
    }

    /**
     * @param $member_id
     *
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     */
    public function setAllProjectsForMemberDeleted($member_id)
    {
        $sql =
            "SELECT `project_id` FROM `project` WHERE `member_id` = :memberId AND `type_id` = :typeId AND `status` > :project_status";
        $projectForDelete = $this->_db->fetchAll($sql, array(
            'memberId'       => $member_id,
            'typeId'         => self::PROJECT_TYPE_STANDARD,
            'project_status' => self::PROJECT_DELETED
        ));
        foreach ($projectForDelete as $item) {
            $this->setDeleted($member_id, $item['project_id']);
        }

        // set personal page deleted
        $sql = "SELECT project_id FROM project WHERE member_id = :memberId AND type_id = :typeId";
        $projectForDelete = $this->_db->fetchAll($sql, array(
            'memberId' => $member_id,
            'typeId'   => self::PROJECT_TYPE_PERSONAL
        ));
        foreach ($projectForDelete as $item) {
            $this->setDeleted($member_id, $item['project_id']);
        }
        /*
        $sql = "UPDATE project SET `status` = :statusCode, deleted_at = NOW() WHERE member_id = :memberId AND type_id = :typeId";
        $this->_db->query($sql, array(
            'statusCode' => self::PROJECT_DELETED,
            'memberId'   => $member_id,
            'typeId'     => self::PROJECT_TYPE_PERSONAL
        ))->execute();
        */
    }

    /**
     * @param int $member_id
     * @param int $id
     *
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     */
    public function setDeleted($member_id, $id)
    {
        $id = (int)$id;
        $updateValues = array(
            'status'     => self::PROJECT_DELETED,
            'deleted_at' => new Zend_Db_Expr('Now()')
        );

        $this->update($updateValues, 'status > 30 AND project_id=' . $id);

        $memberLog = new Default_Model_MemberDeactivationLog();
        $memberLog->logProjectAsDeleted($member_id, $id);
        
        // this will delete the product and request the ppload for deleting associated files
        $product = $this->_model->find($id)->current();
        $command = new Backend_Commands_DeleteProductExtended($product);
        $command->doCommand();

        $this->setDeletedForUpdates($member_id, $id);
        $this->setDeletedForComments($member_id, $id);
        $this->setDeletedInMaterializedView($id);
    }

    /**
     * @param     $member_id
     * @param int $id
     */
    protected function setDeletedForUpdates($member_id, $id)
    {
        $id = (int)$id;
        $updateValues = array(
            'status'     => self::PROJECT_DELETED,
            'deleted_at' => new Zend_Db_Expr('Now()')
        );

        $this->update($updateValues, 'status > 30 AND pid=' . $id);
    }

    /**
     * @param $id
     *
     * @throws Zend_Db_Statement_Exception
     */
    private function setDeletedInMaterializedView($id)
    {
        $sql = "UPDATE `stat_projects` SET `status` = :new_status WHERE `project_id` = :project_id";

        $result = $this->_db->query($sql, array('new_status' => self::PROJECT_DELETED, 'project_id' => $id))->execute();
    }

    /**
     * @param int $member_id
     *
     * @throws Zend_Exception
     */
    public function setAllProjectsForMemberActivated($member_id)
    {
        $sql = "SELECT `p`.`project_id` FROM `project` `p`
                JOIN `member_deactivation_log` `l` ON `l`.`object_type_id` = 3 AND `l`.`object_id` = `p`.`project_id` AND `l`.`deactivation_id` = `p`.`member_id`
                WHERE `p`.`member_id` = :memberId";
        $projectForDelete = $this->_db->fetchAll($sql, array(
            'memberId' => $member_id
        ));
        foreach ($projectForDelete as $item) {
            $this->setActive($member_id, $item['project_id']);
        }
    }

    /**
     * @param int $member_id
     * @param int $id
     *
     * @throws Zend_Exception
     */
    public function setActive($member_id, $id)
    {
        $updateValues = array(
            'status'     => self::PROJECT_ACTIVE,
            'deleted_at' => null
        );

        $this->update($updateValues, $this->_db->quoteInto('project_id=?', $id, 'INTEGER'));

        $memberLog = new Default_Model_MemberDeactivationLog();
        $memberLog->removeLogProjectAsDeleted($member_id, $id);

        $this->setActiveForUpdates($member_id, $id);
        $this->setActiveForComments($member_id, $id);
    }

    /**
     * @param int $id
     */
    protected function setActiveForUpdates($member_id, $id)
    {
        $updateValues = array(
            'status'     => self::PROJECT_ACTIVE,
            'deleted_at' => null
        );

        $this->update($updateValues, $this->_db->quoteInto('pid=?', $id, 'INTEGER'));
    }

    /**
     * @param int $member_id
     * @param int $project_id
     */
    private function setActiveForComments($member_id, $project_id)
    {
        $modelComments = new Default_Model_ProjectComments();
        $modelComments->setAllCommentsForProjectActivated($member_id, $project_id);
    }

    /**
     * @param array    $inputFilterParams
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     * @throws Zend_Cache_Exception
     * @throws Zend_Db_Select_Exception
     * @throws Zend_Exception
     */
    public function fetchProjectsByFilter($inputFilterParams, $limit = null, $offset = null)
    {
        $cacheName = __FUNCTION__ . '_' . md5(serialize($inputFilterParams) . (string)$limit . (string)$offset);
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');

        if (false === ($returnValue = $cache->load($cacheName))) {
            $statement = $this->generateStatement($inputFilterParams, $limit, $offset);

            if (APPLICATION_ENV == 'development') {
                Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . $statement->__toString());
            }

            /** @var Zend_Db_Table_Rowset $fetchedElements */
            $fetchedElements = $this->fetchAll($statement);
            $statement->reset('limitcount')->reset('limitoffset');
            $statement->reset('columns')->columns(array('count' => new Zend_Db_Expr('count(*)')));
            $countElements = $this->fetchRow($statement);
            $returnValue = array('elements' => $fetchedElements, 'total_count' => $countElements->count);
            $cache->save($returnValue, $cacheName, array(), 120);
        }

        return $returnValue;
    }

    /**
     * @param array    $inputFilterParams
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return Zend_Db_Select
     * @throws Zend_Cache_Exception
     * @throws Zend_Db_Statement_Exception
     */
    protected function generateStatement($inputFilterParams, $limit = null, $offset = null)
    {
        $statement = $this->generateBaseStatement();
        $statement = $this->generateCategoryFilter($statement, $inputFilterParams);
        $statement = $this->generateOrderFilter($statement, $inputFilterParams);
        $statement = $this->generateTagFilter($statement, $inputFilterParams);
        // $statement = $this->generateOriginalFilter($statement, $inputFilterParams);
        $statement = $this->generateFavoriteFilter($statement, $inputFilterParams);
        $statement = $this->generateReportedSpamFilter($statement);

        $statement->limit($limit, $offset);

        return $statement;
    }

    /**
     * @return Zend_Db_Select
     */
    protected function generateBaseStatement()
    {
        $statement = $this->select()->setIntegrityCheck(false);
        //$statement->from(array('project' => $this->_name), array(
        $statement->from(array('project' => 'stat_projects'), array(
            '*'
        ));
        $statement->where('project.status = ?', self::PROJECT_ACTIVE)->where('project.type_id IN (?)', array(self::PROJECT_TYPE_STANDARD, self::PROJECT_TYPE_COLLECTION));

        return $statement;
    }

    /**
     * @param Zend_Db_Select $statement
     * @param array          $filterArrayValue
     *
     * @return Zend_Db_Select
     * @throws Zend_Cache_Exception
     * @throws Zend_Db_Statement_Exception
     */
    protected function generateCategoryFilter(Zend_Db_Select $statement, $filterArrayValue)
    {
        if (false == isset($filterArrayValue[self::FILTER_NAME_CATEGORY])) {
            return $statement;
        }

        $filter = $filterArrayValue[self::FILTER_NAME_CATEGORY];

        if (false === is_array($filter)) {
            $filter = array($filter);
        }

        // fetch child elements for each category
        $modelProjectCategories = new Default_Model_DbTable_ProjectCategory();
        $childElements = $modelProjectCategories->fetchChildIds($filter);
        $allCategories = array_unique(array_merge($filter, $childElements));
        $stringCategories = implode(',', $allCategories);

        $statement->where("(
        		project.project_category_id IN ({$stringCategories})
        		)");

        return $statement;
    }

    /**
     * @param Zend_Db_Select $statement
     * @param array          $filterArrayValue
     *
     * @return Zend_Db_Select
     */
    protected function generateOrderFilter(Zend_Db_Select $statement, $filterArrayValue)
    {
        if (!isset($filterArrayValue[self::FILTER_NAME_ORDER])) {
            $filterValue = '';
        } else {
            $filterValue = $filterArrayValue[self::FILTER_NAME_ORDER];
        }
        switch ($filterValue) {
            case 'latest':
                $statement->order('project.major_updated_at DESC');
                //$statement->order('project.changed_at DESC');
                break;

            case 'rating':
                //$statement->order(array('amount_received DESC', 'count_plings DESC', 'latest_pling DESC', 'project.created_at DESC'));
                //$statement->order(array(new Zend_Db_Expr('(round(((count_likes + 6) / ((count_likes + count_dislikes) + 12)),2) * 100) DESC'),'amount_received DESC', 'count_plings DESC', 'latest_pling DESC', 'project.created_at DESC'));
                /*$statement->order(array(
                    new Zend_Db_Expr('(round(((count_likes + 6) / ((count_likes + count_dislikes) + 12)),2) * 100) DESC'),
                    'project.created_at DESC'
                ));*/
                $statement->order('project.laplace_score DESC');
                break;
            case 'plinged':
                $statement->order('project.count_plings DESC');
                break;
            case 'test':
                $statement->order('project.laplace_score_test DESC');
                break;
            case 'top':
                $statement->order('project.laplace_score_old DESC');
                break;
            case 'download':
                $statement->order('project.count_downloads_hive DESC');
                break;
            case 'downloadQuarter':
                $statement->order('project.count_downloads_quarter DESC');
                break;


            case 'hot':
               
                $statement->order(array(
                    new Zend_Db_Expr('(round(((count_likes + 6) / ((count_likes + count_dislikes) + 12)),2) * 100) DESC'),                    
                    'count_plings DESC',                    
                    'project.created_at DESC'
                ));
                $statement->where(' project.created_at >= (NOW()- INTERVAL 14 DAY)');
                break;

            case 'alpha':
            default:
                $statement->order('project.title');
        }

        return $statement;
    }

    /**
     * @param Zend_Db_Select $statement
     * @param array          $filterArrayValue
     *
     * @return Zend_Db_Select
     */
    /*protected function generateOriginalFilter(Zend_Db_Select $statement, $filterArrayValue)
    {
        if (false == isset($filterArrayValue[self::FILTER_NAME_ORIGINAL])) {
            return $statement;
        }

        $filter = $filterArrayValue[self::FILTER_NAME_ORIGINAL];

        if (is_array($filter)) {
            // todo maybe for other tags filter
        } else {
            $statement->where('find_in_set(?, tags)', $filter);
        }

        return $statement;
    }*/
    
    /**
     * @param Zend_Db_Select $statement
     * @param array          $filterArrayValue
     *
     * @return Zend_Db_Select
     */
    protected function generateFavoriteFilter(Zend_Db_Select $statement, $filterArrayValue)
    {
        if (false == isset($filterArrayValue[self::FILTER_NAME_FAVORITE])) {
            return $statement;
        }

        $filterMemberId = $filterArrayValue[self::FILTER_NAME_FAVORITE];

        if ( null != $filterMemberId) {
            $statement->where('project_follower.member_id = ?', $filterMemberId);
            $statement->setIntegrityCheck(false)->join('project_follower', 'project.project_id = project_follower.project_id', array('project_follower_id'));
        }

        return $statement;
    }

    /**
     * @param Zend_Db_Select $statement
     *
     * @return Zend_Db_Select
     */
    protected function generateReportedSpamFilter(Zend_Db_Select $statement)
    {
        return $statement->where('(amount_reports is null)');
    }

    /**
     * @param int    $member_id
     * @param array  $values
     * @param string $username
     *
     * @return Zend_Db_Table_Row_Abstract
     * @throws Exception
     * @throws Zend_Db_Table_Exception
     */
    public function createProject($member_id, $values, $username)
    {
        $values = (array)$values;
        if (empty($member_id)) {
            throw new Zend_Db_Table_Exception('member_id is not set');
        }
        if (empty($username)) {
            throw new Zend_Db_Table_Exception('username is not set');
        }
        // check important values for a new project
        $values['uuid'] = (!array_key_exists('uuid', $values)) ? Local_Tools_UUID::generateUUID() : $values['uuid'];
        $values['member_id'] = (!array_key_exists('member_id', $values)) ? $member_id : $values['member_id'];
        $values['status'] = (!array_key_exists('status', $values)) ? self::PROJECT_INACTIVE : $values['status'];
        $values['type_id'] = (!array_key_exists('type_id', $values)) ? self::ITEM_TYPE_PRODUCT : $values['type_id'];
        $values['created_at'] = (!array_key_exists('created_at', $values)) ? new Zend_Db_Expr('NOW()') : $values['created_at'];
        $values['start_date'] = (!array_key_exists('start_date', $values)) ? new Zend_Db_Expr('NULL') : $values['start_date'];
        $values['creator_id'] = (!array_key_exists('creator_id', $values)) ? $member_id : $values['creator_id'];
        $values['gitlab_project_id'] = (empty($values['gitlab_project_id'])) ? new Zend_Db_Expr('NULL') : $values['gitlab_project_id'];

        if ($username == 'pling editor') {
            $values['claimable'] = (!array_key_exists('claimable', $values)) ? self::PROJECT_CLAIMABLE : $values['claimable'];
        }

        $savedRow = $this->save($values);

        return $savedRow;
    }

    /**
     * @param int   $project_id
     * @param array $values
     *
     * @return Zend_Db_Table_Row_Abstract
     * @throws Exception
     * @throws Zend_Db_Table_Exception
     */
    public function updateProject($project_id, $values)
    {
        $values = (array)$values;
        $projectData = $this->find($project_id)->current();
        if (empty($projectData)) {
            throw new Zend_Db_Table_Exception('project_id not found');
        }

        $values['gitlab_project_id'] = (empty($values['gitlab_project_id'])) ? new Zend_Db_Expr('NULL') : $values['gitlab_project_id'];

        $projectData->setFromArray($values)->save();

        return $projectData;
    }

    /**
     * @param int $member_id
     *
     * @return array|mixed
     */
    public function fetchMainProject($member_id)
    {
        $sql = "SELECT * FROM {$this->_name} WHERE type_id = :type AND member_id = :member";

        //        $this->_db->getProfiler()->setEnabled(true);
        $result = $this->_db->fetchRow($sql, array('type' => self::PROJECT_TYPE_PERSONAL, 'member' => (int)$member_id));
        //        $dummy = $this->_db->getProfiler()->getLastQueryProfile()->getQuery();
        //        $this->_db->getProfiler()->setEnabled(true);

        if (count($result) > 0) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * @param $project_id
     *
     * @return Zend_Db_Table_Row_Abstract
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchProductDataFromMV($project_id)
    {
        $sql = "SELECT * FROM `stat_projects` WHERE `project_id` = :project_id";
        $resultSet = $this->_db->query($sql, array('project_id' => $project_id))->fetch();
        if (false === $resultSet) {
            return $this->generateRowClass(array());
        }

        return $this->generateRowClass($resultSet);
    }


    /**
     * @return array
     */
    public function fetchGhnsExcludedProjects()
    {
        $sql = "
        	SELECT `p`.`project_id`, `p`.`title`, `l`.`member_id` AS `exclude_member_id`, `l`.`time` AS `exclude_time`, `m`.`username` AS `exclude_member_name` FROM `project` `p`
                JOIN `activity_log` `l` ON `l`.`project_id` = `p`.`project_id` AND `l`.`activity_type_id` = 314
                INNER JOIN `member` `m` ON `m`.`member_id` = `l`.`member_id`
                WHERE `p`.`ghns_excluded` = 1

        ";

        $list = $this->_db->fetchAll($sql);

        return $list;
    }

    public function getUserCreatingCategorys($member_id)
    {
        $sql = "
                    select
                       c.title as category1,
                       count(1) as cnt
                      from project p
                      join project_category c on p.project_category_id = c.project_category_id
                      where p.status = 100
                      and p.member_id =:member_id
                      and p.type_id = 1
                      group by c.title
                      order by cnt desc, c.title asc
                  ";
        $result = $this->_db->fetchAll($sql, array('member_id' => $member_id));
        return $result;
    }

    /**
     * @return array
     */
    public function getUserActiveProjects($member_id, $limit = null, $offset = null)
    {
        // for member me page
        $sql = "
                        SELECT
                        `p`.`project_id`,
                        `p`.`title`,
                        `p`.`created_at`  AS `project_created_at`,
                        `p`.`changed_at` AS `project_changed_at`,
                        `pr`.`likes` AS count_likes,
                        `pr`.`dislikes`AS count_dislikes,
                        IFNULL(pr.score_with_pling, 500) AS laplace_score,
                        `p`.`member_id`,
                        `cat`.`title` AS `catTitle`,
                        `p`.`project_category_id`,
                        `p`.`image_small`,
                        (SELECT count(1) FROM `project_plings` `l` WHERE `p`.`project_id` = `l`.`project_id` AND `l`.`is_deleted` = 0 AND `l`.`is_active` = 1 ) `countplings`,
                        c.cnt cntCategory
                        FROM `project` `p`
                        join project_category cat on p.project_category_id = cat.project_category_id
                        LEFT join  stat_rating_project AS pr  ON p.project_id = pr.project_id
                        left join stat_cnt_projects_catid_memberid c on p.project_category_id = c.project_category_id and p.member_id = c.member_id
                        WHERE `p`.`status` =100
                        and `p`.`type_id` = 1
                        AND `p`.`member_id` = :member_id
                        ORDER BY cntCategory desc,catTitle asc, `p`.`changed_at` DESC

        ";

        if (isset($limit)) {
            $sql = $sql . ' limit ' . $limit;
        }

        if (isset($offset)) {
            $sql = $sql . ' offset ' . $offset;
        }

        $result = $this->_db->fetchAll($sql, array('member_id' => $member_id));
        if ($result) {
            return $this->generateRowClass($result);
        } else {
            return null;
        }
    }

    /**
     * @return array
     */
    public function getUserActiveProjectsDuplicatedSourceurl($member_id, $limit = null, $offset = null)
    {
        // for member me page
        $sql = "
                      select * from
                      (
                      SELECT
                            `p`.`project_id`,
                            `p`.`title`,
                            `p`.`created_at`  AS `project_created_at`,
                            `p`.`changed_at` AS `project_changed_at`,
                            `pr`.`likes` AS count_likes,
                            `pr`.`dislikes`AS count_dislikes,
                             IFNULL(pr.score_with_pling, 500) AS laplace_score,
                            `p`.`member_id`,
                            `cat`.`title` AS `catTitle`,
                            `p`.`project_category_id`,
                            `p`.`image_small`,
                            (SELECT count(1) FROM `project_plings` `l` WHERE `p`.`project_id` = `l`.`project_id` AND `l`.`is_deleted` = 0 AND `l`.`is_active` = 1 ) `countplings`,
                            c.cnt cntCategory,
                              (select count(1) from stat_projects_source_url s where TRIM(TRAILING '/' FROM p.source_url)  = s.source_url) as cntDuplicates
                            FROM `project` `p`
                            join project_category cat on p.project_category_id = cat.project_category_id
                            left join stat_cnt_projects_catid_memberid c on p.project_category_id = c.project_category_id and p.member_id = c.member_id
                            LEFT join  stat_rating_project AS pr  ON p.project_id = pr.project_id
                            WHERE `p`.`status` =100
                            and `p`.`type_id` = 1
                            AND `p`.`member_id` = :member_id
                            ORDER BY cntCategory desc,catTitle asc, `p`.`changed_at` DESC
                            ) t where t.cntDuplicates >1

        ";

        if (isset($limit)) {
            $sql = $sql . ' limit ' . $limit;
        }

        if (isset($offset)) {
            $sql = $sql . ' offset ' . $offset;
        }

        $result = $this->_db->fetchAll($sql, array('member_id' => $member_id));
        if ($result) {
            return $this->generateRowClass($result);
        } else {
            return null;
        }
    }

    /**
     * @return cnt
     */
    public function getOriginalProjectsForMemberCnt($member_id)
    {        
        $sql = "
            SELECT
            count(1) as cnt
            FROM stat_projects p
            inner join tag_object t on tag_id = 2451  and tag_group_id=11 and tag_type_id = 1 and is_deleted = 0
            and t.tag_object_id = p.project_id
            WHERE member_id = :member_id             
        ";
        $result = $this->_db->fetchRow($sql, array('member_id' => $member_id));
        if ($result) {
            return $result['cnt'];
        } else {
            return 0;
        }
    }

    /**
     * @return cnt
     */
    public function getOriginalProjectsForMember($member_id, $limit=null, $offset=null)
    {        
        $sql = "
            SELECT
            *
            FROM stat_projects p
            inner join tag_object t on tag_id = 2451  and tag_group_id=11 and tag_type_id = 1 and is_deleted = 0
            and t.tag_object_id = p.project_id
            WHERE member_id = :member_id                       
        ";

        if (isset($limit)) {
            $sql = $sql . ' limit ' . $limit;
        }

        if (isset($offset)) {
            $sql = $sql . ' offset ' . $offset;
        }

        $result = $this->_db->fetchAll($sql, array('member_id' => $member_id));
        if ($result) {
            return $this->generateRowClass($result);
        } else {
            return null;
        }
     
    }

     /**
     * @return int
     */
    public function getUnpublishedProjectsForMemberCnt($member_id)
    {
        // for member me page
        $sql = "
                        SELECT
                        count(1) as cnt
                        FROM `project` `p`                        
                        WHERE `p`.`status` = 40
                        and `p`.`type_id` = 1
                        AND `p`.`member_id` = :member_id                        
        ";
        $result = $this->_db->fetchRow($sql, array('member_id' => $member_id));
        if ($result) {
            return $result['cnt'];
        } else {
            return 0;
        }
    }
    

    /**
     * @return array
     */
    public function getUnpublishedProjectsForMember($member_id, $limit = null, $offset = null)
    {
        // for member me page
        $sql = "
                        SELECT
                        `p`.`project_id`,
                        `p`.`title`,
                        `p`.`created_at`  AS `project_created_at`,
                        `p`.`changed_at` AS `project_changed_at`,
                        `pr`.`likes` AS count_likes,
                        `pr`.`dislikes`AS count_dislikes,
                        IFNULL(pr.score_with_pling, 500) AS laplace_score,
                        `p`.`member_id`,
                        `cat`.`title` AS `catTitle`,
                        `p`.`project_category_id`,
                        `p`.`image_small`,
                        (SELECT count(1) FROM `project_plings` `l` WHERE `p`.`project_id` = `l`.`project_id` AND `l`.`is_deleted` = 0 AND `l`.`is_active` = 1 ) `countplings`
                        FROM `project` `p`
                        join project_category cat on p.project_category_id = cat.project_category_id
                        LEFT join  stat_rating_project AS pr  ON p.project_id = pr.project_id
                        WHERE `p`.`status` = 40
                        and `p`.`type_id` = 1
                        AND `p`.`member_id` = :member_id 
                        ORDER BY catTitle asc, `p`.`changed_at` DESC

        ";

        if (isset($limit)) {
            $sql = $sql . ' limit ' . $limit;
        }

        if (isset($offset)) {
            $sql = $sql . ' offset ' . $offset;
        }

        $result = $this->_db->fetchAll($sql, array('member_id' => $member_id));
        if ($result) {
            return $this->generateRowClass($result);
        } else {
            return null;
        }
    }

    
     /**
     * @return int
     */
    public function getDeletedProjectsForMemberCnt($member_id)
    {
        // for member me page
        $sql = "
                        SELECT
                        count(1) as cnt
                        FROM `project` `p`                        
                        WHERE `p`.`status` = 30
                        and `p`.`type_id` = 1
                        AND `p`.`member_id` = :member_id                        
        ";
        $result = $this->_db->fetchRow($sql, array('member_id' => $member_id));
        if ($result) {
            return $result['cnt'];
        } else {
            return 0;
        }
    }
    

    /**
     * @return array
     */
    public function getDeletedProjectsForMember($member_id, $limit = null, $offset = null)
    {
        // for member me page
        $sql = "
                        SELECT
                        `p`.`project_id`,
                        `p`.`title`,
                        `p`.`created_at`  AS `project_created_at`,
                        `p`.`changed_at` AS `project_changed_at`,
                        `pr`.`likes` AS count_likes,
                        `pr`.`dislikes`AS count_dislikes,
                        IFNULL(pr.score_with_pling, 500) AS laplace_score,
                        `p`.`member_id`,
                        `cat`.`title` AS `catTitle`,
                        `p`.`project_category_id`,
                        `p`.`image_small`,
                        (SELECT count(1) FROM `project_plings` `l` WHERE `p`.`project_id` = `l`.`project_id` AND `l`.`is_deleted` = 0 AND `l`.`is_active` = 1 ) `countplings`
                        FROM `project` `p`
                        join project_category cat on p.project_category_id = cat.project_category_id
                        LEFT join  stat_rating_project AS pr  ON p.project_id = pr.project_id
                        WHERE `p`.`status` = 30
                        and `p`.`type_id` = 1
                        AND `p`.`member_id` = :member_id 
                        ORDER BY catTitle asc, `p`.`changed_at` DESC

        ";

        if (isset($limit)) {
            $sql = $sql . ' limit ' . $limit;
        }

        if (isset($offset)) {
            $sql = $sql . ' offset ' . $offset;
        }

        $result = $this->_db->fetchAll($sql, array('member_id' => $member_id));
        if ($result) {
            return $this->generateRowClass($result);
        } else {
            return null;
        }
    }
    
    public function fetchFilesForProjects($projects)
    {

        $ids=[];
        foreach ($projects as $p) {
            $ids[] = $p->project_id;
        }        
        $sql = "
                select 
                p.project_id
                ,f.id
                ,f.name
                ,f.type
                ,f.size
                ,f.title
                ,f.collection_id
                from stat_projects p, ppload.ppload_files f
                where p.ppload_collection_id = f.collection_id
                and f.active = 1 
                and p.project_id in ( ".implode(',', $ids).")
        ";      
        $result = $this->_db->fetchAll($sql);
        return $result;
    }
    public function fetchFilesForProject($project_id)
    {
   
        $sql = "
                select 
                f.id
                ,f.name
                ,f.type
                ,f.size
                ,f.title
                ,f.collection_id
                from stat_projects p, ppload.ppload_files f
                where p.ppload_collection_id = f.collection_id
                and f.active = 1 
                and p.project_id = :project_id
        ";      
        $result = $this->_db->fetchAll($sql,array("project_id"=>$project_id));
        return $result;
    }
    /**
     * @param int      $member_id
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function fetchAllFeaturedProjectsForMember($member_id, $limit = null, $offset = null)
    {
        // for member me page
        $sql = "
                          SELECT
                          `p`.`project_id`,
                          `p`.`title`,
                          `p`.`created_at`  AS `project_created_at`,
                          `p`.`changed_at` AS `project_changed_at`,
                          `p`.`count_likes`,
                          `p`.`count_dislikes`,
                          `p`.`laplace_score`,
                          `p`.`member_id`,
                          `p`.`cat_title` AS `catTitle`,
                          `p`.`image_small`,
                          (SELECT count(1) FROM `project_plings` `l` WHERE `p`.`project_id` = `l`.`project_id` AND `l`.`is_deleted` = 0 AND `l`.`is_active` = 1 ) `countplings`
                          FROM `stat_projects` `p`
                          WHERE `p`.`status` =100
                          AND `p`.`type_id` = 1
                          AND `featured` = 1
                          AND `p`.`member_id` = :member_id
                          ORDER BY `p`.`changed_at` DESC
          ";

        if (isset($limit)) {
            $sql = $sql . ' limit ' . $limit;
        }

        if (isset($offset)) {
            $sql = $sql . ' offset ' . $offset;
        }

        $result = $this->_db->fetchAll($sql, array('member_id' => $member_id));
        if ($result) {
            return $this->generateRowClass($result);
        } else {
            return null;
        }
    }


    /**
     * @param int      $member_id
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function fetchAllCollectionsForMember($member_id, $limit = null, $offset = null)
    {
        // for member me page
        $sql = "
                          SELECT
                          `p`.`project_id`,
                          `p`.`title`,
                          `p`.`created_at`  AS `project_created_at`,
                          `p`.`changed_at` AS `project_changed_at`,
                          `p`.`count_likes`,
                          `p`.`count_dislikes`,
                          `p`.`laplace_score`,
                          `p`.`member_id`,
                          `p`.`cat_title` AS `catTitle`,
                          `p`.`image_small`,
                          (SELECT count(1) FROM `project_plings` `l` WHERE `p`.`project_id` = `l`.`project_id` AND `l`.`is_deleted` = 0 AND `l`.`is_active` = 1 ) `countplings`
                          FROM `stat_projects` `p`
                          WHERE `p`.`status` =100
                          AND `p`.`type_id` = 3
                          AND `p`.`member_id` = :member_id
                          ORDER BY `p`.`changed_at` DESC
          ";

        if (isset($limit)) {
            $sql = $sql . ' limit ' . $limit;
        }

        if (isset($offset)) {
            $sql = $sql . ' offset ' . $offset;
        }

        $result = $this->_db->fetchAll($sql, array('member_id' => $member_id));
        if ($result) {
            return $this->generateRowClass($result);
        } else {
            return null;
        }
    }

    /**
     * @param string   $orderby
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     */
    public function fetchDuplicatedSourceProjects($orderby = 'source_url asc', $limit = null, $offset = null)
    {
        $sql = "
            SELECT
            `source_url`
            ,count(1) AS `cnt`,
            GROUP_CONCAT(`p`.`project_id` ORDER BY `p`.`created_at`) `pids`
            FROM `stat_projects_source_url` `p`
            GROUP BY `source_url`
            HAVING count(1)>1
       ";
        if (isset($orderby)) {
            $sql = $sql . '  order by ' . $orderby;
        }

        if (isset($limit)) {
            $sql .= ' limit ' . (int)$limit;
        }

        if (isset($offset)) {
            $sql .= ' offset ' . (int)$offset;
        }
        $result = $this->_db->fetchAll($sql);

        return $result;
    }

    /**
     * @return mixed
     */
    public function getTotalCountDuplicates()
    {

        $sql = "
          SELECT count(1) AS `cnt` FROM
          (
                      SELECT
                       `source_url`
                       ,count(1) AS `cnt`,
                       GROUP_CONCAT(`p`.`project_id` ORDER BY `p`.`created_at`) `pids`
                       FROM `stat_projects_source_url` `p`
                       GROUP BY `p`.`source_url`
                       HAVING count(1)>1
          ) `a`
      ";
        $result = $this->_db->fetchAll($sql);

        return $result[0]['cnt'];;
    }

    /**
     * @param string $source_url
     *
     * @return mixed
     */
    public function getCountSourceUrl($source_url)
    {
        $last = substr($source_url, -1);
        if ($last == '/') {
            $source_url = substr($source_url, 0, -1);
        }
        $sql = "
            SELECT count(1) AS `cnt` FROM
            `stat_projects_source_url` `p`
            WHERE `p`.`source_url`= :source_url
      ";
        $result = $this->_db->fetchAll($sql, array('source_url' => $source_url));

        return $result[0]['cnt'];
    }


    public function getSourceUrlProjects($source_url)
    {
        $last = substr($source_url, -1);
        if ($last == '/') {
            $source_url = substr($source_url, 0, -1);
        }
        $sql = "
            SELECT
                p.project_id,
                pj.title,
                pj.member_id,
                pj.created_at,
                pj.changed_at,
                m.username
                FROM stat_projects_source_url p
                inner join project pj on p.project_id = pj.project_id and pj.status=100
                inner join member m on pj.member_id = m.member_id
            WHERE p.source_url= :source_url
      ";
        $result = $this->_db->fetchAll($sql, array('source_url' => $source_url));

        return $result;
    }

    /**
     * @param int $member_id
     *
     * @return mixed
     */
    public function getCountProjectsDuplicateSourceurl($member_id)
    {

        $sql = "
           SELECT count(1) AS `cnt`
           FROM
           (
              SELECT  `p`.`source_url`
              ,(SELECT count(1) FROM `stat_projects_source_url` `pp` WHERE `pp`.`source_url`=`p`.`source_url`) `cnt`
              FROM `stat_projects_source_url` `p`
              WHERE `p`.`member_id` = :member_id
           ) `t` WHERE `t`.`cnt`>1
      ";
        $result = $this->_db->fetchAll($sql, array('member_id' => $member_id));

        return $result[0]['cnt'];
    }

    /**
     * @param $ids
     *
     * @return Zend_Db_Table_Row_Abstract
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchProjects($ids)
    {
        $sql = "SELECT * FROM stat_projects WHERE project_id in (" . $ids . ") order by project_id";
        $resultSet = $this->_db->fetchAll($sql);

        return $this->generateRowSet($resultSet);
    }

    /**
     * @param $project_id
     * @return true/false
     * @throws Zend_Db_Statement_Exception
     */
    public function validateDeleteProjectFromSpam($project_id)
    {
      //produkt ist ueber 6 monate alt oder produkt hat ueber 5 kommentare oder produkt hat minimum 1 pling
      // darf nicht gelöscht werden
      $sql ='select count_comments
            ,created_at
            , (created_at+ INTERVAL 6 MONTH < NOW()) is_old
            ,(select count(1) from project_plings f where f.project_id = p.project_id and f.is_deleted = 0) plings
            FROM project p where project_id =:project_id';
      $result = $this->_db->fetchRow($sql, array(
                            'project_id'     => $project_id,
            ));

      if($result['count_comments'] >5 || $result['is_old'] ==1 || $result['plings']>0)
      {
        return false;
      }
      return true;
    }

    

}
