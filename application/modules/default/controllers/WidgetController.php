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
class WidgetController extends Zend_Controller_Action
{

    public function indexAction()
    {
        $this->_helper->viewRenderer('render');
        $this->renderAction();
    }

    public function renderAction()
    {
        $this->_helper->layout->disableLayout();
        $widgetProjectId = (int)$this->getParam('project_id');
        $userConfig = $this->getParamUserConfig();

        if (false == isset($widgetProjectId)) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }

        $widgetDefaultModel = new Default_Model_DbTable_ProjectWidgetDefault();
        $widgetDefault = $widgetDefaultModel->fetchConfig($widgetProjectId);

        $widgetConfig = $this->mergeConfig($userConfig, $widgetDefault);

        $this->view->widgetConfig = $widgetConfig;
        $productModel = new Default_Model_Project();
        $this->view->project = $productModel->fetchProductInfo($widgetProjectId);
        if (is_null($this->view->project)) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }
        $this->view->supporting = $productModel->fetchProjectSupporterWithPlings($widgetProjectId);
        $plingModel = new Default_Model_DbTable_Plings();
        $this->view->comments = $plingModel->getCommentsForProject($widgetProjectId, 10);
        $websiteOwner = new Local_Verification_WebsiteAuthCodeExist();
        $this->view->authCode = '<meta name="ocs-site-verification" content="' . $websiteOwner->generateAuthCode(stripslashes($this->view->project->link_1)) . '" />';
        $this->view->paymentProvider = true;
        if (empty($this->view->project->paypal_mail) && empty($this->view->project->dwolla_id)) :
            $this->view->paymentProvider = false;
        endif;
    }

    private function getParamUserConfig()
    {
        $params = $this->getAllParams();

        if (!isset($params['ssu']) OR !isset($params['sco']) OR !isset($params['sda'])) {
            return null;
        }

        $userConfig = new stdClass();
        $userConfig->colors = new stdClass();
        $userConfig->colors->widgetBg = $params['wbg'];
        $userConfig->colors->widgetContent = $params['wco'];
        $userConfig->colors->headline = $params['whe'];
        $userConfig->colors->text = $params['wte'];
        $userConfig->colors->button = $params['wbu'];
        $userConfig->colors->buttonText = $params['wbt'];

        $userConfig->showSupporters = $params['ssu'] == 'true' ? true : false;
        $userConfig->showComments = $params['sco'] == 'true' ? true : false;
        $userConfig->logo = $params['log'];
        $userConfig->amounts = new stdClass();
        $userConfig->amounts->showDonationAmount = $params['sda'] == 'true' ? true : false;

        return $userConfig;
    }

    /**
     * @param object $userConfig
     * @param object $widgetDefault
     * @return object
     */
    protected function mergeConfig($userConfig, $widgetDefault)
    {
        if (false == is_null($userConfig)) {
            $widgetConfig = (object)array_merge((array)$widgetDefault, (array)$userConfig);
            $widgetConfig->text = $widgetDefault->text;
            $widgetConfig->amounts = (object)array_merge((array)$widgetDefault->amounts, (array)$userConfig->amounts);
            return $widgetConfig;
        } else {
            $widgetConfig = $widgetDefault;
            return $widgetConfig;
        }
    }

    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
        $body = $this->getRequest()->getRawBody();
        $data = json_decode($body, true);

        //TODO: Filter Input !!!

        $modelProjectWidget = new Default_Model_DbTable_ProjectWidget();

        try {
            $result = $modelProjectWidget->save(array('uuid' => $data['uuid'], 'project_id' => (int)$data['project'], 'config' => json_encode($data['config'])));

            $embedCode = $this->view->externalWidgetSource((int)$data['project']);

            $this->_helper->json(array('status' => 'ok', 'data' => array('uuid' => $result->uuid, 'embedCode' => $embedCode, 'config' => $result->config)));
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(500);
            $this->_helper->json(array('status' => 'error', 'data' => array('uuid' => '', 'embedCode' => '', 'config' => $result->config)));
        }
    }

    public function savedefaultAction()
    {
        $this->_helper->layout->disableLayout();
        $body = $this->getRequest()->getRawBody();
        $data = json_decode($body, true);

        //TODO: Filter Input !!!

        $modelProjectWidget = new Default_Model_DbTable_ProjectWidgetDefault();

        try {
            $result = $modelProjectWidget->save(array('uuid' => $data['uuid'], 'project_id' => (int)$data['project'], 'config' => json_encode($data['config'])));

            $externalWidget = new Default_View_Helper_ExternalWidgetSource();
            $embedCode = $this->view->externalWidgetSource((int)$data['project']);

            $this->_helper->json(array('status' => 'ok', 'data' => array('uuid' => $result->uuid, 'embedCode' => $embedCode, 'config' => $result->config)));
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(500);
            $this->_helper->json(array('status' => 'error', 'data' => array('uuid' => '', 'embedCode' => '', 'config' => $result->config)));
        }
    }

    public function configAction()
    {
        $this->_helper->layout->disableLayout();
        $widgetProjectId = (int)$this->getParam('project_id');

        if (false == isset($widgetProjectId)) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }

        $widgetDefaultModel = new Default_Model_DbTable_ProjectWidgetDefault();

        $this->view->widgetConfig = $widgetDefaultModel->fetchConfig($widgetProjectId);
        $productModel = new Default_Model_Project();
        $this->view->product = $productModel->fetchProductInfo($widgetProjectId);
        if (is_null($this->view->product)) {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }
        $this->view->supporting = $productModel->fetchProjectSupporterWithPlings($widgetProjectId);
        $plingModel = new Default_Model_DbTable_Plings();
        $this->view->comments = $plingModel->getCommentsForProject($widgetProjectId, 10);
        $websiteOwner = new Local_Verification_WebsiteAuthCodeExist();
        $this->view->authCode = '<meta name="ocs-site-verification" content="' . $websiteOwner->generateAuthCode(stripslashes($this->view->product->link_1)) . '" />';

        $supportersArray = array();
        $helperBuildMemberUrl = new Default_View_Helper_BuildMemberUrl();
        foreach ($this->view->supporting as $supporter) {
            $new_supporter = new stdClass();
            $new_supporter->username = $supporter->username;
            $new_supporter->img = $supporter->profile_image_url;
            $new_supporter->url = $helperBuildMemberUrl->buildExternalUrl($supporter->member_id);
            array_push($supportersArray, $new_supporter);
        }
        $commentsArray = array();
        foreach ($this->view->comments as $comment) {
            $new_comment = new stdClass();
            $new_comment->username = $comment->username;
            $new_comment->img = $comment->profile_image_url;
            $new_comment->amount = $comment->amount;
            $new_comment->comment = $comment->comment;
            array_push($commentsArray, $new_comment);
        }

        $result = array();
        $result['text'] = array(
            'content' => $this->view->widgetConfig->text->content,
            'headline' => $this->view->widgetConfig->text->headline,
            'button' => 'Pling it!'
        );
        $result['amounts'] = array(
            'donation' => $this->view->widgetConfig->amounts->donation,
            'showDonationAmount' => $this->view->widgetConfig->amounts->showDonationAmount,
            'current' => (float)$this->view->product->amount_received,
            'goal' => (false === empty($this->view->product->amount)) ? $this->view->product->amount : ''
        );
        $result['colors'] = array(
            'widgetBg' => $this->view->widgetConfig->colors->widgetBg,
            'widgetContent' => $this->view->widgetConfig->colors->widgetContent,
            'headline' => $this->view->widgetConfig->colors->headline,
            'text' => $this->view->widgetConfig->colors->text,
            'button' => $this->view->widgetConfig->colors->button,
            'buttonText' => $this->view->widgetConfig->colors->buttonText
        );
        $result['showSupporters'] = $this->view->widgetConfig->showSupporters ? 'true' : 'false';
        $result['supporters'] = $supportersArray;

        $result['showComments'] = $this->view->widgetConfig->showComments ? 'true' : 'false';
        $result['comments'] = $commentsArray;

        $result['logo'] = $this->view->widgetConfig->logo;

        $result['project'] = $this->view->product->project_id;

        $result['uuid'] = $this->view->widgetConfig->uuid;

        $result['embedCode'] = $this->view->externalWidgetSource($this->view->product->project_id);

        $this->_helper->json(array('status' => 'ok', 'message' => $result));
    }

}