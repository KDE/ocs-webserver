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
class DiscoveryController extends Zend_Controller_Action
{

    /**
     * @var int project_id
     */
    protected $project_id;

    /**
     * @param $project_id
     */
    public function init($project_id)
    {
        parent::init();
        $this->project_id = $project_id;
        $this->_discovery = new Default_Model_Discovery();
        /*
        $this->_helper->contextSwitch()
             ->addActionContext('index', 'json')
             ->addActionContext('youtube', 'json')
             ->addActionContext('general', 'json')
             ->initContext();*/
        //$this->_helper->layout->disableLayout();
    }

    public function indexAction()
    {
        $this->_helper->layout->disableLayout();
        if ($this->_request->isPost() && $this->getParam('next')) {


            $url = $_REQUEST['url'];
            try {
                $data = $this->_discovery->guessGeneralData($url);
                $form = $this->getForm($data);
            } catch (Exception $e) {
                $form = $this->getForm(array());
            }
            $this->view->form = $form;

        }
    }

    /**
     * @param $data
     * @return Zend_Form
     */
    private function getForm($data)
    {
        $form = new Zend_Form();
        $helperBuildProductUrl = new Default_View_Helper_BuildProductUrl();
        $form->setAction($helperBuildProductUrl->buildProductUrl($this->project_id, 'additem'));
        return $form;
    }

    public function generalAction()
    {
        $url = $_REQUEST['url'];
        $result = $this->_discovery->getGeneralData($url);
        $response = array(
            'result' => true,
            'message' => 'Do you like this content?',
            'data' => $result
        );
        $this->view->response = $response;
    }

    public function youtubeAction()
    {
        $url = $_REQUEST['url'];
        $code = $this->_discovery->getYoutubeCode($url);
        if ($code) {
            try {
                $data = $this->_discovery->getYoutubeData($code);
                $response = array(
                    'result' => true,
                    'message' => 'This looks like a youtube video',
                    'data' => $data
                );
            } catch (Exception $e) {
                $response = false;
            }
        } else {
            $response = false;
        }
        if (!$response) {
            $response = array(
                'result' => false,
                'message' => 'This does not look like a youtube video'
            );
        }
        $this->view->response = $response;
    }
}