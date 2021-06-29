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

namespace Statistic;

use Laminas\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * @param \Laminas\Mvc\MvcEvent $e The MvcEvent instance
     *
     * @return void
     */
    public function onBootstrap($e)
    {
        // Register a dispatch event
        $app = $e->getParam('application');
        $app->getEventManager()->attach('dispatch', array($this, 'setLayout'));
    }

    /**
     * @param \Laminas\Mvc\MvcEvent $e The MvcEvent instance
     *
     * @return void
     */
    public function setLayout($e)
    {
        $matches = $e->getRouteMatch();
        $controller = $matches->getParam('controller');
        if (false === strpos($controller, __NAMESPACE__)) {
            // not a controller from this module
            return;
        }

        // Set the layout template
        $viewModel = $e->getViewModel();
        $viewModel->setTemplate('statistic/layout');
    }

}