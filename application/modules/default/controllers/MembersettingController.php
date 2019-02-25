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

class MembersettingController extends Zend_Controller_Action
{

	const GROUP_METAHEADER = 1;
	protected $_format = 'json';
	public function init()
    {
        parent::init();
        $this->initView();
        // $this->_initResponseHeader();
    }

    public function initView()
    {
        // Disable render view
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function indexAction()
    {
        $this->_sendErrorResponse(999, 'unknown request');
    }

    protected function _initResponseHeader()
    {
  //   	http_response_code(200);
  //       header('Access-Control-Allow-Origin: *', true);
  //       header('Access-Control-Allow-Credentials: true', true);
  //       header('Access-Control-Max-Age: 1728000', true);
  //       header('Access-Control-Allow-Methods: ' . implode(', ', array_unique([
  //           'OPTIONS', 'HEAD', 'GET', 'POST'])), true);
  //       header('Access-Control-Expose-Headers: Authorization, Content-Type, Accept', true);
		// header("Access-Control-Allow-Headers: X-Requested-With");

        http_response_code(200);
        if (!empty($_SERVER['HTTP_ORIGIN'])) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN'], true);
            header('Access-Control-Allow-Credentials: true', true);
            header('Access-Control-Max-Age: 1728000', true);
        }


        if (!empty($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header('Access-Control-Allow-Methods: ' . implode(', ', array_unique([
                'OPTIONS', 'HEAD', 'GET', 'POST','PUT',
                strtoupper($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])
            ])), true);
        }

        if (!empty($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            header('Access-Control-Allow-Headers: ' . $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'], true);
            header('Access-Control-Expose-Headers: Authorization, Content-Type, Accept', true);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit;
        }

        header('Content-Type: application/json; charset=UTF-8', true);

    }

    protected function _sendResponse($response, $format = 'json', $xmlRootTag = 'ocs')
    {

    	header('Content-Type: application/json; charset=UTF-8');
    	echo json_encode($response);

        // header('Pragma: public');
        // header('Cache-Control: cache, must-revalidate');
        // $duration = 1800; // in seconds
        // $expires = gmdate("D, d M Y H:i:s", time() + $duration) . " GMT";
        // header('Expires: ' . $expires);
        // $callback = $this->getParam('callback');
        // if ($callback != "")
        // {
        //     header('Content-Type: text/javascript; charset=UTF-8');
        //     // strip all non alphanumeric elements from callback
        //     $callback = preg_replace('/[^a-zA-Z0-9_]/', '', $callback);
        //     echo $callback. '('. json_encode($response). ')';
        // }else{
        //      header('Content-Type: application/json; charset=UTF-8');
        //      echo json_encode($response);
        // }
        // exit;
    }

    public function getsettingsAction()
    {
			$this->_initResponseHeader();
    	$identity = Zend_Auth::getInstance()->getStorage()->read();
    	if($identity==null || $identity->member_id==null)
    	{
    			$response = array(
    		            'status'     => 'error',
    		            'msg'	 => 'no user found'
    		        );
    			$this->_sendResponse($response, $this->_format);
    			return;
    	}
    	$model = new Default_Model_MemberSettingValue();
    	$member_id = $identity->member_id;

    	$results = $model->findMemberSettings($member_id,$this::GROUP_METAHEADER);
    	$response = array(
                'status'     => 'ok',
                'member_id'  => $member_id,
                'results'    => $results
            );
    	$this->_sendResponse($response, $this->_format);
    }


    public function setsettingsAction()
    {
			$this->_initResponseHeader();
    	$identity = Zend_Auth::getInstance()->getStorage()->read();
    	if($identity==null || $identity->member_id==null)
    	{
    			$response = array(
    		            'status'     => 'error',
    		            'msg'	 => 'no user found'
    		        );
    	}else
    	{
    		$model = new Default_Model_MemberSettingValue();
    		$member_id = $identity->member_id;
    		$member_setting_item_id = $this->getParam('itemid');
    		$value = $this->getParam('itemvalue');
    		$model->updateOrInsertSetting($member_id,$member_setting_item_id,null,$value);
    		$response = array(
                'status'     => 'ok'
            );
    	}
    	$this->_sendResponse($response, $this->_format);
    }


}
