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

namespace Application\Model\Service\Interfaces;


use Laminas\Paginator\Paginator;
use Zend_Service_Solr_HttpTransportException;
use Zend_Service_Solr_InvalidArgumentException;

interface SolrServiceInterface
{
    /**
     * @param $input
     *
     * @return null|string|string[]
     */
    static public function escape($input);

    /**
     * @param array $op
     *
     * @return null
     * @throws Zend_Service_Solr_HttpTransportException
     * @throws Zend_Service_Solr_InvalidArgumentException
     */
    public function search($op = array());

    /**
     * @param $project_id
     *
     * @return bool
     * @throws Zend_Service_Solr_HttpTransportException
     * @throws Zend_Service_Solr_InvalidArgumentException
     */
    public function isExist($project_id);

    /**
     * @return Paginator|null
     */
    public function getPagination();

    /**
     *
     * Get spell
     *
     * @param array $op
     *
     * @return mixed
     * @throws Zend_Service_Solr_HttpTransportException
     * @throws Zend_Service_Solr_InvalidArgumentException
     */
    public function spell($op = array());
}