<?php
/**
 *   ocs-webserver
 *
 *   Copyright 2016 by pling GmbH.
 *
 *     This file is part of ocs-webserver.
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Application\Model\Entity;

use DomainException;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

/**
 * Class PploadCollections
 *
 * @package Application\Model\Entity
 */
class PploadCollections implements InputFilterAwareInterface
{
    // attributes
    public $id;
    public $active;
    public $client_id;
    public $owner_id;
    public $name;
    public $files;
    public $size;
    public $title;
    public $description;
    public $category;
    public $tags;
    public $version;
    public $content_id;
    public $content_page;
    public $downloaded_timestamp;
    public $downloaded_ip;
    public $downloaded_count;
    public $created_timestamp;
    public $created_ip;
    public $updated_timestamp;
    public $updated_ip;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->active = !empty($data['active']) ? $data['active'] : null;
        $this->client_id = !empty($data['client_id']) ? $data['client_id'] : null;
        $this->owner_id = !empty($data['owner_id']) ? $data['owner_id'] : null;
        $this->name = !empty($data['name']) ? $data['name'] : null;
        $this->files = !empty($data['files']) ? $data['files'] : null;
        $this->size = !empty($data['size']) ? $data['size'] : null;
        $this->title = !empty($data['title']) ? $data['title'] : null;
        $this->description = !empty($data['description']) ? $data['description'] : null;
        $this->category = !empty($data['category']) ? $data['category'] : null;
        $this->tags = !empty($data['tags']) ? $data['tags'] : null;
        $this->version = !empty($data['version']) ? $data['version'] : null;
        $this->content_id = !empty($data['content_id']) ? $data['content_id'] : null;
        $this->content_page = !empty($data['content_page']) ? $data['content_page'] : null;
        $this->downloaded_timestamp = !empty($data['downloaded_timestamp']) ? $data['downloaded_timestamp'] : null;
        $this->downloaded_ip = !empty($data['downloaded_ip']) ? $data['downloaded_ip'] : null;
        $this->downloaded_count = !empty($data['downloaded_count']) ? $data['downloaded_count'] : null;
        $this->created_timestamp = !empty($data['created_timestamp']) ? $data['created_timestamp'] : null;
        $this->created_ip = !empty($data['created_ip']) ? $data['created_ip'] : null;
        $this->updated_timestamp = !empty($data['updated_timestamp']) ? $data['updated_timestamp'] : null;
        $this->updated_ip = !empty($data['updated_ip']) ? $data['updated_ip'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'id'                   => $this->id,
            'active'               => $this->active,
            'client_id'            => $this->client_id,
            'owner_id'             => $this->owner_id,
            'name'                 => $this->name,
            'files'                => $this->files,
            'size'                 => $this->size,
            'title'                => $this->title,
            'description'          => $this->description,
            'category'             => $this->category,
            'tags'                 => $this->tags,
            'version'              => $this->version,
            'content_id'           => $this->content_id,
            'content_page'         => $this->content_page,
            'downloaded_timestamp' => $this->downloaded_timestamp,
            'downloaded_ip'        => $this->downloaded_ip,
            'downloaded_count'     => $this->downloaded_count,
            'created_timestamp'    => $this->created_timestamp,
            'created_ip'           => $this->created_ip,
            'updated_timestamp'    => $this->updated_timestamp,
            'updated_ip'           => $this->updated_ip,


        ];
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(
            sprintf(
                '%s does not allow injection of an alternate input filter', __CLASS__
            )
        );
    }

    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();
        //-----------------> hier come inputfiler
        $this->inputFilter = $inputFilter;

        return $this->inputFilter;
    }
}