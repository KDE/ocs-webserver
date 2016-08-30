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

class Local_Log_Filter_MinMax extends Zend_Log_Filter_Abstract
{

    /** @var int */
    protected $minPriority;
    /** @var int */
    protected $maxPriority;

    /**
     *
     * Filter logging by $priority.  By default, it will accept any log
     * event whose priority value is less than or equal to $priorityMax
     * and greater or equal to $priorityMin.
     *
     * @param $priorityMin
     * @param $priorityMax
     * @throws Zend_Log_Exception
     * @link http://php.net/manual/en/language.oop5.decon.php
     */
    public function __construct($priorityMin, $priorityMax)
    {
        if (!is_int($priorityMin)) {
            throw new Zend_Log_Exception('minimal Priority must be an integer');
        }

        if (!is_int($priorityMax)) {
            throw new Zend_Log_Exception('maximal Priority must be an integer');
        }

        $this->minPriority = $priorityMin;
        $this->maxPriority = $priorityMax;
    }

    /**
     * Construct a Zend_Log driver
     *
     * @param  array|Zend_Config $config
     * @return Zend_Log_FactoryInterface
     */
    static public function factory($config)
    {
        $config = self::_parseConfig($config);
        $config = array_merge(array(
            'priorityMin' => null,
            'priorityMax' => null,
        ), $config);

        return new self(
            (int)$config['priorityMin'],
            $config['priorityMax']
        );
    }

    /**
     * Returns TRUE to accept the message, FALSE to block it.
     *
     * @param  array $event event data
     * @return boolean            accepted?
     */
    public function accept($event)
    {
        return (($event['priority'] >= $this->minPriority) AND ($event['priority'] <= $this->maxPriority));
    }

}