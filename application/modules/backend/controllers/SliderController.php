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
class Backend_SliderController extends Local_Controller_Action_Backend
{

    protected $_table;

    public function init()
    {
        $this->_table = new Default_Model_DbTable_ContentSlider();
        parent::init();
    }

    public function indexAction()
    {

        $sel = $this->_table->select();

        $sel->where('is_deleted=0')
            ->order('order_pos');

        $this->view->sliderImages = $this->_table->fetchAll($sel);
    }

    public function addAction()
    {

        if ($this->_request->isPost()) {
            $form = $this->getForm();

            if ($form->isValid($_POST)) {

                $values = $form->getValues();

                if ($values['picture']) {

                    $newVals = array(
                        'image' => $values['picture'],
                        'order_pos' => $values['order_pos'],
                        'created_at' => new Zend_Db_Expr("NOW()"),
                        'changed_at' => new Zend_Db_Expr("NOW()")

                    );

                    $this->_table->insert($newVals);

                    $this->view->saveText = "Bild gespeichert!";
                } else {
                    $this->view->saveText = "Da kein Bild hochgeladen wurde, wurde auch nichts gespeichert.";
                }


            } else {
                $this->view->form = $form;
            }
        } else {
            $this->view->form = $this->getForm();
        }
    }

    public function getForm($valPicture = "", $valOrderPos = "")
    {
        $form = new Zend_Form();
        $form->setMethod('POST');
        $form->setAttrib('enctype', 'multipart/form-data');

        $picture = $form->createElement('file', 'picture');
        $picture->setLabel('Slider-Bild: ')
            ->setDestination((string)Zend_Registry::get('sliderImages2'))
            ->setMultiFile(1);

        if ($valPicture) {
            $picture->setDescription('Vorhandenes Bild:<br/><img src="/images/slider2/' . $valPicture . '" border="0" height="80px" />');
        }

        $picture->getDecorator('description')->setEscape(false);

        $pos = $form->createElement('text', 'order_pos')
            ->setLabel("Reihenfolgenposition:")
            ->setDescription("Nur Zahlen (1,2,3...)")
            ->setValue($valOrderPos);

        $form->addElement($picture);
        $form->addElement($pos);
        $form->addElement('submit', 'save', array('label' => 'Speichern'));

        return $form;
    }

    public function editAction()
    {

        $id = $this->getParam('id');

        $editImage = $this->_table->fetchRow('content_slider_id=' . $id);

        if ($this->_request->isPost()) {

            $form = $this->getForm();

            if ($form->isValid($_POST)) {

                $values = $form->getValues();

                if ($values['picture']) {
                    $editImage->image = $values['picture'];
                }

                $editImage->order_pos = $values['order_pos'];

                $editImage->save();

                $this->view->saveText = "Erfolgreich gespeichert.";
            } else {
                $this->view->form = $form;
            }
        } else {
            $this->view->form = $this->getForm($editImage->image, $editImage->order_pos);
        }
    }

    public function setstatusAction()
    {
        $this->_helper->layout->disableLayout();

        $id = $this->getParam('id');
        $status = $this->getParam("status");

        $this->_table->setStatus($status, $id);

        $this->_helper->json(true);
    }

    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();

        $id = $this->getParam('id');

        $this->_table->setDelete($id);

        $this->_helper->json(true);
    }

}