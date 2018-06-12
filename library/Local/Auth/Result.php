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
 * Created: 30.05.2018
 */

class Local_Auth_Result extends Zend_Auth_Result
{

    const MAIL_ADDRESS_NOT_VALIDATED = -10;

    const ACCOUNT_INACTIVE = -20;

    /**
     * Sets the result code, identity, and failure messages
     *
     * @param int   $code
     * @param mixed $identity
     * @param array $messages
     */
    public function __construct($code, $identity, array $messages = array())
    {
        $code = (int) $code;

        $this->_code     = $code;
        $this->_identity = $identity;
        $this->_messages = $messages;
    }


}