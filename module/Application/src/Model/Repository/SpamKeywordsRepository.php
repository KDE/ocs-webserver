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

namespace Application\Model\Repository;

use Application\Model\Entity\SpamKeywords;
use Application\Model\Interfaces\SpamKeywordsInterface;
use Laminas\Db\Adapter\AdapterInterface;

class SpamKeywordsRepository extends BaseRepository implements SpamKeywordsInterface
{

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "spam_keywords";
        $this->_key = "spam_key_id";
        $this->_prototype = SpamKeywords::class;
    }

    public function delete($where)
    {
        return $this->update(['spam_key_is_deleted' => 1, 'spam_key_is_active' => 0], $where);
    }

    public function listAll($startIndex, $pageSize, $sorting)
    {
        $rows = $this->fetchAllRows(['spam_key_is_active' => 1], $sorting, $pageSize, $startIndex)->toArray();

        $count = $this->fetchAllRowsCount(['spam_key_is_active' => 1]);

        if (empty($rows)) {
            return array('rows' => array(), 'totalCount' => 0);
        }

        return array('rows' => $rows, 'totalCount' => $count);
    }

}