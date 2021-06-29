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

use Application\Model\Repository\MemberSettingValueRepository;
use Laminas\View\Helper\AbstractHelper;

class FetchMemberSettingItem extends AbstractHelper
{
    /**
     * Constructor
     *
     */
    private $memberSettingValueTable;

    public function __construct(MemberSettingValueRepository $memberSettingValueTable)
    {
        $this->memberSettingValueTable = $memberSettingValueTable;
    }

    public function __invoke($member_id, $item_id)
    {
        return $this->memberSettingValueTable->fetchMemberSettingItem($member_id, $item_id);

    }
}