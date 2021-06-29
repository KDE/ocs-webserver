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

use Application\Model\Entity\TagObject;
use Application\Model\Interfaces\TagObjectInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Expression;
use RuntimeException;

class TagObjectRepository extends BaseRepository implements TagObjectInterface
{
    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "tag_object";
        $this->_key = "tag_item_id";
        $this->_prototype = TagObject::class;
    }

    public function runQuery($sql, $params = null)
    {
        try {
            $this->db->query($sql, $params);
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                sprintf(
                    'Cannot run query' . $sql, $sql
                )
            );
        }
    }

    public function deleteTagObject($tag_item_id)
    {
        $this->update(
            [
                'tag_changed' => new Expression('now()'),
                'is_deleted'  => 1,
                'tag_item_id' => $tag_item_id,
            ]
        );
    }

}
