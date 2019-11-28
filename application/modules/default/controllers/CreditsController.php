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
class CreditsController extends Local_Controller_Action_DomainSwitch
{

    public function indexAction()
    {
       	$this->view->page = (int)$this->getParam('page', 1);
    }

    public function deleteAction()
    {
       	$this->_helper->layout->disableLayout();
       	$id =  (int)$this->getParam('id');
       	$m = new Default_Model_ProjectClone();
       	$m->setDelete($id);
       	$this->_helper->json(array(
       	    'status'  => 'ok',
       	    'message' => 'deleted',
       	    'data'    => array()
       	));

    }

     public function validAction()
    {
       	$this->_helper->layout->disableLayout();
	 $id =  (int)$this->getParam('id');
	 $m = new Default_Model_ProjectClone();
       	$m->setValid($id);
       	$this->_helper->json(array(
       	    'status'  => 'ok',
       	    'message' => 'validated',
       	    'data'    => array()
       	));
       	
    }

     public function editAction()
    {
        $this->_helper->layout->disableLayout();
      	$id =  (int)$this->getParam('id'); 
      	$text =$this->getParam('t');
      	$project_id =  (int)$this->getParam('p'); // cloneID
      	$link =  $this->getParam('l');
      	$m = new Default_Model_ProjectClone();
		
		$arr = array( 'text' => $text
				, 'project_id' =>$project_id				  
			);
		if($link){
			$arr['external_link'] = $link;
		}
	    $m->update($arr, 'project_clone_id='.$id);                             
	
       	$this->_helper->json(array(
       	    'status'  => 'ok',
       	    'message' => 'updated',
       	    'data'    => array()
       	));
       	
    }

	public function modsAction()
	{
		$this->view->headTitle('Modifications','SET');
		$this->view->page = (int)$this->getParam('page', 1);
	}

 
}