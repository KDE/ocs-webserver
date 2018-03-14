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
class TagController extends Zend_Controller_Action
{

    public function addAction()
    {
        $this->_helper->layout()->disableLayout();

        $label = Default_Model_HtmlPurify::purify($this->getParam('t'));

        $this->_helper->json(array(
            'status'  => 'ok',
            'message' => '',
            'data'    => array('id' => 123456, 'label' => $label)
        ));
    }

    public function delAction()
    {
        $this->_helper->layout()->disableLayout();

        $id = (int) $this->getParam('ti');

        $this->_helper->json(array(
            'status'  => 'ok',
            'message' => '',
            'data'    => array('id' => $id)
        ));
    }

    public function assignAction()
    {
        $this->_helper->layout()->disableLayout();

        $objectId = (int) $this->getParam('oid');
        $objectType = (int) $this->getParam('ot', 10);
        $tag = Default_Model_HtmlPurify::purify($this->getParam('tag'));

        $this->_helper->json(array(
            'status'  => 'ok',
            'message' => '',
            'data'    => array('oid' => $objectId, 'tag' => $tag, 'type' => $objectType)
        ));
    }

    public function removeAction()
    {
        $this->_helper->layout()->disableLayout();

        $objectId = (int) $this->getParam('oid');
        $objectType = (int) $this->getParam('ot', 10);
        $tag = Default_Model_HtmlPurify::purify($this->getParam('tag'));

        $this->_helper->json(array(
            'status'  => 'ok',
            'message' => '',
            'data'    => array('oid' => $objectId, 'tag' => $tag, 'type' => $objectType)
        ));
    }

    public function filterAction()
    {
         $this->_helper->layout()->disableLayout();  
         $model = new Default_Model_Tags();
         $filter = $this->getParam('q');         
         $tags  = $model->filterTagsUser($filter,10);
         $this->_helper->json(array(
             'status'  => 'ok',
             'message' => '',
             'filter'=>$filter,
             'data'    => array('tags' => $tags)
         ));
    }
}