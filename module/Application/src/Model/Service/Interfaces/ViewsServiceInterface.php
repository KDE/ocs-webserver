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

interface ViewsServiceInterface
{
    public static function saveViewProduct($product_id);

    public static function getRemoteAddress($ipClient);

    public static function validate_ip($ip);

    public static function saveViewMemberpage($member_id);

    public static function saveFileDownload($file_id);

    public static function saveViewCollection($_projectId);

    public static function saveViewMusic($object_id);

    public static function saveViewVideo($object_id);

    public static function saveViewBook($object_id);
}