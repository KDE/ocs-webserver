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
    const FILTER_NAME_MEMBER = 'member';
    const FILTER_NAME_ORDER = 'order';
    const FILTER_NAME_LOCATION = 'location';

    const ITEM_TYPE_DUMMY = 0;
    const ITEM_TYPE_PRODUCT = 1;
    const ITEM_TYPE_UPDATE = 2;


    /**
     * @param int $status
     * @param int $id
     * @throws Exception
     */
    public function setStatus($status, $id)
    {
        if (false === in_array($status, $this->_allowedStatusTypes)) {
            throw new Exception('Wrong value for project status.');
        }
        $updateValues = array(
            'status' => $status,
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
            'changed_at' => new Zend_Db_Expr('Now()')
        );

        $this->update($updateValues, $this->_db->quoteInto('project_id=?', $id, 'INTEGER'));
    }

    public function resetClaimedByMember($id)
    {
        $updateValues = array(
            'claimed_by_member' => new Zend_Db_Expr('NULL'),
            'changed_at' => new Zend_Db_Expr('Now()')
        );

        $this->update($updateValues, $this->_db->quoteInto('project_id=?', $id, 'INTEGER'));
    }

    /**
     * @param int $id
     */
    public function transferClaimToMember($id)
    {
        $updateValues = array(
            'member_id' => new Zend_Db_Expr('claimed_by_member'),
            'claimable' => new Zend_Db_Expr('NULL'),
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
            'status' => self::PROJECT_ACTIVE,
            'deleted_at' => new Zend_Db_Expr('Now()')
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
            'status' => self::PROJECT_ACTIVE,
            'changed_at' => new Zend_Db_Expr('Now()')
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
        $updateValues = array(
            'status' => self::PROJECT_INACTIVE,
            'deleted_at' => new Zend_Db_Expr('Now()')
        );

        $this->update($updateValues, $this->_db->quoteInto('project_id=?', $id, 'INTEGER'));

        $this->setInActiveForUpdates($id);
        $this->setDeletedForComments($id);
    }

    /**
     * @param int $id
     */
    protected function setInActiveForUpdates($id)
    {
        $updateValues = array(
            'status' => self::PROJECT_INACTIVE,
            'changed_at' => new Zend_Db_Expr('Now()')
        );

        $this->update($updateValues, $this->_db->quoteInto('pid=?', $id, 'INTEGER'));
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
        $updateValues = array(
            'status' => self::PROJECT_DELETED,
            'deleted_at' => new Zend_Db_Expr('Now()')
        );

        $this->update($updateValues, $this->_db->quoteInto('project_id=?', $id, 'INTEGER'));

        $this->setDeletedForUpdates($id);
        $this->setDeletedForComments($id);
    }

    /**
     * @param int $id
     */
    protected function setDeletedForUpdates($id)
    {
        $updateValues = array(
            'status' => self::PROJECT_DELETED,
            'deleted_at' => new Zend_Db_Expr('Now()')
        );

        $this->update($updateValues, $this->_db->quoteInto('pid=?', $id, 'INTEGER'));
    }

    /**
     * @param null $order
     * @param null $count
     * @param null $offset
     * @return array
     */
    public function fetchAllActive($order = null, $count = null, $offset = null)
    {
        $q = $this->select()
            ->where('status = ?', self::PROJECT_ACTIVE)
            ->limit($count, $offset);

        if (!empty($order)) {
            $q->order($order);
        }

        return $q->query()->fetchAll();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function fetchActive($id)
    {
        $q = $this->select()
            ->where('status = ?', self::PROJECT_ACTIVE)
            ->where('project_id = ?', $id);

        return $q->query()->fetch();
    }

    /**
     * @param array $arg
     * @return array|null
     */
    public function fetchActiveByArray(array $arg)
    {
        if (!is_array($arg)) {
            return null;
        }

        $q = $this->select()
            ->where('status = ?', self::PROJECT_ACTIVE)
            ->where('project_id IN (?)', $arg);

        return $q->query()->fetchAll();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function fetchActiveBySourcePk($id)
    {
        $q = $this->select()
            ->where('status = ?', self::PROJECT_ACTIVE)
            ->where('source_pk = ?', (int)$id)
            ->where('source_type = "project"');

        return $q->query()->fetch();
    }

    /**
     * @param int $member_id
     * @param bool $onlyActiveProjects
     * @return mixed
     */
    public function countAllProjectsForMember($member_id, $onlyActiveProjects = false)
    {
        $q = $this->select()->from($this, array('countAll' => new Zend_Db_Expr('count(*)')))
            ->setIntegrityCheck(false)
            ->where('project.status >= ?', ($onlyActiveProjects ? self::PROJECT_ACTIVE : self::PROJECT_INACTIVE))
            ->where('project.member_id = ?', $member_id, 'INTEGER')
            ->where('project.type_id = ?', self::PROJECT_TYPE_STANDARD);
        $resultSet = $q->query()->fetchAll();
        return $resultSet[0]['countAll'];
    }

    /**
     * By default it will show all projects for a member included the unpublished elements.
     *
     * @param int $member_id
     * @param int|null $limit
     * @param int|null $offset
     * @param bool $onlyActiveProjects
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchAllProjectsForMember($member_id, $limit = null, $offset = null, $onlyActiveProjects = false)
    {
        $q = $this->select()->from($this, array(
            '*',
            'project_validated' => 'project.validated',
            'project_uuid' => 'project.uuid',
            'project_status' => 'project.status',
            'project_created_at' => 'project.created_at',
            'project_changed_at' => 'project.changed_at',
            'member_type' => 'member.type',
            'project_member_id' => 'member_id',
            'laplace_score' => new Zend_Db_Expr('(round(((count_likes + 6) / ((count_likes + count_dislikes) + 12)),2) * 100)'),
            'catTitle' => new Zend_Db_Expr('(SELECT title FROM project_category WHERE project_category_id = project.project_category_id)')
        ))
            ->setIntegrityCheck(false)
            ->join('member', 'project.member_id = member.member_id', array('username'))
            ->where('project.status >= ?', ($onlyActiveProjects ? self::PROJECT_ACTIVE : self::PROJECT_INACTIVE))
            ->where('project.member_id = ?', $member_id, 'INTEGER')
            ->where('project.type_id = ?', self::PROJECT_TYPE_STANDARD)
            ->order('project_changed_at DESC');
        if (isset($limit)) {
            $q->limit($limit, $offset);
        }
        return $this->generateRowSet($q->query()->fetchAll());
    }

    /**
     * @param array $data
     * @return Zend_Db_Table_Rowset_Abstract
     */
    protected function generateRowSet($data)
    {
        $classRowSet = $this->getRowsetClass();

        return new $classRowSet(array(
            'table' => $this,
            'rowClass' => $this->getRowClass(),
            'stored' => true,
            'data' => $data
        ));
    }

    /**
     * @param int $project_id
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
               	 (round(((p.count_likes + 6) / ((p.count_likes + p.count_dislikes) + 12)),2) * 100) as laplace_score,
               	 sp.amount_received,
               	 sp.count_plings,
               	 sp.count_plingers,
               	 sp.latest_pling
                FROM project AS p
                  JOIN member AS m ON p.member_id = m.member_id AND m.is_active = 1 AND m.is_deleted = 0
                  JOIN project_category AS pc ON p.project_category_id = pc.project_category_id
                  LEFT JOIN stat_plings as sp ON p.project_id = sp.project_id
                WHERE 
                  p.project_id = :projectId
                  AND p.status >= :projectStatus AND p.type_id = :typeId
        ';

        $result = $this->_db->fetchRow($sql, array(
            'projectId' => $project_id,
            'projectStatus' => self::PROJECT_INACTIVE,
            'typeId' => self::PROJECT_TYPE_STANDARD
        ));

        if ($result) {
            return $this->generateRowClass($result);
        } else {
            return null;
        }
    }

    /**
     * @param array $data
     * @return Zend_Db_Table_Row_Abstract
     */
    protected function generateRowClass($data)
    {
        /** @var Zend_Db_Table_Row $classRow */
        $classRow = $this->getRowClass();

        return new $classRow(array('table' => $this, 'stored' => true, 'data' => $data));
    }

    /**
     * @param int $returnAmount
     * @param int $fetchLimit
     * @return null|Zend_Db_Table_Row_Abstract
     * @throws Zend_Exception
     */
    public function fetchRandomProjects($returnAmount = 5, $fetchLimit = 50)
    {
        return $this->fetchRandomProjectsForCategories(null, $returnAmount, $fetchLimit);
    }

    /**
     * @param int $returnAmount
     * @param int $fetchLimit
     * @return null|Zend_Db_Table_Row_Abstract
     * @throws Zend_Exception
     */
    public function fetchRandomProjectsForCategories($catId = null, $returnAmount = 5, $fetchLimit = 50)
    {
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        $cacheName = __FUNCTION__ . '_' . md5((string)$returnAmount . (string)$fetchLimit);
        if (!($products = $cache->load($cacheName))) {
            $projectSel = $this->select()->setIntegrityCheck(false)
                ->from($this, array(
                    '*',
                    'project_validated' => 'project.validated',
                    'project_uuid' => 'project.uuid',
                    'project_status' => 'project.status',
                    'project_created_at' => 'project.created_at',
                    'member_type' => 'member.type',
                    'project_member_id' => 'member_id',
                    'collectPlings' => new Zend_Db_Expr('(SELECT COUNT(1) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id=2)'),
                    'collectPlingsAll' => new Zend_Db_Expr('(SELECT COUNT(1) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id in (2,3,4))'),
                    'sumAmount' => new Zend_Db_Expr('(SELECT SUM(amount) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id=2)'),
                    'sumAmountDollar' => new Zend_Db_Expr('(SELECT ROUND(SUM(amount), 0) FROM plings WHERE plings.project_id = project.project_id AND plings.status_id = 2)'),
                    'sumAmountCent' => new Zend_Db_Expr('(SELECT ROUND(MOD(SUM(amount), TRUNCATE(SUM(amount), 0)), 2) * 100 FROM plings WHERE plings.project_id = project.project_id AND plings.status_id = 2)'),
                    'plingers' => new Zend_Db_Expr('(SELECT COUNT(DISTINCT plings.member_id) FROM plings WHERE plings.status_id >= 2 AND plings.project_id = project.project_id)'),
                    'catTitle' => new Zend_Db_Expr('(SELECT title FROM project_category WHERE project_category_id = project.project_category_id)')
                ))
                ->join('member', 'project.member_id = member.member_id')
                ->where('project.status>?', self::PROJECT_INACTIVE)
                ->where('project.pid is null')
                ->where('project.type_id=?', self::PROJECT_TYPE_STANDARD)
                ->where('member.is_deleted = 0')
                ->where('member.is_active = 1')
                ->limit($fetchLimit);

            if (isset($catId)) {
                $projectSel->where('project.project_category_id in (' . $catId . ')');
            }

            $products = $this->fetchAll($projectSel)->toArray();

            $cache->save($products, $cacheName);
        }
        return $this->_arrayRandom($products, $returnAmount);
    }

    protected function _arrayRandom($arr, $count = 1)
    {
        shuffle($arr);
        return array_slice($arr, 0, $count);
    }

    /**
     * @param $project_id
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchProjectUpdates($project_id)
    {
        $projectSel = $this->select()->setIntegrityCheck(false)
            ->from($this->_name)
            ->join('member', 'project.member_id = member.member_id', array('*'))
            ->where('project.pid=?', $project_id, 'INTEGER')
            ->where('project.status>?', self::PROJECT_INACTIVE)
            ->where('project.type_id=?', self::PROJECT_TYPE_UPDATE)
            ->order('RAND()');

        return $this->fetchAll($projectSel);
    }

    /**
     * @param $project_id
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchAllProjectUpdates($project_id)
    {

        $projectSel = $this->select()->setIntegrityCheck(false)
            ->from($this->_name)
            ->where('project.pid=?', $project_id, 'INTEGER')
            ->where('project.status>?', self::PROJECT_INACTIVE)
            ->where('project.type_id=?', self::PROJECT_TYPE_UPDATE);

        return $this->fetchAll($projectSel);

    }

    /**
     * @param array $filter
     * @param null|string $forDate
     * @param null|int $limit
     * @return array
     * @throws Exception
     * @deprecated
     */
    public function fetchElementsByFilterRanking($filter, $forDate = null, $limit = null)
    {
        if (false == is_null($forDate)) {
            $filterDate = new DateTime($forDate);
        } else {
            $filterDate = new DateTime();
        }

        $cacheName = __FUNCTION__ . md5('filter_ranking' . serialize($filter) . $forDate . $limit);
        $cache = Zend_Registry::get('cache');
        if ($fetchedElements = $cache->load($cacheName)) {
            return $fetchedElements;
        } else {
            $statement = $this->generateSqlForFilter($filter, $filterDate);
            $statement->limit($limit);


            $sql = "
                SELECT @rn:=@rn+1 AS rank, t1.*
                FROM ({$statement->__toString()}) t1,
                (SELECT @rn:=0) t2;
                ";

            $db = $this->_db;
            $resultSet = $db->query($sql)->fetchAll();
            $cache->save($resultSet, $cacheName);
            return $resultSet;
        }
    }

    /**
     * @param array $filter
     * @param DateTime $filterDate
     * @return void|Zend_Db_Select
     * @throws Exception
     */
    protected function generateSqlForFilter($filter, DateTime $filterDate)
    {
        $statement = $this->getBaseStatement();

        $statement = $this->getProjectIdNotInFilter($statement, $filter);
        $statement = $this->getRankingFilter($statement, $filter, $filterDate);
        $statement = $this->getCategoryFilter($statement, $filter);
        $statement = $this->getOrderFilter($statement, $filter);
//        $statement = $this->getLocationFilter($statement, $filter, $filterDate);
        return $statement;
    }

    /**
     * @return Zend_Db_Select
     */
    protected function getBaseStatement()
    {
        $statement = $this->select()->setIntegrityCheck(false);
        $statement->from('project');
        $statement->join('member', 'project.member_id=member.member_id',
            array(
                'username' => 'username',
                'profile_image_url',
                'city',
                'country',
                'member_created_at' => 'created_at',
                'paypal_mail'
            ));
        $statement->join('project_category', 'project.project_category_id=project_category.project_category_id',
            array('catTitle' => 'title'));
        $statement->joinLeft('stat_daily',
            'project.project_id=stat_daily.project_id',
            array(
                'count_views' => new Zend_Db_Expr('SUM(stat_daily.count_views)'),
                'count_plings' => new Zend_Db_Expr('SUM(stat_daily.count_plings)'),
                'count_updates' => new Zend_Db_Expr('SUM(stat_daily.count_updates)'),
                'stat_count_comments' => new Zend_Db_Expr('SUM(stat_daily.count_comments)'),
                'count_followers' => new Zend_Db_Expr('SUM(stat_daily.count_followers)'),
                'count_supporters' => new Zend_Db_Expr('SUM(stat_daily.count_supporters)'),
                'count_amount' => new Zend_Db_Expr('SUM(stat_daily.count_money)'),
                self::FILTER_NAME_RANKING => new Zend_Db_Expr('SUM(stat_daily.ranking_value)')
            ));
        $statement->where('project.status>?', self::PROJECT_INACTIVE)
            ->where('project.type_id=?', self::PROJECT_TYPE_STANDARD)
            ->where('project.pid is null');
        $statement->group(array('project_id', 'project_category_id', 'project_type_id'));
        return $statement;
    }

    /**
     * @param Zend_Db_Select $statement
     * @param array $filterArrayValue
     * @return Zend_Db_Select
     */
    protected function getProjectIdNotInFilter(Zend_Db_Select $statement, $filterArrayValue)
    {
        if (!isset($filterArrayValue[self::FILTER_NAME_PROJECT_ID_NOT_IN])) {
            return $statement;
        } else {
            $filterValue = $filterArrayValue[self::FILTER_NAME_PROJECT_ID_NOT_IN];
        }

        if (!empty($filterValue)) {
            $statement->where('project.project_id != ?', $filterValue);
        }

        return $statement;
    }

    /**
     * @param Zend_Db_Select $statement
     * @param array $filterArrayValue
     * @param DateTime $filterDate
     * @return Zend_Db_Select
     * @throws Exception
     */
    protected function getRankingFilter(Zend_Db_Select $statement, $filterArrayValue, DateTime $filterDate)
    {
        if (!isset($filterArrayValue[self::FILTER_NAME_RANKING])) {
            $filterValue = 'all';
        } else {
            $filterValue = $filterArrayValue[self::FILTER_NAME_RANKING];
        }
        switch ($filterValue) {
            case 'all':

                break;
            case 'week':
                $statement->where('year_week = ?', $filterDate->format('YW'));

                break;
            case 'month':
                $statement->where('stat_daily.year = ?', $filterDate->format('Y'))
                    ->where('stat_daily.month = ?', $filterDate->format('m'));

                break;
            case 'hour':

                break;
            case 'today':
                $statement->where('stat_daily.year = ?', $filterDate->format('Y'))
                    ->where('stat_daily.month = ?', $filterDate->format('m'))
                    ->where('stat_daily.day = ?', $filterDate->format('d'));

                break;
            case 'new':
                //$statement->where('project.created_at BETWEEN NOW() AND DATE_SUB(NOW(), INTERVAL 7 DAY)');
                $statement->order('project.created_at DESC');

                break;
            default:

                break;
        }

        return $statement;
    }

    /**
     * @param Zend_Db_Select $statement
     * @param array $filterArrayValue
     * @return Zend_Db_Select
     */
    protected function getCategoryFilter(Zend_Db_Select $statement, $filterArrayValue)
    {
        if (false == isset($filterArrayValue[self::FILTER_NAME_CATEGORY])) {
            return $statement;
        }
        if (false == empty($filterArrayValue['filter'])) {
            $filterValue = $filterArrayValue['filter'];
        } elseif (false == empty($filterArrayValue[self::FILTER_NAME_CATEGORY])) {
            $filterValue = $filterArrayValue[self::FILTER_NAME_CATEGORY];
        }

        if (!empty($filterValue)) {
            $statement->where('(
                    (project_category.project_category_id = ?)
                    OR
                    (project.project_id IN (SELECT project_id FROM project_subcategory WHERE project_sub_category_id = ?))
                    )', $filterValue);
        }

        return $statement;
    }

    /**
     * @param Zend_Db_Select $statement
     * @param array $filterArrayValue
     * @return Zend_Db_Select
     */
    protected function getOrderFilter(Zend_Db_Select $statement, $filterArrayValue)
    {
        if (!isset($filterArrayValue[self::FILTER_NAME_ORDER])) {
            $filterValue = '';
        } else {
            $filterValue = $filterArrayValue[self::FILTER_NAME_ORDER];
        }
        switch ($filterValue) {
            case 'count_followers':
                $statement->order(new Zend_Db_Expr('SUM(stat_daily.count_followers)') . ' DESC');
                break;
            case 'count_comments':
                $statement->order(new Zend_Db_Expr('SUM(stat_daily.count_comments)') . ' DESC');
                break;
            case 'count_updates':
                $statement->order(new Zend_Db_Expr('SUM(stat_daily.count_updates)') . ' DESC');
                break;
            case 'count_plings':
                $statement->order(new Zend_Db_Expr('SUM(stat_daily.count_plings)') . ' DESC');
                break;
            case 'count_views':
                $statement->order(new Zend_Db_Expr('SUM(stat_daily.count_views)') . ' DESC');
                break;
            case 'count_supporter':
                $statement->order(new Zend_Db_Expr('SUM(stat_daily.count_supporter)') . ' DESC');
                break;
            case 'alpha':
                $statement->order('project.title');
                break;
            case 'latest':
                $statement->order('project.created_at DESC');
                break;
            case 'pop':
                $statement->order(new Zend_Db_Expr('SUM(stat_daily.count_money)') . ' DESC , ' . new Zend_Db_Expr('SUM(stat_daily.count_views)') . ' DESC , project.created_at');
                break;
            default:
//                $statement->order(new Zend_Db_Expr('SUM(stat_daily.count_plings)') . ' DESC , ' . new Zend_Db_Expr('SUM(stat_daily.count_views)') . ' DESC , project.created_at');
                $statement->order(new Zend_Db_Expr('SUM(stat_daily.ranking_value) DESC, SUM(stat_daily.count_plings) DESC , project.created_at'));
        }

        return $statement;
    }

    /**
     * @param $project
     * @param int $count
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchSimilarProjects($project, $count = 10)
    {
        return $this->generateRowSet(
            $this->fetchElementsByFilter(array(
                'category' => $project->project_category_id,
                'project_id_not_in' => $project->project_id,
                'ranking' => 'all'
            ), null, $count)
        );
    }

    /**
     * @param array $filter
     * @param null|string $forDate
     * @param null|int $limit
     * @return array
     * @throws Exception
     */
    public function fetchElementsByFilter($filter, $forDate = null, $limit = null)
    {
        $cacheName = __FUNCTION__ . md5(serialize($filter) . $forDate . $limit);
        $cache = Zend_Registry::get('cache');
        if ($fetchedElements = $cache->load($cacheName)) {
            return $fetchedElements;
        } else {
            if (false == is_null($forDate)) {
                $filterDate = new DateTime($forDate);
            } else {
                $filterDate = new DateTime();
            }

            $statement = $this->generateSqlForFilter($filter, $filterDate);
            $statement->limit($limit);

            $db = $this->_db;
            $resultSet = $db->query($statement)->fetchAll();

            $cache->save($resultSet, $cacheName);

            return $resultSet;
        }
    }


    /**
     * /**
     * @param $member
     * @param $project
     * @param int $count
     * @return Zend_Db_Table_Rowset_Abstract
    public function fetchMoreProjects($project, $count = 6)
     * {
     * $q = $this->select()->from($this, array(
     * '*',
     * 'project_validated' => 'project.validated',
     * 'project_uuid' => 'project.uuid',
     * 'project_status' => 'project.status',
     * 'project_created_at' => 'project.created_at',
     * 'member_type' => 'member.type',
     * 'project_member_id' => 'member_id',
     * 'collectPlings' => new Zend_Db_Expr('(SELECT COUNT(1) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id=2)'),
     * 'collectPlingsAll' => new Zend_Db_Expr('(SELECT COUNT(1) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id in (2,3,4))'),
     * 'sumAmount' => new Zend_Db_Expr('(SELECT SUM(amount) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id=2)'),
     * 'sumAmountDollar' => new Zend_Db_Expr('(SELECT ROUND(SUM(amount),0) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id=2)'),
     * 'sumAmountCent' => new Zend_Db_Expr('(SELECT round(mod(sum(amount), truncate(sum(amount),0)),2)*100 FROM plings WHERE plings.project_id=project.project_id AND plings.status_id=2)'),
     * 'plingers' => new Zend_Db_Expr('(select count(distinct plings.member_id) FROM plings WHERE plings.status_id >= 2 and plings.project_id = project.project_id)'),
     * 'catTitle' => new Zend_Db_Expr('(SELECT title FROM project_category WHERE project_category_id = project.project_category_id)')
     * ))->setIntegrityCheck(false)
     * ->join('member', 'project.member_id = member.member_id', array('username'))
     * ->where('project.status = ?', self::PROJECT_ACTIVE)
     * ->where('project.member_id = ?', $project->member_id, 'INTEGER')
     * ->where('project.project_id != ?', $project->project_id, 'INTEGER')
     * ->where('project.type_id = ?', 1)
     * ->where('project.project_category_id = ?', $project->project_category_id, 'INTEGER')
     * ->limit($count)
     * ->order('sumAmount DESC');
     * return $this->generateRowSet($q->query()->fetchAll());
     * }
     **/

    /**
     * @param Zend_Db_Table_Row $project
     * @param int $count
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchMoreProjects($project, $count = 6)
    {
        $q = $this->select()->from($this, array(
            'project_id',
            'image_small',
            'title',
            'catTitle' => new Zend_Db_Expr('(SELECT title FROM project_category WHERE project_category_id = project.project_category_id)')
        ))->setIntegrityCheck(false)
            ->where('project.status = ?', self::PROJECT_ACTIVE)
            ->where('project.member_id = ?', $project->member_id, 'INTEGER')
            ->where('project.project_id != ?', $project->project_id, 'INTEGER')
            ->where('project.type_id = ?', 1)
            ->where('project.project_category_id = ?', $project->project_category_id, 'INTEGER')
            ->limit($count)
            ->order('project.created_at DESC');
        return $this->generateRowSet($q->query()->fetchAll());
    }

    /**
     * @param $project
     * @param int $count
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchMoreProjectsOfOtherUsr_($project, $count = 6)
    {
        $q = $this->select()->from($this, array(
            'project_id',
            'image_small',
            'title',
            'catTitle' => new Zend_Db_Expr('(SELECT title FROM project_category WHERE project_category_id = project.project_category_id)')
        ))->setIntegrityCheck(false)
            ->where('project.status = ?', self::PROJECT_ACTIVE)
            ->where('project.member_id != ?', $project->member_id, 'INTEGER')
            ->where('project.type_id = ?', 1)
            ->where('project.project_category_id = ?', $project->project_category_id, 'INTEGER')
            ->limit($count)
            ->order('project.created_at DESC');;
        return $this->generateRowSet($q->query()->fetchAll());
    }


    /**
     * /**
     * @param $member
     * @param $project
     * @param int $count
     * @return Zend_Db_Table_Rowset_Abstract

    public function fetchMoreProjectsOfOtherUsr($project, $count = 6)
     * {
     * $q = $this->select()->from($this, array(
     * '*',
     * 'project_validated' => 'project.validated',
     * 'project_uuid' => 'project.uuid',
     * 'project_status' => 'project.status',
     * 'project_created_at' => 'project.created_at',
     * 'member_type' => 'member.type',
     * 'project_member_id' => 'member_id',
     * 'collectPlings' => new Zend_Db_Expr('(SELECT COUNT(1) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id=2)'),
     * 'collectPlingsAll' => new Zend_Db_Expr('(SELECT COUNT(1) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id in (2,3,4))'),
     * 'sumAmount' => new Zend_Db_Expr('(SELECT SUM(amount) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id=2)'),
     * 'sumAmountDollar' => new Zend_Db_Expr('(SELECT ROUND(SUM(amount),0) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id=2)'),
     * 'sumAmountCent' => new Zend_Db_Expr('(SELECT round(mod(sum(amount), truncate(sum(amount),0)),2)*100 FROM plings WHERE plings.project_id=project.project_id AND plings.status_id=2)'),
     * 'plingers' => new Zend_Db_Expr('(select count(distinct plings.member_id) FROM plings WHERE plings.status_id >= 2 and plings.project_id = project.project_id)'),
     * 'catTitle' => new Zend_Db_Expr('(SELECT title FROM project_category WHERE project_category_id = project.project_category_id)')
     * ))->setIntegrityCheck(false)
     * ->join('member', 'project.member_id = member.member_id', array('username'))
     * ->where('project.status = ?', self::PROJECT_ACTIVE)
     * ->where('project.member_id != ?', $project->member_id, 'INTEGER')
     * ->where('project.type_id = ?', 1)
     * ->where('project.project_category_id = ?', $project->project_category_id, 'INTEGER')
     * ->limit($count)
     * ->order('sumAmount DESC');
     * //->order('RAND()')
     * ;
     *
     *
     * return $this->generateRowSet($q->query()->fetchAll());
     * }
     **/

    /**
     * @param $project
     * @param int $count
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchMoreProjectsOfOtherUsr($project, $count = 8)
    {
        $sql = 'select count(1) count from project where project.status = ' . self::PROJECT_ACTIVE . ' and member_id  <> ' . $project->member_id . ' and project_category_id=' . $project->project_category_id . ' and type_id=1';
        $result = $this->_db->fetchRow($sql);

        $cnt = $result['count'];

        if ($cnt > $count) {
            $offset = rand(0, $cnt - $count);
        } else {
            $offset = 0;
        }

        $q = $this->select()->from($this, array(
            'project_id',
            'image_small',
            'title',
            'catTitle' => new Zend_Db_Expr('(SELECT title FROM project_category WHERE project_category_id = project.project_category_id)')
        ))->setIntegrityCheck(false)
            ->where('project.status = ?', self::PROJECT_ACTIVE)
            ->where('project.member_id != ?', $project->member_id, 'INTEGER')
            ->where('project.type_id = ?', 1)
            ->where('project.project_category_id = ?', $project->project_category_id, 'INTEGER')
            ->limit($count, $offset)
            ->order('project.created_at DESC');;
        return $this->generateRowSet($q->query()->fetchAll());
    }

    /**
     * @param int $member_id
     * @return Zend_Db_Table_Rowset_Abstract
     * @deprecated
     */
    public function fetchProjectInfoForMemberId($member_id)
    {
        $sel = $this->select()->setIntegrityCheck(false)->from($this, array(
            '*',
            'project_member_id' => 'member_id',
            'collectPlings' => '(SELECT COUNT(1) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id=2)',
            'collectPlingsAll' => '(SELECT COUNT(1) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id in (2,3,4))',
            'plingers' => '(select count(distinct plings.member_id) FROM plings WHERE plings.status_id >= 2 and plings.project_id = project.project_id)',
            'catTitle' => '(SELECT title FROM project_category WHERE project_category_id = project.project_category_id)'
        ))
            ->join('member', 'project.member_id=member.member_id')
            ->where('project.member_id=?', $member_id, 'INTEGER')
            ->where('project.type_id=?', 1)
            ->where('project.status>=?', self::PROJECT_INACTIVE)
            ->where('member.type=?', 0);

        return $this->fetchAll($sel);

    }

    /**
     * @param int $member_id
     * @return Zend_Db_Table_Rowset_Abstract
     * @deprecated
     */
    public function fetchProjectInfoForGroup($member_id)
    {
        $sel = $this->select()->setIntegrityCheck(false)->from($this, array(
            '*',
            'project_member_id' => 'member_id',
            'creator_name' => 'group_member.username',
            'owner_name' => 'member.username',
            'collectPlings' => '(SELECT COUNT(1) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id=2)',
            'collectPlingsAll' => '(SELECT COUNT(1) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id in (2,3,4))',
            'backers' => '(SELECT count(*) FROM plings WHERE plings.project_id=project.project_id AND plings.status_id=2)',
            'plingers' => '(select count(distinct plings.member_id) FROM plings WHERE plings.status_id >= 2 and plings.project_id = project.project_id)',
            'catTitle' => '(SELECT title FROM project_category WHERE project_category_id = project.project_category_id)'
        ))
            ->join('member', 'project.member_id=member.member_id')
            ->join('member as group_member', 'project.creator_id=group_member.member_id')
            ->where('project.member_id=?', $member_id, 'INTEGER')
            ->where(' project.type_id=?', 1)
            ->where('project.status>?', self::PROJECT_INACTIVE);

        return $this->fetchAll($sel);
    }

    /**
     * @param int $project_id
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchProjectSupporter($project_id)
    {
        $plingTable = new Default_Model_DbTable_Plings();
        return $plingTable->getSupporterForProjectId($project_id);
    }

    /**
     * @param int $project_id
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchProjectSupporterWithPlings($project_id)
    {
        $plingTable = new Default_Model_DbTable_Plings();
        return $plingTable->getSupporterWithPlingsForProjectId($project_id);
    }

    /**
     * @return string
     * @deprecated
     */
    public function fetchRandomProjectId()
    {
        $sql = $this->select()->setIntegrityCheck(false)
            ->from($this, array('pid'))
            ->join('member', 'project.member_id = member.member_id')
            ->where('project.pid is not null ')
            ->where('project.image_small is not null')
            ->where('project.status = ?', self::PROJECT_ACTIVE)
            ->where('member.is_deleted = 0')
            ->where('member.is_active = 1');
        $result = $this->fetchAll($sql)->toArray();
        $randArrayElement = $result[array_rand($result)];
        return $randArrayElement['pid'];
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
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getGalleryPictureSources($projectId)
    {
        $galleryPictureTable = new Default_Model_DbTable_ProjectGalleryPicture();
        $stmt = $galleryPictureTable->select()
            ->where('project_id = ?', $projectId)
            ->order(array('sequence'));

        $pics = array();
        foreach ($galleryPictureTable->fetchAll($stmt) as $pictureRow) {
            $pics[] = $pictureRow['picture_src'];
        }
        return $pics;
    }

    /**
     * @param int $project_id
     * @return array
     */
    public function fetchProjectViews($project_id)
    {
        $sql = "
    SELECT
        `stat_page_views`.`project_id` AS `project_id`,
        count(1) AS `count_views`,
        count(DISTINCT `stat_page_views`.`ip`) AS `count_visitor`,
        max(`stat_page_views`.`created_at`) AS `last_view`
    FROM
        `stat_page_views`
	WHERE `stat_page_views`.`project_id` = ?
    GROUP BY `stat_page_views`.`project_id`
	ORDER BY NULL";
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

    /**
     * @return array
     */
    public function fetchTopProducts()
    {

        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');
        if ($products = $cache->load(__FUNCTION__)) {
            return $products;
        } else {
            $today = new DateTime();
            $limit = 50;
            $products = $this->fetchElementsByFilter(array('ranking' => 'today'), $today->format('Y-m-d'), $limit);
            $cache->save($products, __FUNCTION__);
            return $products;
        }

    }

    public function fetchProductsByCategory($idCategory, $limit = null)
    {
        $select = $this->select()->setIntegrityCheck(false)->from($this->_name)
            ->where('project.project_category_id in (?)', $idCategory)
            ->where('project.status = ?', self::PROJECT_ACTIVE)
            ->where('project.type_id = ?', self::PROJECT_TYPE_STANDARD)
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
                array('cat_title' => 'title'))
            ->order('pling_amount.sumAmount DESC');
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
     * @param bool $withSubCat if was set true it will also count products in sub categories
     * @return int                   count of products in given category
     * @throws Zend_Exception
     */
    public function countProductsInCategory($idCategory = null, $withSubCat = true)
    {
        if (empty($idCategory)) {
            throw new Zend_Exception('idCategory param was not set');
        }

        $cacheName = __FUNCTION__ . md5(serialize($idCategory) . $withSubCat);
        $cache = Zend_Registry::get('cache');

        if ($resultSet = $cache->load($cacheName)) {
            return (int)$resultSet[0]['count_active_projects'];
        }

        $select = $this->select()->setIntegrityCheck(false)->from($this->_name,
            array('count_active_projects' => 'COUNT(1)'))
            ->joinInner('member',
                'member.member_id = project.member_id AND member.is_active = 1 AND member.is_deleted = 0')
            ->where('project.status = ? ', self::PROJECT_ACTIVE)
            ->where('project.type_id = ?', self::PROJECT_TYPE_STANDARD);

        $sqlwhereCat = "";
        $sqlwhereSubCat = "";

        if (false === is_array($idCategory)) {
            $idCategory = array($idCategory);
        }
        $sqlwhereCat .= implode(',', $idCategory);

        if ($withSubCat) {
            $modelCategory = new Default_Model_DbTable_ProjectCategory();
            $subCategories = $modelCategory->fetchChildElements($idCategory);

            if (count($subCategories) > 0) {
                foreach ($subCategories as $element) {
                    $sqlwhereSubCat .= "{$element['project_category_id']},";
                }
            }
        }

        $select->where('project.project_category_id in (' . $sqlwhereSubCat . $sqlwhereCat . ')');

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . $select->__toString());
        //$this->_db->getProfiler()->setEnabled(true);
        $resultSet = $this->fetchAll($select);
        //Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . $this->_db->getProfiler()->getLastQueryProfile()->getQuery());
        //Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . print_r($resultSet->toArray(),true));
        //$this->_db->getProfiler()->setEnabled(false);

        $cache->save($resultSet, $cacheName, array(), 300);

        return (int)$resultSet[0]['count_active_projects'];
    }

    /**
     * @param int|array $idCategory
     * @return int
     */
    public function countActiveMembersForCategory($idCategory)
    {
        //Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . print_r(func_get_args(), true));

        if (is_null($idCategory) OR $idCategory == '' OR is_array($idCategory)) {
            return $this->countActiveProjects();
        }

        $select = $this->select()->setIntegrityCheck(false)->from($this->_name,
            array('count_active_projects' => 'COUNT(1)'))
            ->group('member_id')
            ->where('project.status = ? ', self::PROJECT_ACTIVE)
            ->where('project.type_id = ?', self::PROJECT_TYPE_STANDARD);
        $modelCategory = new Default_Model_DbTable_ProjectCategory();
        $subCategories = $modelCategory->fetchChildElements($idCategory);

        $sqlwhere = "{$idCategory},";
        if (count($subCategories) > 0) {
            foreach ($subCategories as $element) {
                $sqlwhere .= "{$element['project_category_id']},";
            }
        }
        $sqlwhere = substr($sqlwhere, 0, -1);
        $select->where('project.project_category_id in (' . $sqlwhere . ') OR (project.project_id IN (SELECT project_id FROM project_subcategory WHERE project_sub_category_id = ' . $idCategory . '))');

//    	$this->_db->getProfiler()->setEnabled(true);
        $resultSet = $this->fetchAll($select);
//    	Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . $this->_db->getProfiler()->getLastQueryProfile()->getQuery());
//    	Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . print_r($resultSet->toArray(),true));
//    	$this->_db->getProfiler()->setEnabled(false);

        if (count($resultSet) > 0) {
            return (int)$resultSet[0]['count_active_projects'];
        } else {
            return 0;
        }
    }

    /**
     * @return int
     */
    public function countActiveProjects()
    {
        $q = $this->select()->from($this->_name, array('count_active_projects' => 'COUNT(1)'))
            ->where('status = ?', self::PROJECT_ACTIVE)
            ->where('type_id = ?', self::PROJECT_TYPE_STANDARD);
        $resultSet = $q->query()->fetchAll();

        return (int)$resultSet[0]['count_active_projects'];
    }

    /**
     * @param $idCategory
     * @return int
     * @throws Zend_Exception
     * @deprecated
     */
    public function countProductsBySubCategory($idCategory)
    {
        $select = $this->select()->setIntegrityCheck(false)->from($this->_name,
            array('count_active_projects' => 'COUNT(*)'))
            ->where('project.status = ?', self::PROJECT_ACTIVE)
            ->where('project.type_id = ?', self::PROJECT_TYPE_STANDARD)
            ->joinLeft('project_subcategory', 'project_subcategory.project_id = project.project_id')
            ->where('project_subcategory.project_sub_category_id = ?', $idCategory);

        $this->_db->getProfiler()->setEnabled(true);
        $resultSet = $this->fetchAll($select);
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . $this->_db->getProfiler()->getLastQueryProfile()->getQuery());
        Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . print_r($resultSet->toArray(), true));
        $this->_db->getProfiler()->setEnabled(false);

        return (int)$resultSet[0]['count_active_projects'];
    }

    /**
     * @param $idCategory
     * @param null $limit
     * @return Zend_Db_Table_Rowset_Abstract
     * @deprecated
     */
    public function fetchProductsBySubCategory($idCategory, $limit = null)
    {
        $select = $this->select()->setIntegrityCheck(false)->from($this->_name)
            ->where('project.status = ?', self::PROJECT_ACTIVE)
            ->where('project.type_id = ?', self::PROJECT_TYPE_STANDARD)
            ->joinLeft(array(
                'pling_amount' => new Zend_Db_Expr('(SELECT
                project_id as plinged_project_id, SUM(amount) AS sumAmount, count(1) as countPlings
            FROM
                plings
            where status_id >= 2
            group by project_id
            order by sumAmount DESC)')
            ), 'pling_amount.plinged_project_id = project.project_id')
            ->joinLeft('project_subcategory', 'project_subcategory.project_id = project.project_id')
            ->where('project_subcategory.project_sub_category_id = ?', $idCategory)
            ->order('pling_amount.sumAmount DESC');
        if (false === is_null($limit)) {
            $select->limit($limit);
        }

        return $this->fetchAll($select);
    }

    public function fetchTotalProjectsCount()
    {
        $sql = "SELECT count(1) AS total_project_count FROM project WHERE project.status = :status AND project.type_id = :ptype";

        $result = $this->_db->fetchRow($sql,
            array('status' => self::PROJECT_ACTIVE, 'ptype' => self::PROJECT_TYPE_STANDARD));

        return $result['total_project_count'];
    }

    public function setAllProjectsForMemberDeleted($member_id)
    {
        $sql = '
                update project
                set status = :statusValue, deleted_at = NOW()
                where member_id = :memberId;
        ';
        $this->_db->query($sql, array('statusValue' => self::PROJECT_DELETED, 'memberId' => $member_id))->execute();
    }

    public function setAllProjectsForMemberActivated($member_id)
    {
        $sql = '
                update project
                set status = :statusValue, changed_at = NOW()
                where member_id = :memberId;
        ';
        $this->_db->query($sql, array('statusValue' => self::PROJECT_ACTIVE, 'memberId' => $member_id))->execute();
    }

    /**
     * @param array $inputFilterParams
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function fetchProjectsByFilter($inputFilterParams, $limit = null, $offset = null)
    {
        $cacheName = __FUNCTION__ . '_' . md5(serialize($inputFilterParams) . (string)$limit . (string)$offset);
        /** @var Zend_Cache_Core $cache */
        $cache = Zend_Registry::get('cache');

        if (false === ($returnValue = $cache->load($cacheName))) {
            $statement = $this->generateStatement($inputFilterParams, $limit, $offset);
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . $statement->__toString());
            /** @var Zend_Db_Table_Rowset $fetchedElements */
            $fetchedElements = $this->fetchAll($statement);

            $statement->reset('limitcount')->reset('limitoffset');
            $statement->reset('columns')->columns(array('count' => new Zend_Db_Expr('count(*)')));
            Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . $statement->__toString());
            $countElements = $this->fetchRow($statement);
            $returnValue = array('elements' => $fetchedElements, 'total_count' => $countElements->count);

            $cache->save($returnValue, $cacheName, array(), 300);
        }
        return $returnValue;
    }

    /**
     * @param array $inputFilterParams
     * @param int|null $limit
     * @param int|null $offset
     * @return Zend_Db_Select
     * @throws Zend_Exception
     */
    protected function generateStatement($inputFilterParams, $limit = null, $offset = null)
    {
        $statement = $this->generateBaseStatement();
        $statement = $this->generateCategoryFilter($statement, $inputFilterParams);
        $statement = $this->generateOrderFilter($statement, $inputFilterParams);

        $statement->limit($limit, $offset);
        return $statement;
    }

    /**
     * @return Zend_Db_Select
     */
    protected function generateBaseStatement()
    {
        $statement = $this->select()->setIntegrityCheck(false);
        $statement->from(array('project' => $this->_name), array(
            '*',
            'project_changed_at' => 'changed_at',
            'laplace_score' => new Zend_Db_Expr('(round(((count_likes + 6) / ((count_likes + count_dislikes) + 12)),2) * 100)')
        ));
        $statement->join(array('member' => 'member'),
            'project.member_id = member.member_id AND member.is_active = 1 AND member.is_deleted = 0');
        $statement->join(array('project_category' => 'project_category'),
            'project.project_category_id = project_category.project_category_id',
            array('cat_title' => 'title'));
        $statement->joinLeft(array('stat_plings' => 'stat_plings'), 'project.project_id = stat_plings.project_id',
            array('amount_received', 'count_plings', 'count_plingers', 'latest_pling'));
        $statement->where('project.status = ?', self::PROJECT_ACTIVE)
            ->where('project.type_id=?', self::PROJECT_TYPE_STANDARD);
        return $statement;
    }

    /**
     * @param Zend_Db_Select $statement
     * @param array $filterArrayValue
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
        $allCategories = array();
        foreach ($filter as $catId) {
            $allCategories[] = $catId;
            $childElements = $modelProjectCategories->fetchChildElements($catId);
            $childIds = array();
            foreach ($childElements as $child) {
                $childIds[] = $child['project_category_id'];
            }
            $allCategories = array_merge($allCategories, $childIds);
        }


        $stringCategories = implode(',', $allCategories);

        $statement->where("(
        		project.project_category_id IN ({$stringCategories})
        		)");

        return $statement;
    }

    /**
     * @param Zend_Db_Select $statement
     * @param array $filterArrayValue
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
     * @param int $member_id
     * @param array $values
     * @param string $username
     * @return Zend_Db_Table_Row_Abstract
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
        $values['created_at'] = (!array_key_exists('created_at',
            $values)) ? new Zend_Db_Expr('NOW()') : $values['created_at'];
        $values['start_date'] = (!array_key_exists('start_date',
            $values)) ? new Zend_Db_Expr('NULL') : $values['start_date'];
        $values['creator_id'] = (!array_key_exists('creator_id', $values)) ? $member_id : $values['creator_id'];

        if ($username == 'pling editor') {
            $values['claimable'] = (!array_key_exists('claimable',
                $values)) ? self::PROJECT_CLAIMABLE : $values['claimable'];
        }

        $savedRow = $this->save($values);
        return $savedRow;
    }

    /**
     * @param int $project_id
     * @param array $values
     * @return Zend_Db_Table_Row_Abstract
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
     * @param $uuid
     * @param bool $activeOnly
     * @return Zend_Db_Table_Row_Abstract
     * @deprecated
     */
    public function fetchProjectForUUID($uuid, $activeOnly = true)
    {
        $whereProjectActive = '';
        if (true === $activeOnly) {
            $whereProjectActive = " and status = " . self::PROJECT_ACTIVE;
        }
        $sql = "SELECT * FROM {$this->_name} WHERE uuid = :uuid {$whereProjectActive}";

        $resultSet = $this->_db->query($sql, array('uuid' => $uuid))->fetch();

        if (false === $resultSet) {
            return $this->generateRowClass(array());
        }

        return $this->generateRowClass($resultSet);
    }

    /**
     * @param int|array $storeCategories
     * @param boolean $withoutUpdates
     * @return array
     * @todo: update the sql. It is deprecated since we store only one cat_id for the product.
     */
    public function fetchProductsForCategories($storeCategories, $withoutUpdates = true)
    {
//        Zend_Registry::get('logger')->debug(__METHOD__ . ' - ' . print_r(func_get_args(), true));

        if (empty($storeCategories)) {
            return array();
        }

//        $inQuery = '?';
//        if (is_array($storeCategories)) {
//            $inQuery = implode(',', array_fill(0, count($storeCategories), '?'));
//        }

        $sql = "
                SELECT
                  p.*,
                  p.changed_at as project_changed_at,
                  pc.title as cat_title,
                  m.username,
                  m.avatar,
                  m.profile_image_url,
                  m.roleId,
                  m.mail,
                  m.paypal_mail,
                  m.dwolla_id,
               	 (round(((p.count_likes + 6) / ((p.count_likes + p.count_dislikes) + 12)),2) * 100) as laplace_score,
               	 sp.amount_received,
               	 sp.count_plings,
               	 sp.count_plingers,
               	 sp.latest_pling
                FROM project AS p
                  JOIN member AS m ON p.member_id = m.member_id AND m.is_active = 1 AND m.is_deleted = 0
                  JOIN project_category AS pc ON p.project_category_id = pc.project_category_id
                  LEFT JOIN stat_plings as sp ON p.project_id = sp.project_id
                WHERE p.project_category_id IN (" . implode(',', $storeCategories) . ")
                AND p.status >= 100
        ";

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
     * @param array $inputFilterParams
     * @param int|null $limit
     * @param int|null $offset
     * @return Zend_Db_Select
     * @throws Zend_Exception
     */
    protected function generateSmallStatement($inputFilterParams, $limit = null, $offset = null)
    {
        $statement = $this->generateSmallBaseStatement($offset);
        $statement = $this->generateCategoryFilter($statement, $inputFilterParams);

        $statement->limit($limit, $offset);
        return $statement;
    }

    /**
     * @return Zend_Db_Select
     */
    protected function generateSmallBaseStatement($offset = null)
    {
        $statement = $this->select()->setIntegrityCheck(false);
        $statement->from(array('project' => $this->_name), array(
            '*',
            'rownum' => '(' . $offset . ')',
            'laplace_score' => new Zend_Db_Expr('(round(((count_likes + 6) / ((count_likes + count_dislikes) + 12)),2) * 100)')
        ));
        $statement->join(array('member' => 'member'), 'project.member_id = member.member_id', array(
            'username',
            'mail',
            'avatar',
            'is_active',
            'is_deleted',
            'agb',
            'newsletter',
            'paypal_mail',
            'dwolla_id',
            'profile_image_url',
            'link_facebook',
            'link_twitter',
            'link_website',
            'link_google',
            'validated',
            'main_project_id'
        ));
        $statement->join(array('project_category' => 'project_category'),
            'project.project_category_id = project_category.project_category_id',
            array('cat_title' => 'title'));
        $statement->joinLeft(array('project_subcategory' => 'project_subcategory'),
            'project.project_id = project_subcategory.project_id',
            array('project_sub_category_id'));
        $statement->where('project.status = ?', self::PROJECT_ACTIVE)
            ->where('project.type_id=?', self::PROJECT_TYPE_STANDARD)
            ->where('project.pid is null')
            ->where('member.is_deleted=?', Default_Model_DbTable_Member::MEMBER_NOT_DELETED)
            ->where('member.is_active=?', Default_Model_Member::MEMBER_ACTIVE);
        return $statement;
    }

    /**
     * @param Zend_Db_Select $statement
     * @param array $filterArrayValue
     * @return Zend_Db_Select
     * @deprecated
     */
    protected function getMemberFilter(Zend_Db_Select $statement, $filterArrayValue)
    {
        if (false == isset($filterArrayValue[self::FILTER_NAME_MEMBER])) {
            return $statement;
        }
        if (false == empty($filterArrayValue['filter'])) {
            $filterValue = $filterArrayValue['filter'];
        } elseif (false == empty($filterArrayValue[self::FILTER_NAME_MEMBER])) {
            $filterValue = $filterArrayValue[self::FILTER_NAME_MEMBER];
        }

        if (!empty($filterValue)) {
            $statement->where('(project.member_id = ?)', $filterValue);
        }

        return $statement;
    }

    /**
     * @return Zend_Db_Select
     * @deprecated
     */
    protected function getBaseStatementHour()
    {
        $statement = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('stat_daily' => 'stat_now'),
                array(
                    'count_views' => new Zend_Db_Expr('SUM(stat_daily.count_views)'),
                    'count_plings' => new Zend_Db_Expr('SUM(stat_daily.count_plings)'),
                    'count_updates' => new Zend_Db_Expr('SUM(stat_daily.count_updates)'),
                    'count_comments' => new Zend_Db_Expr('SUM(stat_daily.count_comments)'),
                    'count_followers' => new Zend_Db_Expr('SUM(stat_daily.count_followers)'),
                    'count_supporters' => new Zend_Db_Expr('SUM(stat_daily.count_supporters)'),
                    self::FILTER_NAME_RANKING => new Zend_Db_Expr('SUM(stat_daily.ranking_value)')
                ));
        $statement->join('project', 'stat_daily.project_id=project.project_id');
        $statement->join('member', 'project.member_id=member.member_id',
            array(
                'username' => 'username',
                'profile_image_url',
                'city',
                'country',
                'member_created_at' => 'created_at',
                'paypal_mail'
            ));
        $statement->join('project_category', 'stat_daily.project_category_id=project_category.project_category_id',
            array('catTitle' => 'title'));
        $statement->where('project.status>?', self::PROJECT_INACTIVE)
            ->where('project.type_id=?', self::PROJECT_TYPE_STANDARD)
            ->where('project.pid is null');
        $statement->group(array('project_id', 'project_category_id', 'project_type_id'));
        return $statement;
    }

    /**
     * @param Zend_Db_Select $statement
     * @param array $filterArrayValue
     * @return Zend_Db_Select
     * @deprecated
     */
    protected function getLocationFilter(Zend_Db_Select $statement, $filterArrayValue)
    {
        if (!isset($filterArrayValue[self::FILTER_NAME_LOCATION])) {
            return $statement;
        } else {
            $filterValue = $filterArrayValue[self::FILTER_NAME_LOCATION];
        }

        if (!empty($filterValue)) {
            $statement->where('city = ?', $filterValue);
        }

        return $statement;
    }

}