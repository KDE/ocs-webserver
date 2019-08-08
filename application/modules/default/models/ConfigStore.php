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
 *
 * Created: 13.09.2017
 */
class Default_Model_ConfigStore
{

    /**
     * @inheritDoc
     */
    public $store_id;
    public $host;
    public $name;
    public $config_id_name;
    public $mapping_id_name;
    public $order;
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
    public $created_at;
    public $changed_at;
    public $deleted_at;
    public $browse_list_type;

    public function __construct($storeHostName)
    {
        $storeConfigArray = Zend_Registry::get('application_store_config_list');
        if (isset($storeConfigArray[$storeHostName])) {
            $storeConfig = $storeConfigArray[$storeHostName];
            $this->store_id = $storeConfig['store_id'];
            $this->host = $storeConfig['host'];
            $this->name = $storeConfig['name'];
            $this->config_id_name = $storeConfig['config_id_name'];
            $this->mapping_id_name = $storeConfig['mapping_id_name'];
            $this->order = $storeConfig['order'];
            $this->is_client = $storeConfig['is_client'];
            $this->google_id = $storeConfig['google_id'];
            $this->piwik_id = $storeConfig['piwik_id'];
            $this->package_type = $storeConfig['package_type'];
            $this->cross_domain_login = $storeConfig['cross_domain_login'];
            $this->is_show_title = $storeConfig['is_show_title'];
            $this->is_show_home = $storeConfig['is_show_home'];
            $this->is_show_git_projects = $storeConfig['is_show_git_projects'];
            $this->is_show_blog_news = $storeConfig['is_show_blog_news'];
            $this->is_show_forum_news = $storeConfig['is_show_forum_news'];
            $this->is_show_in_menu = $storeConfig['is_show_in_menu'];
            $this->is_show_real_domain_as_url = $storeConfig['is_show_real_domain_as_url'];
            $this->layout_home = $storeConfig['layout_home'];
            $this->layout_explore = $storeConfig['layout_explore'];
            $this->layout_pagedetail = $storeConfig['layout_pagedetail'];
            $this->layout = $storeConfig['layout'];
            $this->render_view_postfix = $storeConfig['render_view_postfix'];
            $this->stay_in_context = $storeConfig['stay_in_context'];
            $this->created_at = $storeConfig['created_at'];
            $this->changed_at = $storeConfig['changed_at'];
            $this->deleted_at = $storeConfig['deleted_at'];
            $this->browse_list_type = $storeConfig['browse_list_type'];
        } else {
            Zend_Registry::get('logger')->warn(__METHOD__ . '(' . __LINE__ . ') - ' . $host
                . ' :: no domain config context configured')
            ;
        }
    }

    /**
     * @return bool
     */
    public function isShowHomepage()
    {
        return $this->is_show_home == 1 ? true : false;
    }

    /**
     * @return bool
     */
    public function isRenderReact()
    {
        return $this->render_view_postfix == 'react' ? true : false;
    }

}
