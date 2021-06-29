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

use Application\Model\Entity\MediaViews;
use Application\Model\Interfaces\MediaViewsInterface;
use DateTime;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSetInterface;

class MediaViewsRepository extends BaseRepository implements MediaViewsInterface
{
    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "media_views";
        $this->_key = "media_view_id";
        $this->_prototype = MediaViews::class;
    }

    public function getNewId()
    {
        $statement = $this->db->query('SELECT UUID_SHORT()');
        $result = $results = $statement->execute()->current();


        return $result['UUID_SHORT()'];
    }

    public function fetchCountViewsTodayForFile($collection_id, $file_id)
    {
        if (empty($collection_id)) {
            return 0;
        }

        $today = (new DateTime())->modify('-1 day');
        $filterDownloadToday = $today->format("Y-m-d H:i:s");

        $adapter = $this->db;
        $fp = function ($name) use ($adapter) {
            return $adapter->driver->formatParameterName($name);
        };

        $sql = "    SELECT COUNT(1) AS 'count'
                    FROM media_views f
                    WHERE f.collection_id = " . $fp('id') . "
                    AND f.file_id = " . $fp('file_id') . "
                    AND f.start_timestamp >= '" . $filterDownloadToday . "'               
                   ";
        $statement = $this->db->query($sql);
        /** @var ResultSetInterface $resultSet */
        $resultSet = $statement->execute(['id' => $collection_id, 'file_id' => $file_id])->current();

        if ($resultSet && count($resultSet) > 0) {
            return $resultSet['count'];
        } else {
            return 0;
        }
    }

    public function fetchCountViewsForFileAllTime($collectionId, $file_id)
    {
        if (empty($file_id) || empty($collectionId)) {
            return 0;
        }

        $adapter = $this->db;
        $fp = function ($name) use ($adapter) {
            return $adapter->driver->formatParameterName($name);
        };

        $sql = "    SELECT COUNT(1)  AS 'count'
                    FROM media_views f
                    WHERE f.collection_id = " . $fp('id') . " 
                    AND f.file_id = " . $fp('file_id') . "
                   ";
        $statement = $this->db->query($sql);
        /** @var ResultSetInterface $resultSet */
        $resultSet = $statement->execute(['id' => $collectionId, 'file_id' => $file_id])->current();

        if ($resultSet && count($resultSet) > 0) {
            return $resultSet['count'];
        } else {
            return 0;
        }
    }

    public function fetchCountViewsTodayForProject($project_id)
    {
        if (empty($project_id)) {
            return 0;
        }

        $today = (new DateTime())->modify('-1 day');
        $filterDownloadToday = $today->format("Y-m-d H:i:s");

        $adapter = $this->db;
        $fp = function ($name) use ($adapter) {
            return $adapter->driver->formatParameterName($name);
        };

        $sql = "    SELECT COUNT(1) AS 'count'
                    FROM media_views f
                    WHERE f.project_id = " . $fp('id') . "
                    AND f.start_timestamp >= '" . $filterDownloadToday . "'               
                   ";
        $statement = $this->db->query($sql);
        /** @var ResultSetInterface $resultSet */
        $resultSet = $statement->execute(['id' => $project_id])->current();

        if ($resultSet && count($resultSet) > 0) {
            return $resultSet['count'];
        } else {
            return 0;
        }
    }

    public function fetchCountViewsForProjectAllTime($project_id)
    {
        if (empty($project_id)) {
            return 0;
        }

        $adapter = $this->db;
        $fp = function ($name) use ($adapter) {
            return $adapter->driver->formatParameterName($name);
        };

        $sql = "    SELECT COUNT(1)  AS 'count'
                    FROM media_views f
                    WHERE f.project_id = " . $fp('id') . "
                   ";
        $statement = $this->db->query($sql);
        /** @var ResultSetInterface $resultSet */
        $resultSet = $statement->execute(['id' => $project_id])->current();

        if ($resultSet && count($resultSet) > 0) {
            return $resultSet['count'];
        } else {
            return 0;
        }
    }

}
