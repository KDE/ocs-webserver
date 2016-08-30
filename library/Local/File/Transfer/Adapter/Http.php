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

class Local_File_Transfer_Adapter_Http extends Zend_File_Transfer_Adapter_Http
{
    /**
     * @param null $files
     * @return bool
     */
    public function isValid($files = null)
    {
        // Workaround for WebServer not conforming HTTP and omitting CONTENT_LENGTH
        $content = 0;
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $content = $_SERVER['CONTENT_LENGTH'];
        } else {
            if (!empty($_POST)) {
                $content = serialize($_POST);
            }
        }

        // Workaround for a PHP error returning empty $_FILES when form data exceeds php settings
        if (empty($this->_files) && ($content > $this->return_bytes(ini_get('post_max_size')))) {
            if (is_array($files)) {
                if (0 === count($files)) {
                    return false;
                }

                $files = current($files);
            }

            $temp = array(
                $files => array(
                    'name' => $files,
                    'error' => 1
                )
            );
            $validator = $this->_validators['Zend_Validate_File_Upload'];
            $validator->setFiles($temp)
                ->isValid($files, null);
            $this->_messages += $validator->getMessages();
            return false;
        }

        return Zend_File_Transfer_Adapter_Abstract::isValid($files);
    }

    /**
     * @param string $size_str
     * @return int
     */
    private function return_bytes($size_str)
    {
        switch (substr($size_str, -1)) {
            case 'M':
            case 'm':
                return (int)$size_str * 1048576;
            case 'K':
            case 'k':
                return (int)$size_str * 1024;
            case 'G':
            case 'g':
                return (int)$size_str * 1073741824;
            default:
                return $size_str;
        }
    }

}