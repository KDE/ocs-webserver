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

class ConfigStore implements InputFilterAwareInterface
{
    public $store_id;
    public $host;
    public $name;
    public $config_id_name;
    public $mapping_id_name;
    public $order;
    public $default;
    public $is_client;
    public $google_id;
    public $piwik_id;
    public $package_type;
    public $cross_domain_login;
    public $is_show_title;
    public $is_show_home;
    public $is_show_git_projects;
    public $is_show_blog_news;
    public $is_show_forum_news;
    public $is_show_in_menu;
    public $is_show_real_domain_as_url;
    public $layout_home;
    public $layout_explore;
    public $layout_pagedetail;
    public $layout;
    public $render_view_postfix;
    public $stay_in_context;
    public $browse_list_type;
    public $created_at;
    public $changed_at;
    public $deleted_at;

    private $inputFilter;

    public function exchangeArray(array $data)
    {

        $this->store_id = isset($data['store_id']) ? $data['store_id'] : null;
        $this->host = isset($data['host']) ? $data['host'] : null;
        $this->name = isset($data['name']) ? $data['name'] : null;
        $this->config_id_name = isset($data['config_id_name']) ? $data['config_id_name'] : null;
        $this->mapping_id_name = isset($data['mapping_id_name']) ? $data['mapping_id_name'] : null;
        $this->order = isset($data['order']) ? $data['order'] : null;
        $this->default = isset($data['default']) ? $data['default'] : null;
        $this->is_client = isset($data['is_client']) ? $data['is_client'] : null;
        $this->google_id = isset($data['google_id']) ? $data['google_id'] : null;
        $this->piwik_id = isset($data['piwik_id']) ? $data['piwik_id'] : null;
        $this->package_type = isset($data['package_type']) ? $data['package_type'] : null;
        $this->cross_domain_login = isset($data['cross_domain_login']) ? $data['cross_domain_login'] : null;
        $this->is_show_title = isset($data['is_show_title']) ? $data['is_show_title'] : null;
        $this->is_show_home = isset($data['is_show_home']) ? $data['is_show_home'] : null;
        $this->is_show_git_projects = isset($data['is_show_git_projects']) ? $data['is_show_git_projects'] : null;
        $this->is_show_blog_news = isset($data['is_show_blog_news']) ? $data['is_show_blog_news'] : null;
        $this->is_show_forum_news = isset($data['is_show_forum_news']) ? $data['is_show_forum_news'] : null;
        $this->is_show_in_menu = isset($data['is_show_in_menu']) ? $data['is_show_in_menu'] : null;
        $this->is_show_real_domain_as_url = isset($data['is_show_real_domain_as_url']) ? $data['is_show_real_domain_as_url'] : null;
        $this->layout_home = isset($data['layout_home']) ? $data['layout_home'] : null;
        $this->layout_explore = isset($data['layout_explore']) ? $data['layout_explore'] : null;
        $this->layout_pagedetail = isset($data['layout_pagedetail']) ? $data['layout_pagedetail'] : null;
        $this->layout = isset($data['layout']) ? $data['layout'] : null;
        $this->render_view_postfix = isset($data['render_view_postfix']) ? $data['render_view_postfix'] : null;
        $this->stay_in_context = isset($data['stay_in_context']) ? $data['stay_in_context'] : null;
        $this->browse_list_type = isset($data['browse_list_type']) ? $data['browse_list_type'] : null;
        $this->created_at = isset($data['created_at']) ? $data['created_at'] : null;
        $this->changed_at = isset($data['changed_at']) ? $data['changed_at'] : null;
        $this->deleted_at = isset($data['deleted_at']) ? $data['deleted_at'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'store_id'                   => $this->store_id,
            'host'                       => $this->host,
            'name'                       => $this->name,
            'config_id_name'             => $this->config_id_name,
            'mapping_id_name'            => $this->mapping_id_name,
            'order'                      => $this->order,
            'default'                    => $this->default,
            'is_client'                  => $this->is_client,
            'google_id'                  => $this->google_id,
            'piwik_id'                   => $this->piwik_id,
            'package_type'               => $this->package_type,
            'cross_domain_login'         => $this->cross_domain_login,
            'is_show_title'              => $this->is_show_title,
            'is_show_home'               => $this->is_show_home,
            'is_show_git_projects'       => $this->is_show_git_projects,
            'is_show_blog_news'          => $this->is_show_blog_news,
            'is_show_forum_news'         => $this->is_show_forum_news,
            'is_show_in_menu'            => $this->is_show_in_menu,
            'is_show_real_domain_as_url' => $this->is_show_real_domain_as_url,
            'layout_home'                => $this->layout_home,
            'layout_explore'             => $this->layout_explore,
            'layout_pagedetail'          => $this->layout_pagedetail,
            'layout'                     => $this->layout,
            'render_view_postfix'        => $this->render_view_postfix,
            'stay_in_context'            => $this->stay_in_context,
            'browse_list_type'           => $this->browse_list_type,
            'created_at'                 => $this->created_at,
            'changed_at'                 => $this->changed_at,
            'deleted_at'                 => $this->deleted_at,
        ];
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

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(
            sprintf(
                '%s does not allow injection of an alternate input filter', __CLASS__
            )
        );
    }
}
