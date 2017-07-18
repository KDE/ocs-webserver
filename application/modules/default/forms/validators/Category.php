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
 * Created: 07.07.2017
 */
class Default_Form_Validator_Category extends Zend_Validate_Abstract
{

    const ERROR_CAT_NOT_THE_LAST = 'cat_not_last';

    /**
     * @var array Message templates
     */
    protected $_messageTemplates = array(
        self::ERROR_CAT_NOT_THE_LAST => "Please select a children for this category. ---",
    );

    /**
     *  This method will check if the given cat_id has no children and is the last in hierarchy
     *
     * @param  mixed $value
     *
     * @return boolean
     * @throws Zend_Validate_Exception If validation of $value is impossible
     */
    public function isValid($value)
    {
        $valid = true;
        $this->_setValue($value);

        $tableCat = new Default_Model_DbTable_ProjectCategory();
        $catChildIds = $tableCat->fetchChildIds($value);
        if(!$catChildIds || (count($catChildIds) == 1 && $catChildIds[0] == $value) ) {
            //is the last child
            $valid = true;
        } else {
            //is not the last child
            $valid = false;
            $this->_error(self::ERROR_CAT_NOT_THE_LAST);
        }
        
        //if (!$catChildIds || count($catChildIds) <> 1 || $catChildIds[0] != $value) {
        //    $valid = false;
        //    $this->_error(self::ERROR_CAT_NOT_THE_LAST);
        //}
        
        
        //$tableCat = new Default_Model_DbTable_ProjectCategory();
        //$catChildIds = $tableCat->fetchChildIds($value);
        //if (count($catChildIds)) {
        //    $valid = false;
        //    $this->_error(self::ERROR_CAT_NOT_THE_LAST);
        //}

        return $valid;
    }

}