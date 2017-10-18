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
        $modelData = new Statistics_Model_Data( Zend_Registry::get('config')->settings->dwh->toArray());
         $this->sendJson($modelData->getNewmemberstats());   
        
    }

    public function getnewprojectstatsAction()
    {
        $this->_helper->layout->disableLayout();        
               $modelData = new Statistics_Model_Data( Zend_Registry::get('config')->settings->dwh->toArray());
               $this->sendJson($modelData->getNewprojectstats());   
             
    }

    public function getpayoutAction()
    {

        $this->_helper->layout->disableLayout();        
        $yyyymm =$this->getParam('yyyymm', '201708');
        $modelData = new Statistics_Model_Data( Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getPayout($yyyymm));              
        
    }

    public function getdownloadsdailyAction()
    {

        $this->_helper->layout->disableLayout();          
        $numofmonthback =$this->getParam('numofmonthback', '3');              
        $modelData = new Statistics_Model_Data( Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getDownloadsDaily($numofmonthback));              
        
    }
     public function getdownloadsundpayoutsdailyAction()
    {

        $this->_helper->layout->disableLayout();          
        $yyyymm =$this->getParam('yyyymm');              
        $modelData = new Statistics_Model_Data( Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getDownloadsUndPayoutsDaily($yyyymm));              
        
    }

    public function gettopdownloadsperdateAction()
    {

        $this->_helper->layout->disableLayout();          
        $date =$this->getParam('date');              
        $modelData = new Statistics_Model_Data( Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getTopDownloadsPerDate($date));              
        
    }
    

    public function getpayoutmemberAction()
    {

        $this->_helper->layout->disableLayout();        
        $member =$this->getParam('member', '1');
        $modelData = new Statistics_Model_Data( Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getPayoutOfMember($member));              
    }

    public function getpayoutyearAction()
    {
        $this->_helper->layout->disableLayout();                
        $modelData = new Statistics_Model_Data( Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getPayoutyear());      
    }


    public function getpayoutcategorymonthlyAction(){
        $this->_helper->layout->disableLayout();               
        $yyyymm =$this->getParam('yyyymm', '201708'); 
        $modelData = new Statistics_Model_Data( Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getPayoutCategoryMonthly($yyyymm));      
    }



      public function newcomerAction()
    {
        $this->_helper->layout->disableLayout();        
        $yyyymm =$this->getParam('yyyymm', '1');        
        $modelData = new Statistics_Model_Data( Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getNewcomer($yyyymm));      
    }
      public function newloserAction()
    {
        $this->_helper->layout->disableLayout();        
        $yyyymm =$this->getParam('yyyymm', '1');        
        $modelData = new Statistics_Model_Data( Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getNewloser($yyyymm));      
    }

    public function monthdiffAction()
    {
        $this->_helper->layout->disableLayout();        
        $yyyymm =$this->getParam('yyyymm', '1');        
        $modelData = new Statistics_Model_Data( Zend_Registry::get('config')->settings->dwh->toArray());
        $this->sendJson($modelData->getMonthDiff($yyyymm));      
    }

    public function sendJson($result){
      if ($result) {
          $msg = array(
              'status' => 'ok',
              'msg' => '',
              'results' =>$result
          );       
          return $this->_helper->json->sendJson($msg);
      }else{
        return $this->_helper->json->sendJson(array(
            'status' => 'not found',
            'msg' => 'data with given id could not be found.',
            'results' => ''
        ));
      }
    }

    public function getnewmembersprojectsAction()
    {

        $this->_helper->layout->disableLayout();        
        $modelData = new Statistics_Model_Data( Zend_Registry::get('config')->settings->dwh->toArray());
        
        try {
            $result =array();

            $m = $modelData->getNewmemberstats();
            $p = $modelData->getNewprojectstats();
            foreach ($m as $member) {
                $date = $member['memberdate'];
                $t=array(
                        'date' =>$date,
                        'members' =>$member['daycount'],
                        'projects' =>0
                   );
                   foreach ($p as $project) {
                        $d = $project['projectdate'];
                        if($d==$date){
                            $t['projects'] = $project['daycount'];
                            break;
                        }
                   }
                   $result []=$t; 
            }


        } catch (Exception $e) {
            Zend_Registry::get('logger')->error($e->getMessage());
            return $this->_helper->json->sendJson(array(
                'status' => 'error',
                'msg' => 'error while processing request',
                'results' => ''
            ));
        }
       
        if ($result) {
            $msg = array(
                'status' => 'ok',
                'msg' => '',
                'results' =>array_reverse($result)
            );
         
            return $this->_helper->json->sendJson($msg);
        }
        return $this->_helper->json->sendJson(array(
            'status' => 'not found',
            'msg' => 'data with given id could not be found.',
            'results' => ''
        ));
    }

}