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

class Local_Filter_Url_Base64Decrypt implements Zend_Filter_Interface
{

    /**
     * Returns the result of filtering $value
     *
     * @param mixed $value
     *
     * @return mixed
     * @throws Zend_Filter_Exception If filtering $value is impossible
     */
    public function filter($value)
    {
        return $this->decryptString($value);
    }

    /**
     * @param string $url
     *
     * @return string
     */
    /**
     * @param string $text
     *
     * @return string
     */
    protected function decryptString($text)
    {
        if (empty($text)) {
            return '';
        }

        $encryptedUrl = base64_decode($text);

        return trim($encryptedUrl);
    }

}