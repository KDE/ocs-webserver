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

namespace Application\Model\Service\Interfaces;

interface AvatarServiceInterface
{
    /**
     * @param string $emailHash
     * @param string $username
     * @param int    $size
     *
     * @return string|null
     */
    public function getAvatarUrl($emailHash, $username = null, $size = 200, $width = null);

    /**
     * @param string $email_hash
     * @param int    $size
     *
     * @return string|null
     */
    public function getAvatarForEmailHash($email_hash, $size, $width = null);

    /**
     * @param string $username
     * @param int    $width
     *
     * @return string|null
     */
    public function getAvatarForUsername($username, $size, $width = null);
}