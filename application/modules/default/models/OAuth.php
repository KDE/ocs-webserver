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
 *    Created: 16.12.2016
 **/
class Default_Model_OAuth
{

    const LOGIN_GITUHB = 'github';

    /**
     * Default_Model_OAuth constructor.
     */
    public function __construct()
    {
    }

    public static function factory($providerId)
    {
        switch ($providerId) {
            case self::LOGIN_GITUHB:
                $authAdapter = new Default_Model_OAuth_Github(
                    Zend_Registry::get('db'),
                    'member',
                    Zend_Registry::get('config')->third_party->github);
                break;

            default:
                throw new Zend_Exception('No provider id present');
                break;
        }

        return $authAdapter;
    }

}