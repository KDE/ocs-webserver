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


use Exception;

class CurrentStore
{
    /** @var ConfigStore */
    protected $config;

    /** @var array */
    protected $tags;

    /** @var array */
    protected $tag_groups;

    /** @var array */
    protected $categories;

    /** @var array */
    protected $template;

    public function __construct()
    {
    }

    /**
     * @param array $data
     */
    public function exchangeArray(array $data)
    {
        $this->config = !empty($data['config']) ? $data['config'] : null;
        $this->tags = !empty($data['tags']) ? $data['tags'] : null;
        $this->tag_groups = !empty($data['tag_groups']) ? $data['tag_groups'] : null;
        $this->categories = !empty($data['categories']) ? $data['categories'] : null;
        $this->template = !empty($data['template']) ? $data['template'] : null;
    }

    /**
     * @return array
     */
    public function getArrayCopy()
    {
        return [
            'config'     => $this->config,
            'tags'       => $this->tags,
            'tag_groups' => $this->tag_groups,
            'categories' => $this->categories,
            'template'   => $this->template,
        ];
    }

    /**
     * @param $name
     *
     * @return ConfigStore|array
     * @throws Exception
     */
    public function __get($name)
    {
        if (method_exists($this, $name)) {
            return $this->$name();
        }
        switch (strtolower($name)) {
            case 'config':
                return $this->config;
            case 'tags':
                return $this->tags;
            case 'tag_groups':
                return $this->tag_groups;
            case 'categories':
                return $this->categories;
            case 'template':
                return $this->template;
            default:
                throw new Exception('Invalid magic property on current store');
        }
    }

    /**
     * @return ConfigStore
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param ConfigStore $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return array
     */
    public function getTagGroups()
    {
        return $this->tag_groups;
    }

    /**
     * @param array $tag_groups
     */
    public function setTagGroups($tag_groups)
    {
        $this->tag_groups = $tag_groups;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return array
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param array $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function hasOwnUrl()
    {
        return (bool)$this->config->is_show_real_domain_as_url;
    }

}