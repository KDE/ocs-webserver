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
class DuplicatesController extends Local_Controller_Action_DomainSwitch
{

    /** @var Default_Model_Project */
    protected $_model;
   
    protected $_modelName = 'Default_Model_Project';
    protected $_pageTitle = 'Duplicates source_url';
    const RESULT_OK = "OK";
    const RESULT_ERROR = "ERROR";
    public function init()
    {
        $this->_model = new $this->_modelName();
        $this->view->pageTitle = $this->_pageTitle;
        parent::init();
    }


    public function indexAction()
    {
        $this->view->headTitle('Duplicates','SET');
        $this->view->page = (int)$this->getParam('page', 1);        
    }

    public function listAction()
    {
    	$startIndex = (int)$this->getParam('jtStartIndex');
    	$pageSize = (int)$this->getParam('jtPageSize');
        $sorting = $this->getParam('jtSorting');        
        $filter_source_url = $this->getParam('filter_source_url');
        $filter_type = $this->getParam('filter_type');
        
        if($sorting==null)
    	{
    		$sorting = 'cnt desc';
    	}
        
        if($filter_type=='1' || $filter_type=='2' || $filter_type ==null)
        {
            // show duplicates
            $sql = "
            SELECT
            `source_url`
            ,count(1) AS `cnt`,
            GROUP_CONCAT(`p`.`project_id` ORDER BY `p`.`created_at`) `pids`
            FROM `stat_projects_source_url` `p`    
            ";
            if($filter_type=='2' && $filter_source_url)
            {
                $sql.=" where source_url like '%".$filter_source_url."%'";
            }
            $sql .=" GROUP BY `source_url`
                HAVING count(1)>1
                ";
            
            $sqlTotal = "select count(1) as cnt from (".$sql.") as t";

            if (isset($sorting)) {
                $sql = $sql . '  order by ' . $sorting;
            }
    
            if (isset($pageSize)) {
                $sql .= ' limit ' . (int)$pageSize;
            }
    
            if (isset($startIndex)) {
                $sql .= ' offset ' . (int)$startIndex;
            }

            $reports = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);   
            $helperTruncate = new Default_View_Helper_Truncate();   
            foreach ($reports as &$r) {                    
                    $r['pids'] = $helperTruncate->truncate($r['pids']);
                }
            $totalRecord = Zend_Db_Table::getDefaultAdapter()->fetchRow($sqlTotal);
            $totalRecordCount = $totalRecord['cnt'];

            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Records'] = $reports;
            $jTableResult['TotalRecordCount'] = $totalRecordCount;
            $jTableResult['sql'] = $sql;            
            $this->_helper->json($jTableResult);
        }else if($filter_type=='3')
        {
            $sql = "
            SELECT
            `source_url`
            ,count(1) AS `cnt`,
            GROUP_CONCAT(`p`.`project_id` ORDER BY `p`.`created_at`) `pids`
            FROM `stat_projects_source_url` `p`    
            ";
            if($filter_source_url)
            {
                $sql.=" where source_url like '%".$filter_source_url."%'";
            }
            $sql .=" GROUP BY `source_url`               
                ";
            
            $sqlTotal = "select count(1) as cnt from (".$sql.") as t";

            if (isset($sorting)) {
                $sql = $sql . '  order by ' . $sorting;
            }
    
            if (isset($pageSize)) {
                $sql .= ' limit ' . (int)$pageSize;
            }
    
            if (isset($startIndex)) {
                $sql .= ' offset ' . (int)$startIndex;
            }

            $reports = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);               
            $totalRecord = Zend_Db_Table::getDefaultAdapter()->fetchRow($sqlTotal);
            $totalRecordCount = $totalRecord['cnt'];

            $jTableResult = array();
            $jTableResult['Result'] = self::RESULT_OK;
            $jTableResult['Records'] = $reports;        
            $jTableResult['TotalRecordCount'] = $totalRecordCount;
            $jTableResult['sql'] = $sql;            
            $jTableResult['sqlTotal'] = $sqlTotal;     
            $this->_helper->json($jTableResult);
        }
    	        
    //     $sql = "
    //         SELECT
    //         `source_url`
    //         ,count(1) AS `cnt`,
    //         GROUP_CONCAT(`p`.`project_id` ORDER BY `p`.`created_at`) `pids`
    //         FROM `stat_projects_source_url` `p`    
    //    ";

    //     if($filter_source_url)
    //     {
    //         $sql.=" where source_url like '%".$filter_source_url."%'";
    //     }

    //     $sql .=" GROUP BY `source_url`
    //             HAVING count(1)>1
    //             ";

    //     $sqlTotal = "select count(1) as cnt from (".$sql.") as t";

    //     if (isset($sorting)) {
    //         $sql = $sql . '  order by ' . $sorting;
    //     }

    //     if (isset($pageSize)) {
    //         $sql .= ' limit ' . (int)$pageSize;
    //     }

    //     if (isset($startIndex)) {
    //         $sql .= ' offset ' . (int)$startIndex;
    //     }
     
    //     $reports = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql);    

    //     $helperTruncate = new Default_View_Helper_Truncate();   
    //     foreach ($reports as &$r) {                    
    //                 $r['pids'] = $helperTruncate->truncate($r['pids']);
    //             }
                
    //     // $totalRecordCount = $mod->getTotalCountDuplicates();
        
    //     $totalRecord = Zend_Db_Table::getDefaultAdapter()->fetchRow($sqlTotal);
    //     $totalRecordCount = $totalRecord['cnt'];
        
    // 	$jTableResult = array();
    // 	$jTableResult['Result'] = self::RESULT_OK;
    // 	$jTableResult['Records'] = $reports;
    //     $jTableResult['TotalRecordCount'] = $totalRecordCount;
    //     $jTableResult['sql'] = $sql;        
    // 	$this->_helper->json($jTableResult);
    }

  
      

}