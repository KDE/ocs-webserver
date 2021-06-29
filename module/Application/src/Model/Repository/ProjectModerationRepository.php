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

use Application\Model\Entity\ProjectModeration;
use Application\Model\Interfaces\ProjectModerationInterface;
use Laminas\Db\Adapter\AdapterInterface;

class ProjectModerationRepository extends BaseRepository implements ProjectModerationInterface
{

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "project_moderation";
        $this->_key = "project_moderation_id";
        $this->_prototype = ProjectModeration::class;
    }

    public function setDelete($project_moderation_id)
    {
        $this->setIsDeleted($project_moderation_id, false);

    }

    public function setValid($project_moderation_id)
    {
        $this->update(['is_valid' => 1, 'project_moderation_id' => $project_moderation_id]);
    }

    public function insertModeration($project_moderation_type_id, $project_id, $value, $created_by, $note)
    {
        $insertValues = array(
            'project_moderation_type_id' => $project_moderation_type_id,
            'project_id'                 => $project_id,
            'value'                      => $value,
            'created_by'                 => $created_by,
            'note'                       => $note,
        );
        $resultIds[] = $this->insert($insertValues);

        return $resultIds;
    }

}