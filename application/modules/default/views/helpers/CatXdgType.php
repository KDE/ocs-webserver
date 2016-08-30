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
class Default_View_Helper_CatXdgType extends Zend_View_Helper_Abstract
{

    /**
     * @param int $catId
     * @return string
     */
    public function catXdgType($catId)
    {
        $table = new Default_Model_DbTable_ProjectCategory();
        $cat = $table->fetchActive($catId);

        if (count($cat)) {
            return $cat[0]['xdg_type'];
        } else {
            return '';
        }
    }

}
