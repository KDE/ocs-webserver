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
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Backend_ContentController extends Local_Controller_Action_Backend
{

    private $_contentTable;

    public function init()
    {
        $this->_contentTable = new Default_Model_DbTable_Content();
        parent::init();
    }

    public function indexAction()
    {
        $selCon = $this->_contentTable->select()->setIntegrityCheck(false);

        $selCon->from($this->_contentTable)
            ->where('is_deleted=0');


        $paginator = Zend_Paginator::factory($selCon);
        $paginator->setCurrentPageNumber($this->getParam('page'));

        $this->view->paginator = $paginator;
    }

    public function addAction()
    {
        if ($this->_request->isPost()) {
            // is Post-Request - das Formular
            $form = $this->getForm();
            if ($form->isValid($_POST)) {
                //alles ok
                $values = $form->getValues();

                $insertValues = array(
                    'title' => $values['title_intern'],
                    'url_name' => $values['url_name'],
                    'content' => $values['content'],
                    'created_at' => new Zend_Db_Expr('Now()'),
                    'changed_at' => new Zend_Db_Expr('Now()')
                );


                $this->_contentTable->insert($insertValues);

                $this->view->saveTitle = "Content gespeichert";

            } else {
                //fehler
                $this->view->form = $form;
            }
        } else {
            // normaler Aufruf (GET) - kein Formular
            $this->view->form = $this->getForm();
        }
    }

    public function getForm($valTitleIntern = "", $valContent = "", $valUrlName = "")
    {

        $form = new Zend_Form();
        $form->setMethod('POST');

        $title = $form->createElement('text', 'title_intern');
        $title->setLabel('Interner Titel: ');
        $title->setValue($valTitleIntern);
        $title->setRequired(true);

        $urlName = $form->createElement('text', 'url_name');
        $urlName->setLabel('URL Name: ');
        $urlName->setValue($valUrlName);
        $urlName->setRequired(true);

        $content = $form->createElement('textarea', 'content');
        $content->setLabel('Content: ');
        $content->setValue($valContent);
        $content->setRequired(true);

        $form->addElement($title)
            ->addElement($urlName)
            ->addElement($content)
            ->addElement('submit', 'save', array('label' => 'Speichern'));

        return $form;
    }

    public function editAction()
    {
        if ($this->_request->isPost()) {
            // is Post-Request - das Formular
            $form = $this->getForm();
            if ($form->isValid($_POST)) {
                //alles ok
                $values = $form->getValues();

                $updateValues = array(
                    'title' => $values['title_intern'],
                    'url_name' => $values['url_name'],
                    'content' => $values['content'],
                    'changed_at' => new Zend_Db_Expr('Now()')
                );

                $id = $this->_request->getParam('id');

                $this->_contentTable->update($updateValues, "content_id=" . $id);

                $this->view->saveTitle = "Content gespeichert";

            } else {
                //fehler
                $this->view->form = $form;
            }
        } else {
            // normaler Aufruf (GET) - kein Formular
            $id = $this->_request->getParam('id');


            $editItem = $this->_contentTable->find($id);
            $editItem = $editItem[0];
            $this->view->form = $this->getForm(stripslashes($editItem->title), stripslashes($editItem->content),
                stripslashes($editItem->url_name));
        }
    }

    public function setstatusAction()
    {
        $this->_helper->layout->disableLayout();

        $id = $this->getParam('id');
        $status = $this->getParam("status");

        $this->_contentTable->setStatus($status, $id);

        $this->_helper->json(true);
    }

    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();

        $id = $this->getParam('id');

        $this->_contentTable->setDelete($id);

        $this->_helper->json(true);
    }

}