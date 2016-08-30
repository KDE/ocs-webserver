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
class Default_Model_DbTable_MemberFollower extends Zend_Db_Table_Abstract
{

    protected $_name = "member_follower";

    public function countFollowedMembers($memberId)
    {
        $select = $this->_db->select()
            ->from('member_follower')
            ->joinUsing('member', 'member_id')
            ->where('member.is_deleted = ?', 0)
            ->where('member_follower.follower_id = ?', $memberId);
        return count($select->query()->fetchAll());
    }

}