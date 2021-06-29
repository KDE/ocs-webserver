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


use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;

class TestJob extends BaseJob
{
    /**
     * for simple testing puposes
     * use it like
     *  ./vendor/bin/resque job:queue JobQueue\\Jobs\\TestJob -c ./config/resque.config.yml
     *
     * @param $args
     */
    public function perform($args)
    {
        $this->logger->info(__METHOD__ . ' a small test.');
        $db = GlobalAdapterFeature::getStaticAdapter();
        $resultSet = new ResultSet();
        $statement = $db->query('SELECT * FROM `member` LIMIT 10;');
        $result = $statement->execute();
        $resultSet->initialize($result);
        $this->logger->info(__METHOD__ . ' ' . var_export($resultSet->toArray(), true));
        $this->logger->info(__METHOD__ . ' ' . PPLOAD_DOWNLOAD_SECRET);
    }

}