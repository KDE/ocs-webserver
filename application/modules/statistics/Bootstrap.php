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
class Statistics_Bootstrap extends Zend_Application_Module_Bootstrap
{

    protected function _initAutoloader()
    {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'Statistics',
            'basePath' => realpath(dirname(__FILE__)),
        ));
        $autoloader->addResourceType('Ranking', 'library/statistics/ranking', 'Ranking');
        return $autoloader;
    }

    protected function _initRouter()
    {
        $frontController = Zend_Controller_Front::getInstance();
        /** @var $router Zend_Controller_Router_Rewrite */
        $router = $frontController->getRouter();

//        $dir = $router->getFrontController()->getControllerDirectory();

        $router->addRoute(
            'statistics_daily_ajax',
            new Zend_Controller_Router_Route(
                '/statistics/daily/ajax/:project_id/:year/:month/:day/',
                array(
                    'module' => 'statistics',
                    'controller' => 'daily',
                    'action' => 'ajax',
                    'project_id' => null,
                    'year' => null,
                    'month' => null,
                    'day' => null
                )
            )
        );

        $router->addRoute(
            'statistics_monthly_ajax',
            new Zend_Controller_Router_Route(
                '/statistics/monthly/ajax/:project_id/:year/:month/',
                array(
                    'module' => 'statistics',
                    'controller' => 'monthly',
                    'action' => 'ajax',
                    'project_id' => null,
                    'year' => null,
                    'month' => null
                )
            )
        );

        $router->addRoute(
            'statistics_weekly_ajax',
            new Zend_Controller_Router_Route(
                '/statistics/weekly/ajax/:project_id/:yearweek/',
                array(
                    'module' => 'statistics',
                    'controller' => 'weekly',
                    'action' => 'ajax',
                    'project_id' => null,
                    'yearweek' => null
                )
            )
        );

        return $router;
    }

    protected function _initIncludePath () {
        set_include_path(implode(PATH_SEPARATOR, array(
            dirname(__FILE__) . '/library',
            get_include_path(),
        )));
    }

}

