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
class Default_Model_Avatar
{
    const AVATAR_DEFAULT_URL = 'default-profile.png';

    /**
     * @param string $emailHash
     * @param string $username
     * @param int    $size
     * @return string|null
     * @throws Zend_Exception
     */
    public function getAvatarUrl($emailHash, $username = null, $size = 200)
    {
        if (false === empty($emailHash)) {
            return $this->getAvatarForEmailHash($emailHash, $size);
        }

        if (false === empty($username)) {
            return $this->getAvatarForUsername($username, $size);
        }

        return self::getAvatarImageUrl($size);
    }

    /**
     * @param string $email_hash
     * @param int    $size
     * @return string|null
     * @throws Zend_Exception
     */
    public function getAvatarForEmailHash($email_hash, $size)
    {
        if (empty($email_hash)) {
            return self::getAvatarImageUrl($size);
        }

        $memberTable = new Default_Model_Member();
        $member = $memberTable->findMemberForMailHash($email_hash, false);

        if (empty($member)) {
            Zend_Registry::get('logger')->warn(__METHOD__ . ' - no member found for email_hash: ' . $email_hash);
            return self::getAvatarImageUrl($size);
        }

        $imgUrl = self::getAvatarImageUrl($size, $member['profile_image_url']);

        return $imgUrl;
    }

    /**
     * @param int    $size
     * @param string $profile_image_url
     * @return string|null
     */
    public static function getAvatarImageUrl($size, $profile_image_url = self::AVATAR_DEFAULT_URL)
    {
        $helperImage = new Default_View_Helper_Image();
        $imgUrl = $helperImage->Image($profile_image_url, array('width' => $size, 'height' => $size));

        return $imgUrl;
    }

    /**
     * @param string $username
     * @param int    $width
     * @return string|null
     * @throws Zend_Exception
     */
    public function getAvatarForUsername($username, $width)
    {
        if (empty($username)) {
            return self::getAvatarImageUrl($width);
        }

        $members = new Default_Model_Member();
        $member = $members->findUsername($username, Default_Model_Member::CASE_INSENSITIVE, array(), false);
        if (empty($member)) {
            Zend_Registry::get('logger')->warn(__METHOD__ . ' - no member_id found for username: ' . $username);
            return self::getAvatarImageUrl($width);
        }

        return self::getAvatarImageUrl($width, $member[0]['profile_image_url']);
    }

}