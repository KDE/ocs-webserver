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
class Backend_MailController extends Local_Controller_Action_Backend
{

    private $_mailTplTable;

    public function init()
    {
        $this->_mailTplTable = new Default_Model_DbTable_MailTemplate();
        parent::init();
    }

    /**
     *
     * @return json projectlist
     */
    public function indexAjaxAction()
    {
        $this->_helper->layout->disableLayout();

        $start = $this->getParam('start', 0);
        $count = $this->getParam('count', 20);
        $sort = $this->getParam('sort', 'created_at');
        $dir = $this->getParam('dir');
        $dir = ($dir == 'yui-dt-desc') ? 'DESC' : 'ASC';
        $filter = $this->getParam('filter', null);

        $sel = $this->_mailTplTable->select()->setIntegrityCheck(false);
        $sel->from($this->_mailTplTable,
            array('mail_template_id', 'name', 'subject', 'text', 'created_at', 'changed_at'))//                ->where('is_deleted=0')
            ->order($sort, $dir)->limit($count, $start)
        ;

        if ($filter) {
            foreach ($filter as $field => $value) {
                $sel->where($field . ' like "' . $value . '%"');
            }
        }

        $mailTplData = $this->_mailTplTable->fetchAll($sel);
        $mailTplData = $mailTplData->toArray();

        $responsData['results'] = $mailTplData;
        $responsData['totalRecords'] = count($mailTplData);

        $this->_helper->json($responsData);
    }

    public function indexAction()
    {

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
                    'name'       => $values['title_intern'],
                    'subject'    => $values['subject'],
                    'text'       => $values['content'],
                    'created_at' => new Zend_Db_Expr('Now()'),
                    'changed_at' => new Zend_Db_Expr('Now()')
                );

                $this->_mailTplTable->insert($insertValues);

                $this->view->saveText = "eMail-Template gespeichert";
            } else {
                //fehler
                $this->view->form = $form;
            }
        } else {
            // normaler Aufruf (GET) - kein Formular
            $this->view->form = $this->getForm();
        }
        $this->renderScript('mail/edit.phtml');
    }

    public function getForm($valTitleIntern = "", $valSubject = "", $valContent = "")
    {

        $form = new Zend_Form();
        $form->setMethod('POST');

        $title = $form->createElement('text', 'title_intern');
        $title->setLabel('Interner Name: ');
        $title->setValue($valTitleIntern);
        $title->setRequired(true);

        $subject = $form->createElement('text', 'subject');
        $subject->setLabel('Betreff: ');
        $subject->setValue($valSubject);
        $subject->setRequired(true);

        $content = $form->createElement('textarea', 'content');
        $content->setLabel('Content: ');
        $content->setValue($valContent);
        $content->setRequired(true);

        $form->addElement($title)->addElement($subject)->addElement($content)
             ->addElement('submit', 'save', array('label' => 'Speichern'))
        ;

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
                    'name'       => $values['title_intern'],
                    'subject'    => $values['subject'],
                    'text'       => $values['content'],
                    'changed_at' => new Zend_Db_Expr('Now()')
                );

                $id = $this->_request->getParam('id');

                $this->_mailTplTable->update($updateValues, "mail_template_id=" . $id);

                $this->view->saveText = "eMail-Template gespeichert";
            } else {
                //fehler
                $this->view->form = $form;
            }
        } else {
            // normaler Aufruf (GET) - kein Formular
            $id = $this->_request->getParam('id');

            $editItem = $this->_mailTplTable->find($id);
            $editItem = $editItem[0];
            $this->view->form =
                $this->getForm(stripslashes($editItem->name), stripslashes($editItem->subject), stripslashes($editItem->text));
        }
    }

    public function setstatusAction()
    {
        //        $this->_helper->layout->disableLayout();
        //
        //        $id = $this->getParam('id');
        //        $status = $this->getParam("status");
        //
        //        $this->_contentTable->setStatus($status,$id);
        //
        //        $this->_helper->json(true);
    }

    public function deleteAction()
    {
        //        $this->_helper->layout->disableLayout();
        //
        //        $id = $this->getParam('id');
        //
        //        $this->_contentTable->setDelete($id);
        //
        //        $this->_helper->json(true);
    }

}