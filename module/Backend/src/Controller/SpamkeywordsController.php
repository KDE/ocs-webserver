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
 * Created: 11.01.2019
 */

namespace Backend\Controller;

use Application\Model\Entity\SpamKeywords;
use Application\Model\Repository\SpamKeywordsRepository;
use Laminas\View\Model\JsonModel;

class SpamkeywordsController extends BackendBaseController
{
    private $spamKeywordsRepository;

    public function __construct(
        SpamKeywordsRepository $spamKeywordsRepository
    ) {
        parent::__construct();
        $this->spamKeywordsRepository = $spamKeywordsRepository;
        $this->_model = $spamKeywordsRepository;
        $this->_modelName = SpamKeywords::class;
        $this->_pageTitle = 'Manage Spam Keywords';

    }

    public function deleteAction()
    {
        $id = (int)$this->getParam('spam_key_id', null);
        $this->_model->deleteReal($id);
        $jTableResult = array();
        $jTableResult['Result'] = self::RESULT_OK;

        return new JsonModel($jTableResult);
    }

}