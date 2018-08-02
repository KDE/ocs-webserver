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
class Default_Model_DbTable_Member extends Local_Model_Table
{

    const MEMBER_ACTIVE = 1;
    const MEMBER_INACTIVE = 0;
    const MEMBER_DELETED = 1;
    const MEMBER_NOT_DELETED = 0;
    const MEMBER_LOGIN_LOCAL = 'local';
    const MEMBER_LOGIN_FACEBOOK = 'facebook';
    const MEMBER_LOGIN_TWITTER = 'twitter';
    const MEMBER_MAIL_CHECKED = 1;
    const MEMBER_NOT_MAIL_CHECKED = 0;
    const MEMBER_DEFAULT_AVATAR = 'default-profile.png';
    const MEMBER_DEFAULT_PROFILE_IMAGE = '/images/system/default-profile.png';
    const MEMBER_TYPE_GROUP = 1;
    const MEMBER_TYPE_PERSON = 0;
    const ROLE_ID_MODERATOR = 400;
    const ROLE_ID_DEFAULT = 300;
    const ROLE_ID_STAFF = 200;
    const ROLE_ID_ADMIN = 100;
    const PROFILE_IMG_SRC_LOCAL = 'local';
    const SOURCE_LOCAL = 0;
    const SOURCE_HIVE = 1;
    const PASSWORD_TYPE_OCS = 0;
    const PASSWORD_TYPE_HIVE = 1;

    protected $_keyColumnsForRow = array('member_id');

    protected $_key = 'member_id';

    protected $_name = "member";

    protected $_dependentTables = array('Default_Model_Project');

    protected $_referenceMap = array(
        'Owner' => array(
            'columns' => 'member_id',
            'refTableClass' => 'Default_Model_Project',
            'refColumns' => 'member_id'
        ),
        'Email' => array(
            'columns' => 'member_id',
            'refTableClass' => 'Default_Model_DbTable_MemberEmail',
            'refColums' => 'email_member_id'
        )
    );


    /**
     * @param array|string $member_id
     * @return int|void
     * @throws Exception
     */
    public function delete($member_id)
    {
        throw new Exception('Deleting of users is not allowed.');
    }

}