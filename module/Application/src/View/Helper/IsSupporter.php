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

use Application\Model\Interfaces\MemberInterface;
use Application\Model\Service\Interfaces\MemberServiceInterface;
use Laminas\View\Helper\AbstractHelper;

class IsSupporter extends AbstractHelper
{
    /**
     * @var MemberInterface
     */
    private $members;

    public function __construct(MemberServiceInterface $members)
    {
        $this->members = $members;
    }

    public function __invoke($member_id)
    {
        return $this->isSupporter($member_id);

    }

    public function isSupporter($member_id)
    {       
        $cache = $GLOBALS['ocs_cache'];
        $cacheName = __FUNCTION__ . '_' . md5($member_id);

        $issupporter = $cache->getItem($cacheName);
        if (null != $issupporter && false !== $issupporter) {
            return $issupporter;
        }        
        $activeyears = $this->members->fetchSupportersActiveYears($member_id);
        if ($activeyears == 0) {
                $cache->setItem($cacheName, false);    
                return false;
        } else {
            $cache->setItem($cacheName, $activeyears);    
            return $activeyears;
        }
    }

} 