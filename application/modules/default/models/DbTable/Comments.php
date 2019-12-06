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
class Default_Model_DbTable_Comments extends Local_Model_Table
{

    const COMMENT_ACTIVE = 1;
    const COMMENT_INACTIVE = 0;    
    const COMMENT_TYPE_PLING = 10;
    const COMMENT_TYPE_DONATION = 20;
    const COMMENT_TYPE_PRODUCT = 0;
    const COMMENT_TYPE_MODERATOR = 30;
            
    protected $_name = "comments";

    protected $_keyColumnsForRow = array('comment_id');

    protected $_key = 'comment_id';

    protected $_defaultValues = array(
        'comment_type' => 0,
        'comment_parent_id' => 0,
        'comment_target_id' => null,
        'comment_member_id' => null,
        'comment_text' => null,
        'comment_created_at' => null,
        'comment_active' => null,
        'source_id' => 0,
        'source_pk' => null
    );

    /**
     * @param int $identifier
     * @return int
     */
    public function setDelete($identifier)
    {
        return $this->delete($this->getAdapter()->quoteInto("$this->_key = ?", $identifier, 'INTEGER'));
    }

}