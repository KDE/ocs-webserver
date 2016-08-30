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
// require_once APPLICATION_LIB . '/Zend/Form/Element/Select.php';

class Default_Form_Element_SelectNotEscaped extends Zend_Form_Element_Select
{

    public $helper = 'formSelectNotEscaped';

    public function init()
    {
        $view = $this->getView();
        $view->addHelperPath(APPLICATION_PATH . '/modules/default/forms/helpers/', 'Default_Form_Helper');
        parent::init();
    }

} 