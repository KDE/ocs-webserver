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

namespace JobQueue\Jobs;


use Resque;

class JobBuilder
{
    const RESQUE_CONFIG_YML = 'resque.config.yml';

    /** @var array */
    private $params;
    /** @var string */
    private $job_class_name;

    public static function getJobBuilder()
    {
        return new JobBuilder();
    }

    public function withJobClass($classname)
    {
        $this->job_class_name = $classname;

        return $this;
    }

    public function withParam($name, $value)
    {
        if (empty($this->params)) {
            $this->params = array();
        }
        $this->params = $this->params + array($name => $value);

        return $this;
    }

    public function build()
    {
        if (getenv('RESQUE_CONFIG_YML') === false) {
            Resque::loadConfig(self::RESQUE_CONFIG_YML); // load from default config
        } else {
            Resque::loadConfig(getenv('RESQUE_CONFIG_YML'));
        }

        return Resque::queue()->push($this->job_class_name, $this->params);
    }

    public function perform()
    {
        $class = new $this->job_class_name;

        return $class->perform($this->params);
    }

}