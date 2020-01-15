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
class AdsController extends Local_Controller_Action_DomainSwitch
{

    public function indexAction()
    {
        $this->_helper->layout->disableLayout();

        $file_id = $this->getParam('file_id');
        $file_type = $this->getParam('file_type');
        $file_name = $this->getParam('file_name');
        $file_size = $this->getParam('file_size');
        $projectId = $this->getParam('project_id');
        if ($this->hasParam('link_type')) {
            $linkType = $this->getParam('link_type');
        } else {
            $linkType = "download";
        }

        $this->view->link_type = $linkType;

        $memberId = $this->_authMember->member_id;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($file_id) && isset($projectId) && isset($memberId)) {
                $memberDlHistory = new Default_Model_DbTable_MemberDownloadHistory();
                $data = array(
                    'project_id' => $projectId,
                    'member_id'  => $memberId,
                    'file_id'    => $file_id,
                    'file_type'  => $file_type,
                    'file_name'  => $file_name,
                    'file_size'  => $file_size
                );
                $memberDlHistory->createRow($data)->save();
            }

            $modelProduct = new Default_Model_Project();
            $productInfo = $modelProduct->fetchProductInfo($projectId);

            //create ppload download hash: secret + collection_id + expire-timestamp
            $salt = PPLOAD_DOWNLOAD_SECRET;
            $collectionID = $productInfo->ppload_collection_id;
            $timestamp = time() + 3600; // one hour valid
            //20181009 ronald: change hash from MD5 to SHA512
            //$hash = md5($salt . $collectionID . $timestamp); // order isn't important at all... just do the same when verifying
            $hash = hash('sha512', $salt . $collectionID . $timestamp); // order isn't important at all... just do the same when verifying
            $url = PPLOAD_API_URI . 'files/download/id/' . $file_id . '/s/' . $hash . '/t/' . $timestamp . '/u/' . $memberId . '/' . $file_name;
            $url = Default_Model_PpLoad::createDownloadUrl($productInfo->ppload_collection_id,$file_name,array('id'=>$file_id, 'u'=>$memberId));

            if ($linkType == 'install') {
                $helperCatXdgType = new Default_View_Helper_CatXdgType();
                $xdgType = $helperCatXdgType->catXdgType($productInfo->project_category_id);

                $url = 'ocs://install'
                       . '?url=' . urlencode($url)
                       . '&type=' . urlencode($xdgType)
                       . '&filename=' . urldecode($file_name);
            }

            $this->view->url = $url;
        }

    }

}