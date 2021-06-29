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

namespace Application\Controller;

use Laminas\View\Model\ViewModel;

/**
 * Class ContentController
 *
 * @package Application\Controller
 */
class ContentController extends BaseController
{
    public function indexAction()
    {
        $this->layout()->setTemplate('layout/flat-ui');
        $this->layout()->noheader = true;
        $static_config = $this->ocsConfig->settings->static;
        $pageName = $this->params()->fromRoute('page', null);

        if (false === isset($static_config->include->$pageName)) {
            //throw new Exception('This page does not exist', 404);
            $this->getResponse()->setStatusCode(404);

            return;
        }

        return new ViewModel(['page' => $static_config->include_path . '/' . $static_config->include->$pageName]);
    }
}