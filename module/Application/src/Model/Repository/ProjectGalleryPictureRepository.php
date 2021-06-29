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

use Application\Model\Entity\ProjectGalleryPicture;
use Application\Model\Interfaces\ProjectGalleryPictureInterface;
use Laminas\Db\Adapter\AdapterInterface;

class ProjectGalleryPictureRepository extends BaseRepository implements ProjectGalleryPictureInterface
{
    //TODO NO TABLE KEY

    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "project_gallery_picture";
        $this->_key = array('project_id', 'sequence');
        $this->_prototype = ProjectGalleryPicture::class;
    }

    public function getGalleryPicturesForProject($projectId)
    {
        return $this->fetchAllRows(['project_id' => $projectId]);
    }

    public function clean($projectId)
    {
        $sql = 'delete from ' . $this->_name . ' where project_id =:project_id';
        $this->db->query($sql, ['project_id' => $projectId]);
    }

    public function insertAll($projectId, $sources)
    {
        $sequenceNr = 1;
        if ($sources && count($sources) > 0) {
            foreach ($sources as $src) {
                if (!isset($src)) {
                    continue;
                }
                $this->insert(['project_id' => $projectId, 'sequence' => $sequenceNr, 'picture_src' => $src]);
                $sequenceNr++;
            }
        }
    }

}