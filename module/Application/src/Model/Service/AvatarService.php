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

namespace Application\Model\Service;

use Application\Model\Repository\MemberRepository;
use Application\Model\Service\Interfaces\AvatarServiceInterface;
use Application\View\Helper\Image;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Log\Logger;

class AvatarService extends BaseService implements AvatarServiceInterface
{

    const AVATAR_DEFAULT_URL = 'default-profile.png';
    protected $db;

    public function __construct(
        AdapterInterface $db
    ) {
        $this->db = $db;
    }

    /**
     * @param string $emailHash
     * @param string $username
     * @param int    $size
     * @param int    $width
     *
     * @return string|null
     */
    public function getAvatarUrl($emailHash, $username = null, $size = 200, $width = null)
    {
        if (false === empty($emailHash)) {
            return $this->getAvatarForEmailHash($emailHash, $size, $width);
        }

        if (false === empty($username)) {
            return $this->getAvatarForUsername($username, $size, $width);
        }

        return self::getAvatarImageUrl($size, $width);
    }

    /**
     * @param string $email_hash
     * @param int    $size
     *
     * @return string|null
     */
    public function getAvatarForEmailHash($email_hash, $size, $width = null)
    {
        if (empty($email_hash)) {
            return self::getAvatarImageUrl($size, $width);
        }

        $memberTable = new MemberRepository($this->db);
        $member = $memberTable->findMemberForMailHash($email_hash, false);

        if (empty($member)) {
            /** @var Logger $log */
            $log = $GLOBALS['ocs_log'];
            $log->warn(__METHOD__ . ' - no member found for email_hash: ' . $email_hash);

            return self::getAvatarImageUrl($size, $width);
        }

        return self::getAvatarImageUrl($size, $width, $member['profile_image_url']);
    }

    /**
     * @param int    $size
     * @param string $profile_image_url
     * @param int    $width
     *
     * @return string|null
     */
    public static function getAvatarImageUrl($size, $width = null, $profile_image_url = self::AVATAR_DEFAULT_URL)
    {
        $helperImage = new Image();
        $img_width = $width ? $width : $size;

        return $helperImage->Image($profile_image_url, array('width' => $img_width, 'height' => $size));
    }

    /**
     * @param string $username
     * @param int    $size
     * @param int    $width
     *
     * @return string|null
     */
    public function getAvatarForUsername($username, $size, $width = null)
    {
        if (empty($username)) {
            return self::getAvatarImageUrl($size, $width);
        }

        $members = new MemberRepository($this->db);
        $member = $members->findUsername($username, MemberRepository::CASE_INSENSITIVE, array(), false);
        if (empty($member)) {
            $log = $GLOBALS['ocs_log'];
            $log->warn(__METHOD__ . ' - no member_id found for username: ' . $username);

            return self::getAvatarImageUrl($size, $width);
        }

        return self::getAvatarImageUrl($size, $width, $member[0]['profile_image_url']);
    }
}