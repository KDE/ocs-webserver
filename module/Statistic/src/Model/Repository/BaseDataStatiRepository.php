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

namespace Statistic\Model\Repository;

use DateTime;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;

class BaseDataStatiRepository
{
    protected $db;

    public function __construct(
        AdapterInterface $db
    ) {
        $this->db = $db;
    }

    public function fetchRow($sql, $params = null)
    {
        $results = $this->fetchAll($sql, $params);

        return array_pop($results);
    }

    public function fetchAll($sql, $params = null)
    {
        $statement = $this->db->driver->createStatement($sql);
        $statement->prepare();

        if ($params && !is_array($params)) {
            $params = array($params);
        }


        $result = $statement->execute($params);

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet;
            $resultSet->initialize($result);
        }

        return $resultSet->toArray();
    }

    protected function getLastYearMonth($yyyymm)
    {
        $aktdate = strval($yyyymm) . '01';
        $fmt = 'Ymd';
        $d = DateTime::createFromFormat($fmt, $aktdate);
        $d->modify('last day of previous month');

        return $d->format('Ym');
    }
}