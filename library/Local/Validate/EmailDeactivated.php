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
class Local_Validate_EmailDeactivated extends Zend_Validate_Abstract
{
    const EMAIL_DEACTIVATED_TEXT = "_deactivated";
    const EXISTS = 'already_exists';
    const DEACTIVATED = 'mail_deactivated';

    protected $_messageTemplates = array(
        self::DEACTIVATED => 'mail address deactivated'
    );

    public function isValid($value, $context = null)
    {
        $value = (string)$value;
        $this->_setValue($value);

        return $this->isEmailDeactivated($value, $context);
    }

    /**
     * @param $email
     * @param $context
     *
     * @return bool
     */
    private function isEmailDeactivated($email, $context)
    {
        if (strpos($email->mail, $this::EMAIL_DEACTIVATED_TEXT) > 0) {
            $this->_error(self::DEACTIVATED);

            return false;
        }

        return true;
    }

}