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
 *
 * Created: 31.05.2017
 */

namespace Application\Controller;

use Application\Model\Repository\BaseRepository;
use Application\Model\Service\Util;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

/**
 * Class DuplicatesController
 *
 * @package Application\Controller
 */
class DuplicatesController extends BaseController
{

    const RESULT_OK = "OK";

    private $baseRepository;

    public function __construct(
        BaseRepository $baseRepository
    ) {
        parent::__construct();

        $this->baseRepository = $baseRepository;

    }

    public function indexAction()
    {

        $this->layout()->setTemplate('layout/flat-ui');

        return new ViewModel();
    }

    //TODO: revise this.  this is a security problem.
    public function listAction()
    {
        $startIndex = (int)$this->params()->fromQuery('jtStartIndex');
        $pageSize = (int)$this->params()->fromQuery('jtPageSize');
        $sorting = $this->params()->fromQuery('jtSorting');
        $filter_source_url = $this->params()->fromPost('filter_source_url');
        $filter_type = $this->params()->fromPost('filter_type');


        if ($sorting == null) {
            $sorting = 'cnt desc';
        }

        if ($filter_type == '1' || $filter_type == '2' || $filter_type == null) {
            // show duplicates
            $sql = "
            SELECT
            `source_url`
            ,count(1) AS `cnt`,
            GROUP_CONCAT(`p`.`project_id` ORDER BY `p`.`created_at`) `pids`
            FROM `stat_projects_source_url` `p`    
            ";
            if ($filter_type == '2' && $filter_source_url) {
                $sql .= " where source_url like '%" . $filter_source_url . "%'";
            }
            $sql .= " GROUP BY `source_url`
                HAVING count(1)>1
                ";

            $sqlTotal = "select count(1) as cnt from (" . $sql . ") as t";

            if (isset($sorting)) {
                $sql = $sql . '  order by ' . $sorting;
            }

            if (isset($pageSize)) {
                $sql .= ' limit ' . (int)$pageSize;
            }

            if (isset($startIndex)) {
                $sql .= ' offset ' . (int)$startIndex;
            }

            $reports = $this->baseRepository->fetchAll($sql);

            foreach ($reports as &$r) {
                $r['pids'] = Util::truncate($r['pids']);
            }
            $totalRecord = $this->baseRepository->fetchRow($sqlTotal);
            $totalRecordCount = $totalRecord['cnt'];

            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Records'] = $reports;
            $jTableResult['TotalRecordCount'] = $totalRecordCount;
            $jTableResult['sql'] = $sql;

            return new JsonModel($jTableResult);

        } else {
            if ($filter_type == '3') {
                $sql = "
                SELECT left(source_url, POSITION('" . $filter_source_url . "' IN source_url)) as source_url
                , count(1) AS `cnt`, GROUP_CONCAT(`p`.`project_id` ORDER BY `p`.`created_at`) `pids` 
                FROM `stat_projects_source_url` `p` 
                where source_url like '%" . $filter_source_url . "%' 
                GROUP BY left(source_url, POSITION('" . $filter_source_url . "' IN source_url))            
            ";

                $sqlTotal = "select count(1) as cnt from (" . $sql . ") as t";

                if (isset($sorting)) {
                    $sql = $sql . '  order by ' . $sorting;
                }

                if (isset($pageSize)) {
                    $sql .= ' limit ' . (int)$pageSize;
                }

                if (isset($startIndex)) {
                    $sql .= ' offset ' . (int)$startIndex;
                }

                $reports = $this->baseRepository->fetchAll($sql);
                $totalRecord = $this->baseRepository->fetchRow($sqlTotal);
                $totalRecordCount = $totalRecord['cnt'];

                $jTableResult = array();
                $jTableResult['Result'] = self::RESULT_OK;
                $jTableResult['Records'] = $reports;
                $jTableResult['TotalRecordCount'] = $totalRecordCount;
                $jTableResult['sql'] = $sql;
                $jTableResult['sqlTotal'] = $sqlTotal;

                return new JsonModel($jTableResult);
            }
        }

    }

}