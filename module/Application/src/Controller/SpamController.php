<?php
/** @noinspection PhpUndefinedFieldInspection */
/** @noinspection PhpUnused */

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
 *
 * Created: 31.05.2017
 */

namespace Application\Controller;

use Application\Model\Interfaces\CommentsInterface;
use Application\Model\Repository\BaseRepository;
use Application\Model\Service\Interfaces\MemberServiceInterface;
use Application\Model\Service\Interfaces\ProjectServiceInterface;
use Application\Model\Service\Interfaces\SectionServiceInterface;
use Application\Model\Service\SpamService;
use Application\Model\Service\Util;
use DateTime;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

/**
 * Class SpamController
 *
 * @package Application\Controller
 */
class SpamController extends BaseController
{
    const RESULT_OK = "OK";
    const RESULT_ERROR = "ERROR";
    const IDENTIFIER = 'comment_id';

    private $memberService;
    /** @var ProjectServiceInterface */
    private $projectService;
    private $spamService;
    private $baseRepository;
    private $commentsRepository;
    private $sectionService;

    public function __construct(
        MemberServiceInterface $memberService,
        ProjectServiceInterface $projectService,
        SpamService $spamService,
        BaseRepository $baseRepository,
        CommentsInterface $commentsRepository,
        SectionServiceInterface $sectionService
    ) {
        parent::__construct();
        $this->memberService = $memberService;
        $this->projectService = $projectService;
        $this->spamService = $spamService;
        $this->baseRepository = $baseRepository;
        $this->commentsRepository = $commentsRepository;
        $this->sectionService = $sectionService;
    }

    public function indexAction()
    {
        $this->layout()->setTemplate('layout/flat-ui');
        $this->layout()->noheader = 1;
        $viewModel = new ViewModel();
        $viewModel = $this->prepareIndex($viewModel);

        return $viewModel;
    }

    private function prepareIndex(ViewModel $viewModel)
    {
        $itemCountPerPage = 10;
        $page = $this->params()->fromRoute('page', 1);
        $candidateProducts = $this->spamService->fetchSpamCandidate();
        $list = new Paginator(new ArrayAdapter($candidateProducts));
        $list->setItemCountPerPage($itemCountPerPage);
        $list->setCurrentPageNumber($page);
        $rownum = 1 + (($page - 1) * $itemCountPerPage);
        foreach ($list as &$l) {
            $l['updateTime'] = Util::printDate($l['project_changed_at']);
            $l['projectDeletionAllowed'] = $this->projectService->isAllowedForDeletion($l['project_id']);
            $l['userDeletionAllowed'] = $this->memberService->isAllowedForDeletion($l['member_id']);
            $l['rownum'] = $rownum++;
        }
        $viewModel->setVariable('products', $list);

        return $viewModel;
    }

    public function commentsAction()
    {
        return $this->initViewModel();
    }

    private function initViewModel()
    {
        $this->layout()->setTemplate('layout/flat-ui');
        $this->layout()->noheader = 1;

        return new ViewModel();
    }

    public function productAction()
    {
        return $this->initViewModel();
    }

    public function newproductAction()
    {

        $sections = $this->sectionService->fetchAllSections();
        $viewModel = $this->initViewModel();
        $viewModel->setVariable('sections', $sections);

        return $viewModel;
    }

    public function mostnewproductAction()
    {
        return $this->initViewModel();
    }

    public function unpublishedproductAction()
    {
        return $this->initViewModel();
    }

    public function paypalAction()
    {
        return $this->initViewModel();
    }

    public function mdsumAction()
    {
        return $this->initViewModel();
    }

    public function deprecatedAction()
    {
        return $this->initViewModel();
    }

    public function deletecommentAction()
    {
        $commentId = (int)$this->params(self::IDENTIFIER, null);

        $data = ['comment_id' => $commentId, 'comment_active' => 0];
        $this->commentsRepository->update($data);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Record'] = $commentId;

        return new JsonModel($jTableResult);
    }

    public function deletereportsAction()
    {
        //$commentId = (int)$this->getParam(self::IDENTIFIER, null);  
        $commentId = (int)$this->params()->fromQuery(self::IDENTIFIER, null);

        $sql = '
	            UPDATE `reports_comment`
	            SET `is_deleted` = 1
	            WHERE `comment_id` = :comment_id';
        $this->baseRepository->query($sql, array('comment_id' => $commentId));

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Record'] = array();

        return new JsonModel($jTableResult);
    }

    public function paypallistAction()
    {
        $startIndex = (int)$this->params()->fromQuery('jtStartIndex');
        $pageSize = (int)$this->params()->fromQuery('jtPageSize');
        $sorting = $this->params()->fromQuery('jtSorting');

        if (!isset($sorting)) {
            $sorting = ' paypal_mail ';
        }

        $sql = "
                    SELECT `a`.*, 
                    (       SELECT sum(`d`.`credits_plings`)/100  `amount`
                            FROM `micro_payout` `d`
                            WHERE `d`.`member_id` IN (`a`.`ids`)
                            AND `d`.`yearmonth`= DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y%m')
                            AND `d`.`is_pling_excluded` = 0 
                            AND `d`.`is_license_missing` = 0
                            AND `d`.`is_member_pling_excluded` = 0
                            AND `d`.`is_source_missing` = 0
                            ) AS `amount`
                    FROM
                    (
                        SELECT `paypal_mail`, GROUP_CONCAT(`member_id`) `ids`, GROUP_CONCAT(`username`) `names`
                        , count(1) `cnt` 
                        , GROUP_CONCAT(`m`.`is_deleted`) `is_deleted`    
                        ,max(`m`.`created_at`) AS `created_at`    
                        ,sum(`m`.`is_deleted`) AS `sum_is_deleted`
                        FROM `member` `m`
                        WHERE  `m`.`paypal_mail` IS NOT NULL AND `m`.`paypal_mail` <> '' AND (`m`.`paypal_mail` REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$') 
                        GROUP BY `paypal_mail`
                        ORDER BY `m`.`created_at` DESC
                    ) `a`
                    WHERE  `cnt` > 1 AND `cnt`>`sum_is_deleted`
                    
        
                    
                ";
        $sql .= ' order by ' . $sorting;
        $sql .= ' limit ' . $pageSize;
        $sql .= ' offset ' . $startIndex;

        $results = $this->baseRepository->fetchAll($sql);

        $sqlall = "  SELECT count(1) `cnt` FROM
                    (
                        SELECT 
                        `paypal_mail`
                        ,GROUP_CONCAT(`member_id`) `ids`
                        ,GROUP_CONCAT(`username`) `names`
                        ,count(1) `cnt` 
                        ,GROUP_CONCAT(`m`.`is_deleted`) `is_deleted`    
                        ,max(`m`.`created_at`) AS `created_at`    
                        ,sum(`m`.`is_deleted`) AS `sum_is_deleted`
                        FROM `member` `m`
                        WHERE  `m`.`paypal_mail` IS NOT NULL AND `m`.`paypal_mail` <> '' AND (`m`.`paypal_mail` REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$') 
                        GROUP BY `paypal_mail`  
                    ) `a`
                    WHERE `cnt` > 1 AND `cnt`>`sum_is_deleted`";

        $reportsAll = $this->baseRepository->fetchRow($sqlall);


        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $results;
        $jTableResult['TotalRecordCount'] = $reportsAll['cnt'];

        return new JsonModel($jTableResult);

    }

    public function mostnewproductlistAction()
    {
        $startIndex = (int)$this->params()->fromQuery('jtStartIndex');
        $pageSize = (int)$this->params()->fromQuery('jtPageSize');
        $sorting = $this->params()->fromQuery('jtSorting');
        if (!isset($sorting)) {
            $sorting = ' cnt desc';
        }
        $filterMonth = $this->params()->fromPost('filterMonth', null);
        $nonwallpaper = $this->params()->fromPost('nonwallpaper', null);
        if ($filterMonth == null) {
            $now = new DateTime('now');
            $ymd = $now->format('Y-m');
        } else {
            $ymd = DateTime::createFromFormat('Ym', $filterMonth)->format('Y-m');
        }

        $time_begin = $ymd . '-01 00:00:00';
        $time_end = $ymd . '-31 23:59:59';

        $sql = "SELECT 
                `p`.`member_id`, 
                `m`.`username`,
                count(1) AS `cnt` ,
                `m`.`created_at`,
                (SELECT count(1) FROM `stat_projects` `pp` WHERE `pp`.`member_id` = `p`.`member_id` AND `pp`.`status`=100 AND `pp`.`created_at` < :time_begin) AS `cntOther`
                FROM `stat_projects` `p`
                JOIN `stat_cat_tree` `t` ON `p`.`project_category_id` = `t`.`project_category_id`    
                JOIN `member` `m` ON `p`.`member_id` = `m`.`member_id`
                WHERE `p`.`status` = 100                 
                ";

        $lft = 0;
        $rgt = 0;

        if ($nonwallpaper == 1) {
            //get wallpaper category lft rgt
            $tmpsql = "SELECT `lft`, `rgt` FROM `stat_cat_tree` WHERE `project_category_id`=295";
            $wal = $this->baseRepository->fetchRow($tmpsql);

            $lft = $wal['lft'];
            $rgt = $wal['rgt'];
            $sql = $sql . ' and (t.lft<' . $lft . ' or t.rgt>' . $rgt . ') ';
        }

        $sql = $sql . '  and p.created_at between :time_begin and :time_end
                        group by member_id';


        if (isset($sorting)) {
            $sql = $sql . '  order by ' . $sorting;
        }

        if (isset($pageSize)) {
            $sql .= ' limit ' . (int)$pageSize;
        }

        if (isset($startIndex)) {
            $sql .= ' offset ' . (int)$startIndex;
        }


        $resultSet = $this->baseRepository->fetchAll($sql, array('time_begin' => $time_begin, 'time_end' => $time_end));

        $sqlTotal = "
                    SELECT count(1) AS `cnt` FROM(
                        SELECT                         
                               `p`.`member_id`
                        FROM `stat_projects` `p`         
                        JOIN `stat_cat_tree` `t` ON `p`.`project_category_id` = `t`.`project_category_id`                           
                        WHERE `p`.`status` = 100 
                        )
          ";

        if ($nonwallpaper == 1) {
            $sqlTotal = $sqlTotal . ' and (t.lft<' . $lft . ' or t.rgt>' . $rgt . ') ';
        }

        $sqlTotal = $sqlTotal . '  and p.created_at between :time_begin and :time_end
                       group by member_id 
                        ) t ';

        $resultTotal = $this->baseRepository->fetchRow(
            $sqlTotal, array('time_begin' => $time_begin, 'time_end' => $time_end)
        );

        $totalRecordCount = $resultTotal['cnt'];
        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $resultSet;
        $jTableResult['TotalRecordCount'] = $totalRecordCount;

        return new JsonModel($jTableResult);
    }

    public function mdsumlistAction()
    {

        $startIndex = (int)$this->params()->fromQuery('jtStartIndex');
        $pageSize = (int)$this->params()->fromQuery('jtPageSize');
        $sorting = $this->params()->fromQuery('jtSorting');

        if (!isset($sorting)) {
            $sorting = ' changed_at desc ';
        }

        $sql = "
                select 
                * 
                from 
                (
                    select f.owner_id as member_id,m.username, f.md5sum, COUNT(1) cnt, GROUP_CONCAT(distinct p.project_id) as projects
                    , count(distinct p.project_id) cntProjects
                    ,max(p.changed_at) as changed_at
                    from  ppload.ppload_files f
                    join project p on f.collection_id = p.ppload_collection_id
                    join member m on f.owner_id = m.member_id and m.is_deleted=0 and m.is_active = 1
                    where f.md5sum is not null
                    group by f.md5sum 
                    having count(1)>1
                ) t
                where cntProjects>1                                    
                ";
        $sql .= ' order by ' . $sorting;
        $sql .= ' limit ' . $pageSize;
        $sql .= ' offset ' . $startIndex;

        $results = $this->baseRepository->fetchAll($sql);

        $sqlall = "
                 select 
                    count(1) as cnt
                    from 
                    (
                        select f.owner_id as member_id,m.username, f.md5sum, COUNT(1) cnt, GROUP_CONCAT(distinct p.project_id) as projects,count(distinct p.project_id) cntProjects
                        from  ppload.ppload_files f
                        join project p on f.collection_id = p.ppload_collection_id
                        join member m on f.owner_id = m.member_id and m.is_deleted=0 and m.is_active = 1
                        where f.md5sum is not null
                        group by f.md5sum 
                        having count(1)>1
                    ) t
                    where cntProjects>1
                  ";

        $reportsAll = $this->baseRepository->fetchRow($sqlall);


        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $results;
        $jTableResult['TotalRecordCount'] = $reportsAll['cnt'];

        return new JsonModel($jTableResult);

    }

    public function newproductlistAction()
    {
        $filter['filter_section'] = $this->params()->fromPost('filter_section');

        $startIndex = (int)$this->params()->fromQuery('jtStartIndex');
        $pageSize = (int)$this->params()->fromQuery('jtPageSize');
        $sorting = $this->params()->fromQuery('jtSorting');

        if (!isset($sorting)) {
            $sorting = ' earn desc, created_at desc';
        }

        $sql = "
                select ss.section_id, ss.name as section_name, pp.project_id,pp.status,pp.member_id, pp.created_at, m.username, m.paypal_mail,m.created_at as member_since, c.title cat_title,c.lft, c.rgt
                ,(select sum(probably_payout_amount) amount
                from member_dl_plings pl
                where pl.project_id=pp.project_id
                and pl.member_id = pp.member_id
                and pl.yearmonth= DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y%m')
                and pl.is_pling_excluded = 0 
                and pl.is_license_missing = 0
                ) as earn                    
                from
                project pp                    
                ,member m
                ,project_category c
                ,section_category s
                ,section ss
                where pp.member_id = m.member_id 
                and pp.project_category_id = c.project_category_id and m.is_deleted=0 and m.is_active = 1
                and s.project_category_id = c.project_category_id
                and s.section_id = ss.section_id
                and pp.created_at > (CURRENT_DATE() - INTERVAL 2 MONTH)                                            
                                        
        ";
        if ($filter['filter_section']) {
            $sql .= " and ss.section_id = " . $filter['filter_section'];
        }
        $sql .= ' order by ' . $sorting;
        $sql .= ' limit ' . $pageSize;
        $sql .= ' offset ' . $startIndex;

        $results = $this->baseRepository->fetchAll($sql);

        $tmpsql = "SELECT `lft`, `rgt` FROM `project_category` WHERE `project_category_id`=295";
        $wal = $this->baseRepository->fetchRow($tmpsql);
        $lft = $wal['lft'];
        $rgt = $wal['rgt'];
        foreach ($results as &$value) {
            $value['created_at'] = Util::printDateSince($value['created_at']);
            if ($value['earn'] && $value['earn'] > 0) {
                $value['earn'] = number_format($value['earn'], 2, '.', '');
            }
            if ($value['lft'] >= $lft && $value['rgt'] <= $rgt) {
                $value['is_wallpaper'] = 1;
            } else {
                $value['is_wallpaper'] = 0;
            }
        }

        $sqlTotal = "select count(1) as cnt
                from
                project pp                    
                ,member m
                ,project_category c
                ,section_category s
                ,section ss
                where pp.member_id = m.member_id 
                and pp.project_category_id = c.project_category_id and m.is_deleted=0 and m.is_active = 1
                and s.project_category_id = c.project_category_id
                and s.section_id = ss.section_id
                and pp.created_at > (CURRENT_DATE() - INTERVAL 2 MONTH)   ";

        if ($filter['filter_section']) {
            $sqlTotal .= " and ss.section_id = " . $filter['filter_section'];
        }
        $resultsCnt = $this->baseRepository->fetchRow($sqlTotal);
        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $results;
        $jTableResult['TotalRecordCount'] = $resultsCnt['cnt'];

        return new JsonModel($jTableResult);

    }

    public function deprecatedlistAction()
    {
        $startIndex = (int)$this->params()->fromQuery('jtStartIndex');
        $pageSize = (int)$this->params()->fromQuery('jtPageSize');
        $sorting = $this->params()->fromQuery('jtSorting');
        if (!isset($sorting)) {
            $sorting = ' tag_created desc';
        }
        $sql = "
                    select 
                    project_id,
                    title,
                    username,
                    member_id,
                    cat_title,
                    profile_image_url,
                    o.tag_created created_at
                    from 
                    stat_projects p
                    inner join tag_object o on p.project_id = o.tag_object_id and o.tag_id= 5589 and o.tag_group_id = 36 and is_deleted = 0 and tag_type_id = 1
                    
        	";

        $sql .= ' order by ' . $sorting;
        $sql .= ' limit ' . $pageSize;
        $sql .= ' offset ' . $startIndex;

        $results = $this->baseRepository->fetchAll($sql);

        foreach ($results as &$value) {
            $value['created_at'] = Util::printDateSince($value['created_at']);
            $value['avatar'] = Util::Image(
                $value['profile_image_url'], array('width' => '200', 'height' => '200', 'crop' => 2)
            );
        }

        $sqlall = "	select count(1) 
                    from stat_projects p
                    inner join tag_object o on p.project_id = o.tag_object_id and o.tag_id= 5589 and o.tag_group_id = 36 and is_deleted = 0 and tag_type_id = 1
                    ";

        $reportsAll = $this->baseRepository->fetchRow($sqlall);

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $results;
        $jTableResult['TotalRecordCount'] = array_pop($reportsAll);

        return new JsonModel($jTableResult);
    }

    public function unpublishedproductlistAction()
    {
        $startIndex = (int)$this->params()->fromQuery('jtStartIndex');
        $pageSize = (int)$this->params()->fromQuery('jtPageSize');
        $sorting = $this->params()->fromQuery('jtSorting');

        if (!isset($sorting)) {
            $sorting = ' unpublished_time desc';
        }

        $sql = "
                    SELECT `pp`.`project_id`,`pp`.`title`,`pp`.`status`,`pp`.`member_id`, `pp`.`created_at`, `m`.`username`, `m`.`paypal_mail`,`m`.`created_at` AS `member_since`, `c`.`title` `cat_title`,`c`.`lft`, `c`.`rgt`
                    ,(SELECT sum(`probably_payout_amount`) `amount`
                    FROM `member_dl_plings` 
                    WHERE `member_id`=`pp`.`member_id`
                    AND `yearmonth`= DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y%m')
                    AND `is_pling_excluded` = 0 
                    AND `is_license_missing` = 0
                    ) AS `earn` ,
                    (SELECT max(`time`) FROM `pling`.`activity_log` `l` WHERE `l`.`activity_type_id` = 9 AND `project_id` = `pp`.`project_id`) AS `unpublished_time`
                    ,(
                        SELECT  sum(`m`.`credits_plings`)/100 AS `probably_payout_amount` FROM `micro_payout` `m`
                        WHERE `m`.`project_id`=`pp`.`project_id` 
                        AND `m`.`paypal_mail` IS NOT NULL 
                        AND `m`.`paypal_mail` <> '' AND (`m`.`paypal_mail` REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+.[A-Z]{2,4}$') 
                        AND `m`.`yearmonth` = DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y%m')
                    ) AS `probably_payout_amount`
                    FROM `project` `pp`                    
                    JOIN `member` `m` ON `pp`.`member_id` = `m`.`member_id` AND `m`.`is_deleted`=0 AND `m`.`is_active` = 1
                    JOIN `project_category` `c` ON `pp`.`project_category_id` = `c`.`project_category_id`        
                    WHERE `pp`.`status` = 40 
                    
                                        
        ";
        $sql .= ' order by ' . $sorting;
        $sql .= ' limit ' . $pageSize;
        $sql .= ' offset ' . $startIndex;

        $results = $this->baseRepository->fetchAll($sql);

        $tmpsql = "select lft, rgt from project_category where project_category_id=295";
        $wal = $this->baseRepository->fetchRow($tmpsql);
        $lft = $wal['lft'];
        $rgt = $wal['rgt'];
        foreach ($results as &$value) {
            $value['created_at'] = Util::printDateSince($value['created_at']);
            $value['unpublished_time'] = Util::printDateSince($value['unpublished_time']);
            if ($value['earn'] && $value['earn'] > 0) {
                $value['earn'] = number_format($value['earn'], 2, '.', '');
            }
            if ($value['lft'] >= $lft && $value['rgt'] <= $rgt) {
                $value['is_wallpaper'] = 1;
            } else {
                $value['is_wallpaper'] = 0;
            }
        }

        $sqltotal = "select count(1) as cnt from
                        project pp                                         
                    where pp.status = 40 ";
        $resultsCnt = $this->baseRepository->fetchRow($sqltotal);
        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $results;
        $jTableResult['TotalRecordCount'] = $resultsCnt['cnt'];

        return new JsonModel($jTableResult);

    }

    public function productfilesAction()
    {
        $startIndex = (int)$this->params()->fromQuery('jtStartIndex');
        $pageSize = (int)$this->params()->fromQuery('jtPageSize');
        $sorting = $this->params()->fromQuery('jtSorting');

        if (!isset($sorting)) {
            $sorting = ' created_at desc';
        }

        $sql = "
                SELECT `pp`.`project_id`,`pp`.`status`,`pp`.`member_id`, `pp`.`created_at`, `cntfiles`,`size`, `m`.`username`, `m`.`paypal_mail`,`m`.`created_at` AS `member_since`, `c`.`title` `cat_title`,`c`.`lft`, `c`.`rgt`
                    ,(SELECT sum(`probably_payout_amount`) `amount`
                    FROM `member_dl_plings` 
                    WHERE `member_id`=`pp`.`member_id`
                    AND `yearmonth`= DATE_FORMAT(CURRENT_DATE() - INTERVAL 1 MONTH, '%Y%m')
                    AND `is_pling_excluded` = 0 
                    AND `is_license_missing` = 0
                    ) AS `earn`,
                    `m`.`is_deleted` 
                    FROM
                    (
                        SELECT 
                        `p`.`project_id`,
                        `p`.`created_at`,
                        `p`.`changed_at`,
                        `p`.`member_id`,    
                        `p`.`status`,
                        `p`.`project_category_id`,
                        count(1) `cntfiles`,
                        sum(`size`) `size`
                        FROM 
                        `project` `p`,
                        `ppload`.`ppload_files` `f`
                        WHERE `p`.`ppload_collection_id` = `f`.`collection_id`
                        GROUP BY `p`.`project_id`
                        ORDER BY `p`.`created_at` DESC, `cntfiles` DESC
                    )
                    `pp` 
                    ,`member` `m`
                    ,`project_category` `c`
                    WHERE `pp`.`member_id` = `m`.`member_id`
                    AND `pp`.`project_category_id` = `c`.`project_category_id` AND `m`.`is_deleted`=0 AND `m`.`is_active` = 1
                    AND `cntfiles` > 10
        ";
        $sql .= ' order by ' . $sorting;
        $sql .= ' limit ' . $pageSize;
        $sql .= ' offset ' . $startIndex;

        $results = $this->baseRepository->fetchAll($sql);

        $tmpsql = "select lft, rgt from project_category where project_category_id=295";
        $wal = $this->baseRepository->fetchRow($tmpsql);
        $lft = $wal['lft'];
        $rgt = $wal['rgt'];
        foreach ($results as &$value) {
            $value['created_at'] = Util::printDateSince($value['created_at']);
            $value['size'] = Util::humanFilesize($value['size']);
            if ($value['earn'] && $value['earn'] > 0) {
                $value['earn'] = number_format($value['earn'], 2, '.', '');
            }
            if ($value['lft'] >= $lft && $value['rgt'] <= $rgt) {
                $value['is_wallpaper'] = 1;
            } else {
                $value['is_wallpaper'] = 0;
            }
        }

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $results;
        $jTableResult['TotalRecordCount'] = 1000;

        return new JsonModel($jTableResult);

    }

    public function commentslistAction()
    {
        $startIndex = (int)$this->params()->fromQuery('jtStartIndex');
        $pageSize = (int)$this->params()->fromQuery('jtPageSize');
        $sorting = $this->params()->fromQuery('jtSorting');
        $filter_year = $this->params()->fromPost('filter_year', date("Y"));

        if (!isset($sorting)) {
            $sorting = ' comment_created_at desc';
        }
        $sql = "
    			select 
                comment_id,
                comment_target_id,
                comment_member_id,
                comment_parent_id,
                comment_text,
                comment_created_at,
                (select count(1) from reports_comment r where c.comment_id = r.comment_id ) cntreport,
                (select GROUP_CONCAT(distinct reported_by) from reports_comment r where c.comment_id = r.comment_id order by created_at desc ) as reportedby,
                  (
                  SELECT count(1) AS count FROM comments c2
                  where c2.comment_target_id <> 0 and c2.comment_member_id = c.comment_member_id and c2.comment_active = 1 
                  ) as cntComments,
                  m.created_at member_since,
                  m.username,
                  (select count(1) from project p where p.status=100 and p.member_id=m.member_id and p.type_id = 1 and p.is_deleted=0) cntProjects,
                  m.profile_image_url,
                  (select description from project p where p.type_id=0 and p.member_id = c.comment_member_id) aboutme
                from comments c
                join member m on c.comment_member_id = m.member_id and m.is_active = 1 and m.is_deleted = 0
                where c.comment_type=0
                and c.comment_active = 1 
                and DATE_FORMAT(c.comment_created_at, '%Y') = :filter_year
        	";

        $sql .= ' order by ' . $sorting;
        $sql .= ' limit ' . $pageSize;
        $sql .= ' offset ' . $startIndex;

        $comments = $this->baseRepository->fetchAll($sql, array('filter_year' => $filter_year));

        foreach ($comments as &$value) {
            $value['member_since'] = Util::printDateSince($value['member_since']);
            $value['comment_created_at'] = Util::printDateSince($value['comment_created_at']);
            $value['avatar'] = Util::Image(
                $value['profile_image_url'], array('width' => '200', 'height' => '200', 'crop' => 2)
            );
        }

        $sqlall = "	select count(1) 
                    from comments c 
                    join member m on c.comment_member_id = m.member_id and m.is_active = 1 and m.is_deleted = 0
					where c.comment_type=0
					and c.comment_active = 1 and DATE_FORMAT(c.comment_created_at, '%Y') = :filter_year";

        $reportsAll = $this->baseRepository->fetchRow($sqlall, array('filter_year' => $filter_year));

        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;
        $jTableResult['Records'] = $comments;
        $jTableResult['TotalRecordCount'] = array_pop($reportsAll);

        return new JsonModel($jTableResult);
    }


}