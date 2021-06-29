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

namespace Application\Model\Service\Paginator;

use Application\Model\Repository\MemberDownloadHistoryRepository;
use Laminas\Paginator\Adapter\AdapterInterface;

class DownloadhistoryAdapter implements AdapterInterface
{

    private $memberDownloadHistoryRepository;
    private $member_id;

    public function __construct(
        MemberDownloadHistoryRepository $memberDownloadHistoryRepository,
        $member_id
    ) {
        $this->memberDownloadHistoryRepository = $memberDownloadHistoryRepository;
        $this->member_id = $member_id;
    }

    public function count()
    {
        return $this->getService()->countDownloadhistory($this->member_id);
    }

    public function getService()
    {
        return $this->memberDownloadHistoryRepository;
    }

    public function getItems($offset, $itemCountPerPage)
    {
        return $this->getService()->getDownloadhistory($this->member_id, $itemCountPerPage, $offset);
    }

}