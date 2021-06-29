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

namespace Backend\Controller;

use Application\Model\Entity\MailTemplate;
use Application\Model\Interfaces\MailTemplateInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Form\Form;
use Laminas\View\Model\JsonModel;

class MailController extends BackendBaseController
{
    private $_mailTplTable;

    public function __construct(
        MailTemplateInterface $mailTemplateRepository
    ) {
        parent::__construct();

        $this->_model = $mailTemplateRepository;
        $this->_mailTplTable = $mailTemplateRepository;
        $this->_modelName = MailTemplate::class;
        $this->_pageTitle = 'Übersicht eMail-Templates';
        $this->_defaultSorting = 'mail_template_id asc';
    }

    public function indexAjaxAction()
    {

        $start = (int)$this->getParam('start', 0);
        $count = (int)$this->getParam('count', 20);
        $sort = $this->getParam('sort', 'created_at');
        $dir = $this->getParam('dir');
        $dir = ($dir == 'yui-dt-desc') ? 'DESC' : 'ASC';
        $filter = $this->getParam('filter');

        $sel = $this->_mailTplTable->select();
        $sel->from($this->_mailTplTable->getName());
        $sel->columns(array('mail_template_id', 'name', 'subject', 'text', 'created_at', 'changed_at'));
        if ($sort) {
            $sel->order($sort);
        }
        if ($count) {
            $sel->limit($count);
        }
        if ($start) {
            $sel->offset($start);
        }

        if ($filter) {
            foreach ($filter as $field => $value) {
                $sel->where($field . ' like "' . $value . '%"');
            }
        }

        $mailTplData = $this->_mailTplTable->fetchAllSelect($sel);
        $mailTplData = $mailTplData->toArray();

        $reportsAll = $this->_model->fetchAllSelect(
            $sel->reset('columns')->reset('limit')->reset('offset')
                ->columns(array('countAll' => new Expression('count(*)')))
        );

        $responsData['results'] = $mailTplData;
        $responsData['totalRecords'] = $reportsAll->current()->countAll;

        return new JsonModel($responsData);

    }

    /**
     * @deprecated
     * @noinspection PhpUndefinedFieldInspection
     */
    public function addAction()
    {
        if ($this->getRequest()->isPost()) {
            // is Post-Request
            $form = $this->getForm();
            $form->setData($_POST);
            if ($form->isValid()) {
                //alles ok
                $values = $form->getData();

                $insertValues = array(
                    'name'       => $values['title_intern'],
                    'subject'    => $values['subject'],
                    'text'       => $values['content'],
                    'created_at' => new Expression('Now()'),
                    'changed_at' => new Expression('Now()'),
                );

                $this->_mailTplTable->insert($insertValues);

                $this->view->saveText = "eMail-Template saved";
            } else {
                //error
                $this->view->form = $form;
            }
        } else {
            // normaler Aufruf (GET) - kein Formular
            $this->view->form = $this->getForm();
        }
        $this->renderScript('mail/edit.phtml');
    }

    /**
     * @deprecated
     * @noinspection PhpUndefinedFieldInspection
     */
    public function getForm($valTitleIntern = "", $valSubject = "", $valContent = "")
    {

        $form = new Form();
        $form->setAttribute('method', 'post');

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

        $form->addElement($title)
             ->addElement($subject)
             ->addElement($content)
             ->addElement('submit', 'save', array('label' => 'Speichern'));

        return $form;
    }

    /**
     * @deprecated
     * @noinspection PhpUndefinedFieldInspection
     */
    public function editAction()
    {
        if ($this->getRequest()->isPost()) {
            // is Post-Request - das Formular
            $form = $this->getForm();
            $form->setData($_POST);
            if ($form->isValid()) {
                //alles ok
                $values = $form->getData();

                $updateValues = array(
                    'name'       => $values['title_intern'],
                    'subject'    => $values['subject'],
                    'text'       => $values['content'],
                    'changed_at' => new Expression('Now()'),
                );

                $id = $this->params('id');

                $this->_mailTplTable->update($updateValues, "mail_template_id=" . $id);

                $this->view->saveText = "eMail-Template gespeichert";
            } else {
                //fehler
                $this->view->form = $form;
            }
        } else {
            // normaler Aufruf (GET) - kein Formular
            $id = $this->getParam('id');

            $editItem = $this->_mailTplTable->fetchById($id);

            $this->view->form = $this->getForm(stripslashes($editItem->name), stripslashes($editItem->subject), stripslashes($editItem->text));
        }
    }

    /**
     * @deprecated
     */
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

    /**
     * @deprecated
     */
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