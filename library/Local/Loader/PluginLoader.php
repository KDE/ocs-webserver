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

class Local_Loader_PluginLoader extends Zend_Loader_PluginLoader
{

    protected static function _appendIncFile($incFile)
    {
        if ($stream = fopen(self::$_includeFileCache, 'c+')) {

            flock($stream, LOCK_EX);

            $file = stream_get_contents($stream);
            if (empty($file)) {
                $file = '<?php';
            }

            if (!strstr($file, $incFile)) {
                $file .= "\ninclude_once '$incFile';";
                fseek($stream, 0);
                fwrite($stream, $file);
            }

            flock($stream, LOCK_UN);
            fclose($stream);

        }
    }

}