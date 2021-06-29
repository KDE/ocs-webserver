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

namespace Application\Controller\Plugin;


use Laminas\Config\Config;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

class ConfigHelper extends AbstractPlugin
{

    private $config;

    /**
     * ConfigHelper constructor.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = new Config($config);
    }

    /**
     * @param string|null $section
     *
     * @return mixed
     */
    public function __invoke($section = null)
    {
        if ($section) {
            return $this->config->$section;
        }

        return $this->config;
    }

}