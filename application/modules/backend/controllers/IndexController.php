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
class Backend_IndexController extends Local_Controller_Action_Backend
{
    

    public function indexAction()
    {

    }

    public function metaAction()
    {

    }

    public function settingsAction()
    {

    }

    public function filebrowserAction()
    {

    }

    public function getnewmemberstatsAction()
    {

        $this->_helper->layout->disableLayout();
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getNewmemberstats());
    }

    public function sendJson($result)
    {
        if ($result) {
            $msg = array(
                'status'  => 'ok',
                'msg'     => '',
                'results' => $result
            );

            return $this->_helper->json->sendJson($msg);
        } else {
            return $this->_helper->json->sendJson(array(
                'status'  => 'not found',
                'msg'     => 'data with given id could not be found.',
                'results' => ''
            ));
        }
    }

    public function getpayoutgroupbyamountAction()
    {
        $this->_helper->layout->disableLayout();
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->resources->toArray());
        $this->sendJson($modelData->getPayoutgroupbyamountProduct());
    }

    public function getpayoutgroupbyamountmemberAction()
    {
        $this->_helper->layout->disableLayout();
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->resources->toArray());
                
        $this->sendJson($modelData->getPayoutgroupbyamountMember());
    }

    public function getnewprojectstatsAction()
    {
        $this->_helper->layout->disableLayout();
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getNewprojectstats());
    }

    public function getpayoutAction()
    {

        $this->_helper->layout->disableLayout();
        $yyyymm = $this->getParam('yyyymm', '201708');
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->settings->dwh->toArray());
        $data = $modelData->getPayout($yyyymm);
        $m = new Default_Model_DbTable_ProjectPlings();
        $plings = $m->getAllPlingListReceived();
        foreach ($data as &$d) {
            $d['plings'] = 0;
            foreach ($plings as $p) {
                if($p['member_id'] == $d['member_id'])
                {
                    $d['plings'] = $p['plings'];
                    break;
                }
            }
        }
        $this->sendJson($data);
    }

    public function getdownloadsdailyAction()
    {

        $this->_helper->layout->disableLayout();
        $numofmonthback = $this->getParam('numofmonthback', '3');
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getDownloadsDaily($numofmonthback));
    }

    public function getdownloadsundpayoutsdailyAction()
    {

        $this->_helper->layout->disableLayout();
        $yyyymm = $this->getParam('yyyymm');
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getDownloadsUndPayoutsDaily($yyyymm));
    }

    public function gettopdownloadsperdateAction()
    {

        $this->_helper->layout->disableLayout();
        $date = $this->getParam('date');
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getTopDownloadsPerDate($date));
    }

    public function getproductmonthlyAction()
    {

        $this->_helper->layout->disableLayout();
        $project_id = $this->getParam('project_id');
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getProductMonthly($project_id));
    }

    public function getproductdaylyAction()
    {

        $this->_helper->layout->disableLayout();
        $project_id = $this->getParam('project_id');
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getProductDayly($project_id));
    }


    public function gettopdownloadspermonthAction()
    {

        $this->_helper->layout->disableLayout();
        $month = $this->getParam('month');
        $catid = $this->getParam('catid');
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getTopDownloadsPerMonth($month,$catid));
    }

    public function getdownloadsdomainAction()
    {

        $this->_helper->layout->disableLayout();
        $dateBegin = $this->getParam('dateBegin');
        $dateEnd = $this->getParam('dateEnd');
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getDownloadsDomainStati($dateBegin, $dateEnd));
    }
 
    public function getpayoutmemberAction()
    {

        $this->_helper->layout->disableLayout();
        $member = $this->getParam('member', '1');
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getPayoutOfMember($member));
    }

    public function getpayoutyearAction()
    {
        $this->_helper->layout->disableLayout();
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getPayoutyear());
    }

    public function getpayoutcategorymonthlyAction()
    {
        $this->_helper->layout->disableLayout();
        $yyyymm = $this->getParam('yyyymm', '201708');
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getPayoutCategoryMonthly($yyyymm));
    }

    public function getpayoutmemberpercategoryAction()
    {
        $this->_helper->layout->disableLayout();
        $yyyymm = $this->getParam('yyyymm', '201708');
        $catid = (int)$this->getParam('catid', 0);
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getPayoutMemberPerCategory($yyyymm,$catid));
    }

    public function getpayoutcategoryAction()
    {   

        $this->_helper->layout->disableLayout();
        $catid = (int)$this->getParam('catid', 0);
        
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->settings->dwh->toArray());

        $result = $modelData->getPayoutCategory($catid);
        if($catid==0)
        {
            $modelCategoryStore = new Default_Model_DbTable_ConfigStoreCategory();
            $pids = $modelCategoryStore->fetchCatIdsForStore(Statistics_Model_Data::DEFAULT_STORE_ID);
        }else{
            $modelCategoriesTable = new Default_Model_DbTable_ProjectCategory();
            $pids = $modelCategoriesTable->fetchImmediateChildrenIds($catid,$modelCategoriesTable::ORDERED_TITLE);
        }
        
        if($pids)
        {
            $modelCategories = new Default_Model_ProjectCategory();
           $pidsname = $modelCategories->fetchCatNamesForID($pids);    
        }else
        {
            $pidsname=[];
        }
        

        $msg = array(
            'status'  => 'ok',
            'msg'     => '',
            'pids'     => $pids,
            'pidsname' => $pidsname,
            'results' => $result
        );
        return $this->_helper->json->sendJson($msg);

        //$this->sendJson($modelData->getPayoutCategory($catid));
    }

    public function newcomerAction()
    {
        $this->_helper->layout->disableLayout();
        $yyyymm = $this->getParam('yyyymm', '1');
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getNewcomer($yyyymm));
    }

    public function newloserAction()
    {
        $this->_helper->layout->disableLayout();
        $yyyymm = $this->getParam('yyyymm', '1');
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getNewloser($yyyymm));
    }

    public function monthdiffAction()
    {
        $this->_helper->layout->disableLayout();
        $yyyymm = $this->getParam('yyyymm', '1');
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getMonthDiff($yyyymm));
    }


    public function newproductsweeklyAction()
    {
        $this->_helper->layout->disableLayout();
        // $yyyymm = $this->getParam('yyyymm', '1');
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->settings->dwh->toArray());
        // $list = array_reverse($modelData->getNewprojectWeeklystats());
        // $this->sendJson($list);

        $listwallpapers = $modelData->getNewprojectWeeklystatsWallpapers();
        $listNowallpapers = $modelData->getNewprojectWeeklystatsWithoutWallpapers();

        $map1 = array();
        foreach($listwallpapers as $value) {          
            $map1[$value['yyyykw']] = $value['amount'];
        }

        $map2 = array();
        foreach($listNowallpapers as $value) {          
            $map2[$value['yyyykw']] = $value['amount'];
        }

        $datetime = new DateTime();
        $result = array();
        for ($i = 1; $i < 60; $i++) {
            $w = '-1 week';
            $datetime->modify($w);    
            $month = $datetime->format('YW');

            $value1=0;
            if(isset($map1[$month])){
                $value1 = $map1[$month];
            }
            $value2=0;
            if(isset($map2[$month])){
                $value2 = $map2[$month];
            }
            $result[] = array('yyyykw'=>$month,
                            'amountnowallpapers'=>$value2,
                            'amountwallpapers' =>$value1
                            );
        }
        $list = array_reverse($result);
        $this->sendJson($list);        

        // $this->_helper->json($list);
       
    }

    public function getnewmembersprojectsAction()
    {

        $this->_helper->layout->disableLayout();
        $modelData = new Statistics_Model_Data(Zend_Registry::get('config')->settings->dwh->toArray());

        try {
            $result = array();

            $m = $modelData->getNewmemberstats();
            $p = $modelData->getNewprojectstats();
            foreach ($m as $member) {
                $date = $member['memberdate'];
                $t = array(
                    'date'     => $date,
                    'members'  => $member['daycount'],
                    'projects' => 0
                );
                foreach ($p as $project) {
                    $d = $project['projectdate'];
                    if ($d == $date) {
                        $t['projects'] = $project['daycount'];
                        break;
                    }
                }
                $result [] = $t;
            }
        } catch (Exception $e) {
            Zend_Registry::get('logger')->error($e->getMessage());

            return $this->_helper->json->sendJson(array(
                'status'  => 'error',
                'msg'     => 'error while processing request',
                'results' => ''
            ));
        }

        if ($result) {
            $msg = array(
                'status'  => 'ok',
                'msg'     => '',
                'results' => array_reverse($result)
            );

            return $this->_helper->json->sendJson($msg);
        }

        return $this->_helper->json->sendJson(array(
            'status'  => 'not found',
            'msg'     => 'data with given id could not be found.',
            'results' => ''
        ));
    }

}