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
 *
 */

namespace Library\Filter\File;


use Laminas\Filter\Exception;

class RemoveEmptyUploads extends \Laminas\Filter\AbstractFilter
{

    /**
     * @inheritDoc
     */
    public function filter($files)
    {
        if (!is_array($files)) {
            return $files;
        }
        if (count($files) == 0) {
            return $files;
        }
        $filtered = array();
//        foreach ($files as $key => $values) {
//            $filtered[$key] = array_filter($files[$key], function($file){ return $file['error'] === 0; });
//        }
//        $return_value = array_filter($filtered);

//        $return_value = $this->array_filter_recursive($files, function($file){ return $file['error'] === 0; });
        $filtered = array_filter($files, array($this, 'filter_item'));
        $return_value = array_filter($filtered);

        return $return_value;
    }

    private function array_filter_recursive($input, $callback = null)
    {
        foreach ($input as &$value)
        {
            if (is_array($value))
            {
                $value = $this->array_filter_recursive($value, $callback);
            }
        }

        return array_filter($input, $callback);
    }

    private function filter_item($param)
    {
        if (isset($param['error'])) {
            return $param['error'] === 0;
        }

        return false;
    }
}