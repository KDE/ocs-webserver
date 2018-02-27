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
    const FILTER_NAME_MEMBER = 'member';
    const FILTER_NAME_ORDER = 'order';
    const FILTER_NAME_LOCATION = 'location';

    const ITEM_TYPE_DUMMY = 0;
    const ITEM_TYPE_PRODUCT = 1;
    const ITEM_TYPE_UPDATE = 2;


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

    public function setClaimedByMember($member_id, $id)
    {
        $updateValues = array(
            'claimed_by_member' => $member_id,
            'changed_at'        => new Zend_Db_Expr('Now()')
        );

        $this->update($updateValues, $this->_db->quoteInto('project_id=?', $id, 'INTEGER'));
    }

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
     * @param int $id
     */
    public function setActive($id)
    {
        $updateValues = array(
            'status'     => self::PROJECT_ACTIVE,
            'deleted_at' => null
        );

        $this->update($updateValues, $this->_db->quoteInto('project_id=?', $id, 'INTEGER'));

        $this->setActiveForUpdates($id);
        $this->setActiveForComments($id);
    }

    /**
     * @param int $id
     */
    protected function setActiveForUpdates($id)
    {
        $updateValues = array(
            'status'     => self::PROJECT_ACTIVE,
            'deleted_at' => null
        );

        $this->update($updateValues, $this->_db->quoteInto('pid=?', $id, 'INTEGER'));
    }

    private function setActiveForComments($id)
    {
        $modelComments = new Default_Model_ProjectComments();
        $modelComments->setAllCommentsForProjectActivated($id);
    }

    /**
     * @param int $id
     */
    public function setInActive($id)
    {
        $id = (int)$id;
        $updateValues = array(
            'status'     => self::PROJECT_INACTIVE,
            'deleted_at' => new Zend_Db_Expr('Now()')
        );

        $this->update($updateValues, 'status > 40 AND project_id='.$id);

        $this->setInActiveForUpdates($id);
        $this->setDeletedForComments($id);
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

        $this->update($updateValues, 'status > 40 AND pid='. $id);
    }

    private function setDeletedForComments($id)
    {
        $modelComments = new Default_Model_ProjectComments();
        $modelComments->setAllCommentsForProjectDeleted($id);
    }

    /**
     * @param int $id
     */
    public function setDeleted($id)
    {
        $id = (int)$id;
        $updateValues = array(
            'status'     => self::PROJECT_DELETED,
            'deleted_at' => new Zend_Db_Expr('Now()')
        );

        $this->update($updateValues, 'status > 30 AND project_id='. $id);

        $this->setDeletedForUpdates($id);
        $this->setDeletedForComments($id);
        $this->setDeletedInMaterializedView($id);
    }

    /**
     * @param int $id
     */
    protected function setDeletedForUpdates($id)
    {
        $id = (int)$id;
        $updateValues = array(
            'status'     => self::PROJECT_DELETED,
            'deleted_at' => new Zend_Db_Expr('Now()')
        );

        $this->update($updateValues,'status > 30 AND pid='. $id);
    }

    private function setDeletedInMaterializedView($id)
    {
        $sql = "UPDATE stat_projects SET status = :new_status WHERE project_id = :project_id";

        $result = $this->_db->query($sql, array('new_status' => self::PROJECT_DELETED, 'project_id' => $id))->execute();
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
                  ->where('project.member_id = ?', $member_id, 'INTEGER')
                  ->where('project.type_id = ?', self::PROJECT_TYPE_STANDARD)
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
     */
    public function countAllProjectsForMemberCatFilter($member_id, $onlyActiveProjects = false, $catids = null)
    {
        $q = $this->select()->from($this, array('countAll' => new Zend_Db_Expr('count(*)')))->setIntegrityCheck(false)
                  ->where('project.status >= ?', ($onlyActiveProjects ? self::PROJECT_ACTIVE : self::PROJECT_INACTIVE))
                  ->where('project.member_id = ?', $member_id, 'INTEGER')
                  ->where('project.type_id = ?', self::PROJECT_TYPE_STANDARD)
        ;
        if (isset($catids)) {
            $q->where('project_category_id in (' . $this->_getCatIds($catids) . ')');
        }
        $resultSet = $q->query()->fetchAll();

        return $resultSet[0]['countAll'];
    }

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

    /*
    @ param string categoryids: 111,107 return id and child ids...
    */

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
                  ->where('project.member_id = ?', $member_id, 'INTEGER')
                  ->where('project.type_id = ?', self::PROJECT_TYPE_STANDARD)->order('project_changed_at DESC')
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
                  ->where('project.member_id = ?', $member_id, 'INTEGER')
                  ->where('project.type_id = ?', self::PROJECT_TYPE_STANDARD)->order('project_changed_at DESC')
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
     * @param int $project_id
     *
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function fetchProductForCollectionId($collection_id)
    {
        $sql = '
                SELECT
                  p.*
                FROM project AS p
                WHERE 
                  p.ppload_collection_id = :collectionId
                  AND p.status >= :projectStatus AND p.type_id = :typeId
        ';
        $result = $this->_db->fetchRow($sql, array(
            'collectionId'     => $collection_id,
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
                  p.*,
                  p.validated AS project_validated,
                  p.uuid AS project_uuid,
                  p.status AS project_status,
                  p.created_at AS project_created_at,
                  p.changed_at AS project_changed_at,
                  p.member_id AS project_member_id,
                  p.source_pk AS project_source_pk,
                  p.version AS project_version,
                  pc.title AS cat_title,
                  m.username,
                  m.avatar,
                  m.profile_image_url,
                  m.roleId,
                  m.mail,
                  m.paypal_mail,
                  m.dwolla_id,
               	 laplace_score(p.count_likes,p.count_dislikes) AS laplace_score,
                 `view_reported_projects`.`amount_reports` AS `amount_reports`
                FROM project AS p
                  JOIN member AS m ON p.member_id = m.member_id AND m.is_active = 1 AND m.is_deleted = 0
                  JOIN project_category AS pc ON p.project_category_id = pc.project_category_id
                  LEFT JOIN `view_reported_projects` ON ((`view_reported_projects`.`project_id` = p.`project_id`))
                WHERE 
                  p.project_id = :projectId
                  AND p.status >= :projectStatus AND p.type_id = :typeId
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
                           ->where('project.pid=?', $project_id, 'INTEGER')
                           ->where('project.status>?', self::PROJECT_INACTIVE)
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
        $projectSel = $this->select()->setIntegrityCheck(false)->from($this->_name)
                           ->where('project.pid=?', $project_id, 'INTEGER')
                           ->where('project.status>?', self::PROJECT_INACTIVE)
                           ->where('project.type_id=?', self::PROJECT_TYPE_UPDATE)
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
                FROM stat_projects AS p
                WHERE p.project_category_id = :cat_id and project_id <> :project_id
                ORDER BY p.changed_at DESC
                LIMIT {$count}
        ";

        $result = $this->_db->fetchAll($sql,
            array('cat_id' => $project->project_category_id, 'project_id' => $project->project_id));

        return $this->generateRowSet($result);
    }

    /**
     * @param Zend_Db_Table_Row $project
     * @param int               $count
     *
     * @return Zend_Db_Table_Rowset_Abstract
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
                  ->where('project_category_id = ?', $project->project_category_id, 'INTEGER')
                  ->limit($count)
                  ->order('project_created_at DESC')
        ;

        $storeConfig = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;
        $storePackageTypeIds = null;
        if ($storeConfig) {
            $storePackageTypeIds = $storeConfig['package_type'];
        }

        if ($storePackageTypeIds) {
            $q = $this->generatePackageTypeFilter($q, array(self::FILTER_NAME_PACKAGETYPE => $storePackageTypeIds));
        }

        return $this->fetchAll($q);
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
     * @todo improve processing speed
     */
    public function fetchMoreProjectsOfOtherUsr($project, $count = 8)
    {
        $sql = "
                SELECT count(1) AS `count`
                FROM stat_projects
                WHERE status = :current_status
                  AND member_id <> :current_member_id
                  AND project_category_id = :category_id
                  AND type_id = :project_type
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
        ))->setIntegrityCheck(false)
                  ->where('status = ?', self::PROJECT_ACTIVE)
                  ->where('member_id != ?', $project->member_id, 'INTEGER')
                  ->where('type_id = ?', 1)
                  ->where('project_category_id = ?', $project->project_category_id, 'INTEGER')
                  ->limit($count, $offset)
                  ->order('project_created_at DESC')
        ;

        $storeConfig = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;
        $storePackageTypeIds = null;
        if ($storeConfig) {
            $storePackageTypeIds = $storeConfig['package_type'];
        }

        if ($storePackageTypeIds) {
            $q = $this->generatePackageTypeFilter($q, array(self::FILTER_NAME_PACKAGETYPE => $storePackageTypeIds));
        }

        return $this->fetchAll($q);
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
     */
    public function fetchOverallPageViewsByMember($member_id)
    {
        $sql = "
                SELECT sum(stat.amount) AS page_views
                FROM project
                JOIN (SELECT project_id, count(project_id) AS amount FROM stat_page_views GROUP BY project_id) AS stat ON stat.project_id = project.project_id
                WHERE project.member_id = :member_id AND project.status = :project_status
                GROUP BY member_id
              ";

        $result = $this->_db->query($sql, array('member_id' => $member_id, 'project_status' => self::PROJECT_ACTIVE));
        if ($result->rowCount() > 0) {
            $row = $result->fetch();

            return $row['page_views'];
        } else {
            return 0;
        }
    }

    public function getStatsForNewProjects()
    {
        $sql = "
                SELECT
                    DATE_FORMAT(`time`, '%M %D') AS projectdate,
                    count(1) AS daycount
                FROM
                    activity_log
                WHERE
                    activity_type_id = 0
                GROUP BY DATE_FORMAT(`time`, '%Y%M%D')
                ORDER BY `time` DESC
                LIMIT 14
                ;";
        $database = Zend_Db_Table::getDefaultAdapter();
        $resultSet = $database->query($sql)->fetchAll();

        return $resultSet;
    }

    public function fetchProductsByCategory($idCategory, $limit = null)
    {
        $select = $this->select()->setIntegrityCheck(false)->from($this->_name)
                       ->where('project.project_category_id in (?)', $idCategory)
                       ->where('project.status = ?', self::PROJECT_ACTIVE)
                       ->where('project.type_id = ?', self::PROJECT_TYPE_STANDARD)->joinLeft(array(
                'pling_amount' => new Zend_Db_Expr('(SELECT
                project_id as plinged_project_id, SUM(amount) AS sumAmount, count(1) as countPlings
            FROM
                plings
            where status_id >= 2
            group by project_id
            order by sumAmount DESC)')
            ), 'pling_amount.plinged_project_id = project.project_id')->joinLeft('project_category',
                'project_category.project_category_id = project.project_category_id', array('cat_title' => 'title'))
                       ->order('pling_amount.sumAmount DESC')
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
        $storePackageTypeIds = (false === empty($store_config['package_type'])) ? $store_config['package_type'] : null;

        $cacheName = __FUNCTION__ . '_' . md5(serialize($idCategory) . $withSubCat . serialize($storePackageTypeIds));
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');

        if (false !== ($resultSet = $cache->load($cacheName))) {
            return (int)$resultSet[0]['count_active_projects'];
        }

        $select = $this->select()->setIntegrityCheck(false)
                                 ->from('stat_projects', array('count_active_projects' => 'COUNT(1)'))
                                 ->where('status = ? ', self::PROJECT_ACTIVE)
                                 ->where('type_id = ?', self::PROJECT_TYPE_STANDARD)
        ;

        $select =
            $this->generatePackageTypeFilter($select, array(self::FILTER_NAME_PACKAGETYPE => $storePackageTypeIds));

        if ($withSubCat) {
            $modelCategory = new Default_Model_DbTable_ProjectCategory();
            $subCategories = $modelCategory->fetchChildIds($idCategory);
            $inCategories = implode(',',array_unique(array_merge($idCategory, $subCategories)));
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
     */
    public function countActiveMembersForCategory($idCategory)
    {
        //Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . print_r(func_get_args(), true));

        /*
        if (is_null($idCategory) OR $idCategory == '' OR is_array($idCategory)) {
            return $this->countActiveProjects();
        }*/

        $cacheName = __FUNCTION__ . md5(serialize($idCategory));
        $cache = Zend_Registry::get('cache');

        $result = $cache->load($cacheName);

        if ($result) {
            return (int)$result['count_active_members'];
        }

        /**
         * $select = $this->select()->setIntegrityCheck(false)->from($this->_name,
         * array('count_active_projects' => 'COUNT(1)'))
         * ->group('member_id')
         * ->where('project.status = ? ', self::PROJECT_ACTIVE)
         * ->where('project.type_id = ?', self::PROJECT_TYPE_STANDARD);
         */
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

        //$select->where('project.project_category_id in (' . $sqlwhereSubCat . $sqlwhereCat . ')');
        $selectWhere = 'AND p.project_category_id in (' . $sqlwhereSubCat . $sqlwhereCat . ')';

        $sql = "SELECT count(1) AS count_active_members FROM (                    
                    SELECT count(1) AS count_active_projects FROM pling.project p
                    WHERE p.`status` = 100
                    AND p.type_id = 1
                    {$selectWhere} GROUP BY p.member_id
                ) AS A;";

        //$resultSet = $this->fetchRow($select);
        $result = $this->_db->fetchRow($sql);
        $cache->save($result, $cacheName);

        return (int)$result['count_active_members'];
    }

    public function fetchTotalProjectsCount()
    {
        $sql =
            "SELECT count(1) AS total_project_count FROM project WHERE project.status = :status AND project.type_id = :ptype";

        $result =
            $this->_db->fetchRow($sql, array('status' => self::PROJECT_ACTIVE, 'ptype' => self::PROJECT_TYPE_STANDARD));

        return $result['total_project_count'];
    }

    public function setAllProjectsForMemberDeleted($member_id)
    {
        $sql = "SELECT project_id FROM project WHERE member_id = :memberId AND type_id = :typeId AND status > :project_status";
        $projectForDelete = $this->_db->fetchAll($sql, array('memberId' => $member_id, 'typeId' => self::PROJECT_TYPE_STANDARD, 'project_status' => self::PROJECT_DELETED));
        foreach ($projectForDelete as $item) {
            $this->setDeleted($item['project_id']);
        }
        // set personal page deleted
        $sql = "UPDATE project SET `status` = :statusCode, deleted_at = NOW() WHERE member_id = :memberId AND type_id = :typeId";
        $this->_db->query($sql, array('statusCode' => self::PROJECT_ACTIVE, 'memberId' => $member_id, 'typeId' => self::PROJECT_TYPE_PERSONAL))->execute();
    }

    public function setAllProjectsForMemberActivated($member_id)
    {
        $sql = "SELECT project_id FROM project WHERE member_id = :memberId AND type_id = :typeId";
        $projectForDelete = $this->_db->fetchAll($sql, array('memberId' => $member_id, 'typeId' => self::PROJECT_TYPE_STANDARD));
        foreach ($projectForDelete as $item) {
            $this->setActive($item['project_id']);
        }
        // set personal page active
        $sql = "UPDATE project SET `status` = :statusCode, deleted_at = null WHERE member_id = :memberId AND type_id = :typeId";
        $this->_db->query($sql, array('statusCode' => self::PROJECT_ACTIVE, 'memberId' => $member_id, 'typeId' => self::PROJECT_TYPE_PERSONAL))->execute();
    }

    /**
     * @param array    $inputFilterParams
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     */
    public function fetchProjectsByFilter($inputFilterParams, $limit = null, $offset = null)
    {
        $cacheName = __FUNCTION__ . '_' . md5(serialize($inputFilterParams) . (string)$limit . (string)$offset);
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');

        if (false === ($returnValue = $cache->load($cacheName))) {
            $statement = $this->generateStatement($inputFilterParams, $limit, $offset);
            //Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . $statement->__toString());
            /** @var Zend_Db_Table_Rowset $fetchedElements */
            $fetchedElements = $this->fetchAll($statement);

            $statement->reset('limitcount')->reset('limitoffset');
            $statement->reset('columns')->columns(array('count' => new Zend_Db_Expr('count(*)')));
            //Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . $statement->__toString());
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
     * @throws Zend_Exception
     */
    protected function generateStatement($inputFilterParams, $limit = null, $offset = null)
    {
        $statement = $this->generateBaseStatement();
        $statement = $this->generateCategoryFilter($statement, $inputFilterParams);
        $statement = $this->generateOrderFilter($statement, $inputFilterParams);
        $statement = $this->generatePackageTypeFilter($statement, $inputFilterParams);
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
        $statement->where('project.status = ?', self::PROJECT_ACTIVE)
                  ->where('project.type_id=?', self::PROJECT_TYPE_STANDARD)
        ;

        return $statement;
    }

    /**
     * @param Zend_Db_Select $statement
     * @param array          $filterArrayValue
     *
     * @return Zend_Db_Select
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
        $values['created_at'] =
            (!array_key_exists('created_at', $values)) ? new Zend_Db_Expr('NOW()') : $values['created_at'];
        $values['start_date'] =
            (!array_key_exists('start_date', $values)) ? new Zend_Db_Expr('NULL') : $values['start_date'];
        $values['creator_id'] = (!array_key_exists('creator_id', $values)) ? $member_id : $values['creator_id'];

        if ($username == 'pling editor') {
            $values['claimable'] =
                (!array_key_exists('claimable', $values)) ? self::PROJECT_CLAIMABLE : $values['claimable'];
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
     * @param int|array $storeCategories
     * @param boolean   $withoutUpdates
     *
     * @return array
     * @todo: update the sql. It is deprecated since we store only one cat_id for the product.
     */
    public function fetchProductsForCategories($storeCategories, $withoutUpdates = true)
    {
        //        Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . print_r(func_get_args(), true));

        if (empty($storeCategories)) {
            return array();
        }

        $storeConfig = Zend_Registry::isRegistered('store_config') ? Zend_Registry::get('store_config') : null;
        $storePackageTypeIds = null;
        if ($storeConfig) {
            $storePackageTypeIds = $storeConfig['package_type'];
        }

        $sql = '
                SELECT
                  p.*,
                  p.changed_at AS project_changed_at,
                  pc.title AS cat_title,
                  m.username,
                  m.avatar,
                  m.profile_image_url,
                  m.roleId,
                  m.mail,
                  m.paypal_mail,
                  m.dwolla_id,
               	 laplace_score(p.count_likes,p.count_dislikes) AS laplace_score
                FROM project AS p
                  JOIN member AS m ON p.member_id = m.member_id AND m.is_active = 1 AND m.is_deleted = 0
                  JOIN project_category AS pc ON p.project_category_id = pc.project_category_id';

        if ($storePackageTypeIds) {
            $sql .= ' JOIN (SELECT DISTINCT project_id FROM project_package_type WHERE package_type_id in ('
                . $storePackageTypeIds . ')) package_type  ON p.project_id = package_type.project_id';
        }

        $sql .= ' WHERE p.project_category_id IN (' . implode(',', $storeCategories) . ') AND p.status >= 100';

        if ($withoutUpdates) {
            $sql .= ' AND p.type_id = 1';
        }
        //        $this->_db->getProfiler()->setEnabled(true);
        $result = $this->_db->query($sql)->fetchAll();
        //        $dummy = $this->_db->getProfiler()->getLastQueryProfile()->getQuery();
        //        $this->_db->getProfiler()->setEnabled(true);
        return $result;
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

    public function fetchProductDataFromMV($project_id)
    {
        $sql = "SELECT * FROM stat_projects WHERE project_id = :project_id";
        $resultSet = $this->_db->query($sql, array('project_id' => $project_id))->fetch();
        if (false === $resultSet) {
            return $this->generateRowClass(array());
        }

        return $this->generateRowClass($resultSet);
    }

    
    /**
     * @return array
     */
    public function fetchGhnsExcludedProjects() {
        $sql = "
        	select p.project_id, p.title, l.member_id as exclude_member_id, l.time as exclude_time, m.username as exclude_member_name from project p
                join activity_log l on l.project_id = p.project_id and l.activity_type_id = 314
                inner join member m on m.member_id = l.member_id
                where p.ghns_excluded = 1

        ";

        $list = $this->_db->fetchAll($sql);
        return $list;
    }
    
}