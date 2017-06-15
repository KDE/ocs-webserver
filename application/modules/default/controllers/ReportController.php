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
class ReportController extends Zend_Controller_Action
{

    public function commentAction()
    {
        $this->_helper->layout()->disableLayout();
        if(APPLICATION_ENV != 'searchbotenv') {
            $comment_id = (int) $this->getParam('i');
            $project_id = (int) $this->getParam('p');
            $reported_by = Zend_Auth::getInstance()->hasIdentity() ? (int) Zend_Auth::getInstance()->getStorage()->read()->member_id : 0;

            $tableReportComments = new Default_Model_DbTable_ReportComments();

            $tableReportComments->save(array('project_id' => $project_id, 'comment_id' => $comment_id, 'reported_by' => $reported_by));
        }
        $this->_helper->json(array('status' => 'ok', 'message' => '<p>Thank you, we received your message.</p><div class="modal-footer">
                                    <button type="button" style="border:none;background: transparent;color: #2673b0;" class="small close" data-dismiss="modal" > Close</button>
                                </div>', 'data' => array()));

    }

    public function productAction()
    {
        $this->_helper->layout()->disableLayout();
        
        if(APPLICATION_ENV != 'searchbotenv') {

            $session = new Zend_Session_Namespace();
            $reportedProducts = isset($session->reportedProducts) ? $session->reportedProducts : array();
            $project_id = (int) $this->getParam('p');
            if (in_array($project_id, $reportedProducts)) {
                $this->_helper->json(array('status' => 'ok', 'message' => '<p>Thank you, but you have already reported this product.</p><div class="modal-footer">
                                            <button type="button" style="border:none;background: transparent;color: #2673b0;" class="small close" data-dismiss="modal" > Close</button>
                                        </div>', 'data' => array()));
            }
            $reported_by = 0;
            if (Zend_Auth::getInstance()->hasIdentity()) {
                $reported_by = (int) Zend_Auth::getInstance()->getStorage()->read()->member_id;
            }

            $modelProduct = new Default_Model_Project();
            $productData = $modelProduct->fetchRow(array('project_id = ?' => $project_id));

            if ($productData->spam_checked == 0) {
                $tableReportComments = new Default_Model_DbTable_ReportProducts();
                $tableReportComments->save(array('project_id' => $project_id, 'reported_by' => $reported_by));
            }
            $session->reportedProducts[] = $project_id;
        }

        $this->_helper->json(array('status' => 'ok', 'message' => '<p>Thank you, we received your message.</p><div class="modal-footer">
                                            <button type="button" style="border:none;background: transparent;color: #2673b0;" class="small close" data-dismiss="modal" > Close</button>
                                        </div>', 'data' => array()));
    }

    public function memberAction()
    {

    }

}