<?php

/**
 *
 *   ocs-apiserver
 *
 *   Copyright 2016 by pling GmbH.
 *
 *    This file is part of ocs-apiserver.
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
 */
class Portal_UserController extends Local_Controller_Action_Portal
{
    protected $_memberId;
    protected $_userName;
    
    
    public function indexAction()
    {
        if ($this->hasParam('user_name')) {
            $this->_userName = $this->getParam('user_name');
            $this->_userName = urldecode($this->_userName);

            $modelMember = new Default_Model_Member();
            $this->_memberId = $modelMember->fetchActiveUserByUsername($this->_userName);
        } else {
            $this->_memberId = (int)$this->getParam('member_id');
        }
        
        $tableMember = new Default_Model_Member();
        $tableProject = new Default_Model_Project();
        $earnModel = new Default_Model_StatDownload();
        $helperUserRole = new Backend_View_Helper_UserRole();
        $pageLimit = 500;

        $this->view->authMember = $this->_authMember;
        $this->view->member = $tableMember->fetchMemberData($this->_memberId);
        
        if (null == $this->view->member) {
            $this->redirect("/");
        }
        if ($this->view->member->is_deleted == 1 or $this->view->member->is_active == 0) {
            $this->redirect("/");
        }

        $this->view->headTitle($this->view->member->username, 'SET');
        $this->view->mainProject = $this->view->member->findDependentRowset($tableProject, 'MainProject')->current();

        $this->view->userProjectCategories = $tableProject->getUserCreatingCategorys($this->_memberId);
        $this->view->aboutmeUserInfo = $this->getAboutmeUserInfo($this->_memberId, $this->view->member->username);
    }
    
    
    public function getAboutmeUserInfo($member_id, $username)
    {
        $tableProject = new Default_Model_Project();
        $userProjectCategories = $tableProject->getUserCreatingCategorys($member_id);
        $cnt = sizeof($userProjectCategories);
        $userinfo = '';
        $isAdmin = false;
        $helperUserRole = new Backend_View_Helper_UserRole();
        $userRoleName = $helperUserRole->userRole();
        if (Default_Model_DbTable_MemberRole::ROLE_NAME_ADMIN == $userRoleName) {
            $isAdmin = true;
        }
        if ($cnt > 0) {
            $userinfo = "Hi, I am <b>" . $username . "</b> and I create ";
            if ($cnt == 1) {
                $userinfo = $userinfo . ' <b>' . $userProjectCategories[0]['category1'] . '</b>.';                
            }elseif($cnt == 2) {                
                $userinfo = $userinfo . ' <b>' . $userProjectCategories[0]['category1'] . '</b>'
                            . ' and <b>' . $userProjectCategories[1]['category1'] . '</b>.';                           
            }elseif($cnt == 3) {                     
                $userinfo = $userinfo . ' <b>' . $userProjectCategories[0]['category1'] . '</b>'                        
                            . ', <b>' . $userProjectCategories[1]['category1'] . '</b>'
                            . ' and <b>' . $userProjectCategories[2]['category1'] . '</b>.';                        
            }else{                        
                $userinfo = $userinfo . ' <b>' . $userProjectCategories[0]['category1'] . '</b>'
                            . ', <b>' . $userProjectCategories[1]['category1'] . '</b>'
                            . ', <b>' . $userProjectCategories[2]['category1'] . '</b>'
                            . ' and more.';                        
            }         
        }else{
            $userinfo = "Hi, I am <b>" . $username . "</b>.";
        }

        $mModel = new Default_Model_Member();
        $supportSections = $mModel->fetchSupporterSectionInfo($member_id);
        if($supportSections && $supportSections['sections'])
        {
            $userinfo = $userinfo." I ".($cnt==0?" ":" also ")."support";
            $sections = explode(",", $supportSections['sections']);
            foreach ($sections as $s) {
                $userinfo.=" <b>".$s."</b>, ";
            }
            
            $userinfo = trim($userinfo);
            $userinfo = substr($userinfo, 0, -1);
        }


        
         if(substr($userinfo, strlen($userinfo)-1) <> ".")
         {
            $userinfo.=".";          
         }
        return $userinfo;
    }

}