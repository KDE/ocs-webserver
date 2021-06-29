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

use Application\Model\Repository\TagsRepository;
use Laminas\Cache\Storage\Adapter\AbstractAdapter;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\View\Helper\AbstractHelper;


class FetchTagsForTagGroup extends AbstractHelper
{
    /**
     * @var AbstractAdapter
     */
    private $db;

    private $cache;

    public function __construct(
        AdapterInterface $db
    ) {
        $this->cache = $GLOBALS['ocs_cache'];
        $this->db = $db;
    }

    public function fetchList($groupId, $withHeader = false)
    {

        $tableTags = new TagsRepository($this->db, $this->cache);
        $tags = array();
        if ($withHeader) {
            $tags = $tableTags->fetchForGroupForSelect($groupId, true);
            //$tags['header'] = 'Filter for: ' . $tags['header'];
            $tags['header'] =  $tags['header'];
        } else {
            $tags = $tableTags->fetchForGroupForSelect($groupId);
        }


        return $tags;
    }

} 