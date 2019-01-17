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
    const FILTER_NAME_PACKAGETYPE = 'package_type';
    const FILTER_NAME_ORIGINAL = 'original';
    const FILTER_NAME_MEMBER = 'member';
    const FILTER_NAME_ORDER = 'order';
    const FILTER_NAME_LOCATION = 'location';

    const ITEM_TYPE_DUMMY = 0;
    const ITEM_TYPE_PRODUCT = 1;
    const ITEM_TYPE_UPDATE = 2;

    const TAG_LICENCE_GID = 7;
    const TAG_TYPE_ID = 1;

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
                  laplace_score(`p`.`count_likes`,`p`.`count_dislikes`) AS `laplace_score`,
                 `view_reported_projects`.`amount_reports` AS `amount_reports`,
                (SELECT `tag`.`tag_fullname` FROM `tag_object`, `tag` WHERE `tag_object`.`tag_id`=`tag`.`tag_id` AND `tag_object_id` = `p`.`project_id` AND `tag_object`.`is_deleted`=0 AND `tag_group_id` = :tag_licence_gid AND `tag_type_id` = :tag_type_id  ORDER BY `tag_object`.`tag_created` DESC LIMIT 1)
                                AS `project_license_title`
                FROM `project` AS `p`
                  JOIN `member` AS `m` ON `p`.`member_id` = `m`.`member_id` AND `m`.`is_active` = 1 AND `m`.`is_deleted` = 0
                  JOIN `project_category` AS `pc` ON `p`.`project_category_id` = `pc`.`project_category_id`
                  LEFT JOIN `view_reported_projects` ON ((`view_reported_projects`.`project_id` = `p`.`project_id`))                  
                WHERE 
                  `p`.`project_id` = :projectId
                  AND `p`.`status` >= :projectStatus AND `p`.`type_id` = :typeId
        ';
        $result = $this->_db->fetchRow($sql, array(
            'projectId'       => $project_id,
            'projectStatus'   => self::PROJECT_INACTIVE,
            'typeId'          => self::PROJECT_TYPE_STANDARD,
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
     * @param int $project_id
     *
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function fetchProductInfo_($project_id)
    {
        $sql = '
                SELECT
                  `p`.*,
                  `p`.`validated` AS `project_validated`,
                  `p`.`uuid` AS `project_uuid`,
                  `p`.`status` AS `project_status`,
                  `p`.`created_at` AS `project_created_at`,
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
                  laplace_score(`p`.`count_likes`,`p`.`count_dislikes`) AS `laplace_score`,
                 `view_reported_projects`.`amount_reports` AS `amount_reports`,
                `project_license`.`title` AS `project_license_title`
                FROM `project` AS `p`
                  JOIN `member` AS `m` ON `p`.`member_id` = `m`.`member_id` AND `m`.`is_active` = 1 AND `m`.`is_deleted` = 0
                  JOIN `project_category` AS `pc` ON `p`.`project_category_id` = `pc`.`project_category_id`
                  LEFT JOIN `view_reported_projects` ON ((`view_reported_projects`.`project_id` = `p`.`project_id`))
                  LEFT JOIN `project_license` ON ((`project_license`.`project_license_id` = `p`.`project_license_id`))
                WHERE 
                  `p`.`project_id` = :projectId
                  AND `p`.`status` >= :projectStatus AND `p`.`type_id` = :typeId
        ';
        $result = $this->_db->fetchRow($sql, array(
            'projectId'     => $project_id,
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
        $q = $this->select()->from('stat_projects', array(
            'project_id',
            'image_small',
            'title',
            'catTitle' => 'cat_title'
        ))->setIntegrityCheck(false)
          ->where('status = ?', self::PROJECT_ACTIVE)
          ->where('member_id = ?', $project->member_id, 'INTEGER')
          ->where('project_id != ?', $project->project_id, 'INTEGER')
          ->where('type_id = ?', self::PROJECT_TYPE_STANDARD)
          ->where('amount_reports is null')
          ->where('project_category_id = ?', $project->project_category_id, 'INTEGER')
          ->limit($count)
          ->order('project_created_at DESC')
        ;

        $storeConfig = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;
        $storePackageTypeIds = null;
        if ($storeConfig) {
            $storePackageTypeIds = $storeConfig->package_type;
        }

        if ($storePackageTypeIds) {
            $q = $this->generatePackageTypeFilter($q, array(self::FILTER_NAME_PACKAGETYPE => $storePackageTypeIds));
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
    protected function generatePackageTypeFilter(Zend_Db_Select $statement, $filterArrayValue)
    {
        if (false == isset($filterArrayValue[self::FILTER_NAME_PACKAGETYPE])) {
            return $statement;
        }

        $filter = $filterArrayValue[self::FILTER_NAME_PACKAGETYPE];

        if (is_array($filter)) {
            $statement->join(array(
                'package_type' => new Zend_Db_Expr('(SELECT DISTINCT project_id FROM project_package_type WHERE package_type_id in ('
                    . $filter . '))')
            ), 'project.project_id = package_type.project_id', array());
        } else {
            $statement->where('find_in_set(?, package_types)', $filter);
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

        $q = $this->select()->from('stat_projects', array(
            'project_id',
            'image_small',
            'title',
            'catTitle' => 'cat_title'
        ))->setIntegrityCheck(false)->where('status = ?', self::PROJECT_ACTIVE)
                  ->where('member_id != ?', $project->member_id, 'INTEGER')->where('type_id = ?', 1)
                  ->where('amount_reports is null')
                  ->where('project_category_id = ?', $project->project_category_id, 'INTEGER')->limit($count, $offset)
                  ->order('project_created_at DESC')
        ;

        $storeConfig = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;
        $storePackageTypeIds = null;
        if ($storeConfig) {
            $storePackageTypeIds = $storeConfig->package_type;
        }

        if ($storePackageTypeIds) {
            $q = $this->generatePackageTypeFilter($q, array(self::FILTER_NAME_PACKAGETYPE => $storePackageTypeIds));
        }

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . $q->__toString());

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
        $storePackageTypeIds = (false === empty($store_config->package_type)) ? $store_config->package_type : null;

        $cacheName = __FUNCTION__ . '_' . md5(serialize($idCategory) . $withSubCat . serialize($storePackageTypeIds));
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');

        if (false !== ($resultSet = $cache->load($cacheName))) {
            return (int)$resultSet[0]['count_active_projects'];
        }

        $select = $this->select()->setIntegrityCheck(false)->from('stat_projects', array('count_active_projects' => 'COUNT(1)'))
                       ->where('status = ? ', self::PROJECT_ACTIVE)->where('type_id = ?', self::PROJECT_TYPE_STANDARD)
        ;

        $select = $this->generatePackageTypeFilter($select, array(self::FILTER_NAME_PACKAGETYPE => $storePackageTypeIds));

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
     * @return mixed
     */
    public function fetchTotalProjectsCount($is_current_store=false)
    {

        $sql ="SELECT count(1) AS `total_project_count` FROM `stat_projects`";
        if($is_current_store){            
            $info = new Default_Model_Info();
            $activeCategories = $info->getActiveCategoriesForCurrentHost();
            $sql .= ' where project_category_id IN (' . implode(',', $activeCategories) . ')';
            $storeConfig = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;
            if($storeConfig && $storeConfig->package_type)
            {
                $sql .= ' AND find_in_set('.$storeConfig->package_type.', package_types)';
            }            
        }
        $result = $this->_db->fetchRow($sql);
        return $result['total_project_count'];
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
        $statement = $this->generatePackageTypeFilter($statement, $inputFilterParams);
        $statement = $this->generateOriginalFilter($statement, $inputFilterParams);
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
        $statement->where('project.status = ?', self::PROJECT_ACTIVE)->where('project.type_id=?', self::PROJECT_TYPE_STANDARD);

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
                $statement->order('project.changed_at DESC');
                break;

            case 'top':
                //$statement->order(array('amount_received DESC', 'count_plings DESC', 'latest_pling DESC', 'project.created_at DESC'));
                //$statement->order(array(new Zend_Db_Expr('(round(((count_likes + 6) / ((count_likes + count_dislikes) + 12)),2) * 100) DESC'),'amount_received DESC', 'count_plings DESC', 'latest_pling DESC', 'project.created_at DESC'));
                $statement->order(array(
                    new Zend_Db_Expr('(round(((count_likes + 6) / ((count_likes + count_dislikes) + 12)),2) * 100) DESC'),
                    'project.created_at DESC'
                ));

                break;

            case 'download':
                $statement->order('project.count_downloads_hive DESC');
                break;
            case 'downloadQuarter':
                $statement->order('project.count_downloads_quarter DESC');
                break;

            case 'hot':
                //$statement->order(array('amount_received DESC', 'count_plings DESC', 'latest_pling DESC', 'project.created_at DESC'));
                $statement->order(array(
                    new Zend_Db_Expr('(round(((count_likes + 6) / ((count_likes + count_dislikes) + 12)),2) * 100) DESC'),
                    'amount_received DESC',
                    'count_plings DESC',
                    'latest_pling DESC',
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
    protected function generateOriginalFilter(Zend_Db_Select $statement, $filterArrayValue)
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
                        `p`.`count_likes`,
                        `p`.`count_dislikes`,
                        laplace_score(`p`.`count_likes`, `p`.`count_dislikes`) AS `laplace_score`,
                        `p`.`member_id`,
                        `cat`.`title` AS `catTitle`,
                        `p`.`project_category_id`,    
                        `p`.`image_small`,
                        (SELECT count(1) FROM `project_plings` `l` WHERE `p`.`project_id` = `l`.`project_id` AND `l`.`is_deleted` = 0 AND `l`.`is_active` = 1 ) `countplings`,
                        c.cnt cntCategory
                        FROM `project` `p`
                        join project_category cat on p.project_category_id = cat.project_category_id
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

    // /**
    //  * @return array
    //  */
    // public function getUserActiveProjects($member_id, $limit = null, $offset = null)
    // {
    //     // for member me page
    //     $sql = "
    //                     SELECT
    //                         SUBSTRING_INDEX(SUBSTRING_INDEX(ancestor_path, '|', 2),'|',-1) as cat1,
    //                         SUBSTRING_INDEX(SUBSTRING_INDEX(ancestor_path, '|', 3),'|',-1) as cat2,
    //                         SUBSTRING_INDEX(SUBSTRING_INDEX(ancestor_path, '|', 4),'|',-1) as cat3,
    //                          SUBSTRING_INDEX(SUBSTRING_INDEX(ancestor_path, '|', 5),'|',-1) as cat4,
    //                         SUBSTRING_INDEX(SUBSTRING_INDEX(ancestor_id_path, ',', 2),',',-1) as catid1,
    //                         SUBSTRING_INDEX(SUBSTRING_INDEX(ancestor_id_path, ',', 3),',',-1) as catid2,
    //                         SUBSTRING_INDEX(SUBSTRING_INDEX(ancestor_id_path, ',', 4),',',-1) as catid3,
    //                          SUBSTRING_INDEX(SUBSTRING_INDEX(ancestor_id_path, ',', 5),',',-1) as catid4,
    //                     `p`.`project_id`,
    //                     `p`.`title`,
    //                     `p`.`created_at`  AS `project_created_at`,
    //                     `p`.`changed_at` AS `project_changed_at`,
    //                     `p`.`count_likes`,
    //                     `p`.`count_dislikes`,
    //                     laplace_score(`p`.`count_likes`, `p`.`count_dislikes`) AS `laplace_score`,
    //                     `p`.`member_id`,
    //                     `cat`.`title` AS `catTitle`,
    //                     `p`.`project_category_id`,    
    //                     `p`.`image_small`,
    //                     (SELECT count(1) FROM `project_plings` `l` WHERE `p`.`project_id` = `l`.`project_id` AND `l`.`is_deleted` = 0 AND `l`.`is_active` = 1 ) `countplings`,
    //                     (select count(1) from project pp where pp.member_id = p.member_id and pp.status = 100  and  pp.project_category_id = p.project_category_id) cntCategory
    //                     FROM `project` `p`
    //                     join project_category cat on p.project_category_id = cat.project_category_id
    //                     join stat_cat_tree c on p.project_category_id = c.project_category_id
    //                     WHERE `p`.`status` =100
    //                     AND `p`.`member_id` = :member_id       
    //                     ORDER BY cntCategory desc, `p`.`changed_at` DESC
                      
    //     ";

    //     if (isset($limit)) {
    //         $sql = $sql . ' limit ' . $limit;
    //     }

    //     if (isset($offset)) {
    //         $sql = $sql . ' offset ' . $offset;
    //     }

    //     $result = $this->_db->fetchAll($sql, array('member_id' => $member_id));
    //     if ($result) {
    //         return $this->generateRowClass($result);
    //     } else {
    //         return null;
    //     }
    // }

    

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
              SELECT DISTINCT `p`.`source_url`
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
        $sql = "SELECT * FROM stat_projects WHERE project_id in (" . $ids . ")";
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