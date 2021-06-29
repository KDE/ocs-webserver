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

namespace Application\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class TwitterLink extends AbstractHelper
{

    public function __invoke($strLink)
    {
        $strLink = trim($strLink);

        if ($this->checkHttpProtocol($strLink)) {
            return $strLink;
        }
        if ($this->checkDomain($strLink)) {
            return '//' . $strLink;
        }

        return '//twitter.com/' . $strLink;
    }

    private function checkHttpProtocol($strUrl)
    {
        if ((substr($strUrl, 0, 8) == 'https://')
            or (substr($strUrl, 0, 7) == 'http://')
            or (substr($strUrl, 0, 2) == '//')) {
            return true;
        } else {
            return false;
        }
    }

    private function checkDomain($strUrl)
    {
        if ((strpos($strUrl, 'twitter') !== false)
            or (strpos($strUrl, '/') !== false)) {
            return true;
        } else {
            return false;
        }
    }
}