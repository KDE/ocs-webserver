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
class NewproductsController extends Local_Controller_Action_DomainSwitch
{

    /** @var Default_Model_Project */
    protected $_model;
   
    protected $_modelName = 'Default_Model_Project';
    protected $_pageTitle = 'New Products';
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
        $this->view->headTitle('New Products','SET');
        $this->view->page = (int)$this->getParam('page', 1);        
    }

    public function listAction()
    {
    	$startIndex = (int)$this->getParam('jtStartIndex');
    	$pageSize = (int)$this->getParam('jtPageSize');
    	$sorting = $this->getParam('jtSorting');
    	if($sorting==null)
    	{
    		$sorting = 'cnt desc';
    	}
        $filterMonth= $this->getParam('filterMonth');
        $nonwallpaper = $this->getParam('nonwallpaper');
        if($filterMonth == null)
        {
            $now = new DateTime('now');
            $ymd = $now->format('Y-m');    
        }else
        {
            $ymd = DateTime::createFromFormat('Ym', $filterMonth)->format('Y-m');
        }
        
        $time_begin = $ymd.'-01 00:00:00';
        $time_end = $ymd.'-31 23:59:59';
        
        $sql="select 
                p.member_id, 
                m.username,
                count(1) as cnt ,
                m.created_at,
                (select count(1) from stat_projects pp where pp.member_id = p.member_id and pp.status=100 and pp.created_at < :time_begin) as cntOther
                from stat_projects p
                join stat_cat_tree t on p.project_category_id = t.project_category_id    
                join member m on p.member_id = m.member_id
                where p.status = 100                 
                ";      
         if($nonwallpaper==1)
         {
            $sql = $sql.' and (t.lft<975 or t.rgt>1068) ';
         }

         $sql = $sql.'  and p.created_at between :time_begin and :time_end
                        group by member_id';


         if(isset($sorting)){
            $sql = $sql.'  order by '.$sorting;
         }

         if (isset($pageSize)) {
             $sql .= ' limit ' . (int)$pageSize;
         }

         if (isset($startIndex)) {
             $sql .= ' offset ' . (int)$startIndex;
         }

      

        $resultSet = Zend_Db_Table::getDefaultAdapter()->fetchAll($sql, array( 'time_begin' => $time_begin, 'time_end'=>$time_end) );
        
        $sqlTotal = "select count(1) as cnt from(
                        select                         
                               p.member_id
                        from stat_projects p         
                        join stat_cat_tree t on p.project_category_id = t.project_category_id                           
                        where p.status = 100 
                        
          ";

        if($nonwallpaper==1)
        {
           $sqlTotal = $sqlTotal.' and (t.lft<975 or t.rgt>1068) ';
        }

        $sqlTotal = $sqlTotal.'  and p.created_at between :time_begin and :time_end
                       group by member_id 
                        ) t ';

        $resultTotal = Zend_Db_Table::getDefaultAdapter()->fetchRow($sqlTotal, array( 'time_begin' => $time_begin, 'time_end'=>$time_end) );

    	$totalRecordCount = $resultTotal['cnt'];    	
    	$jTableResult = array();
    	$jTableResult['Result'] = self::RESULT_OK;
    	$jTableResult['Records'] = $resultSet;
    	$jTableResult['TotalRecordCount'] = $totalRecordCount;
    	$this->_helper->json($jTableResult);
    }

  
      

}